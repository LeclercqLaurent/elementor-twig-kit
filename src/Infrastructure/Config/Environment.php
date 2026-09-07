<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Config;

/**
 * Lecteur de fichier « .env », volontairement minimal.
 *
 * Les secrets d'un plugin WordPress n'ont rien à faire dans la base : ils vivent
 * dans un fichier non versionné, propre à chaque serveur, qui survit à un
 * déploiement par « git reset --hard ». Cinquante lignes suffisent — inutile de
 * traîner une dépendance pour lire des paires clé/valeur.
 */
final class Environment
{
    /**
     * @param array<string, string> $values
     */
    private function __construct(private readonly array $values)
    {
    }

    public static function fromFile(string $path): self
    {
        if (!is_readable($path)) {
            return new self([]);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return new self(self::parse(false === $lines ? [] : $lines));
    }

    /**
     * @param array<string, string> $values
     */
    public static function fromArray(array $values): self
    {
        return new self($values);
    }

    public function get(string $key, string $default = ''): string
    {
        $value = $this->values[$key] ?? '';

        return '' === $value ? $default : $value;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = strtolower($this->get($key));

        return match ($value) {
            'true', '1', 'yes', 'on' => true,
            'false', '0', 'no', 'off' => false,
            default => $default,
        };
    }

    public function float(string $key, float $default): float
    {
        $value = $this->get($key);

        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * @param list<string> $lines
     *
     * @return array<string, string>
     */
    private static function parse(array $lines): array
    {
        $values = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ('' === $line || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim(trim($value), "\"'");
        }

        return $values;
    }
}
