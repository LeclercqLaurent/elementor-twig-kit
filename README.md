# Elementor Twig Kit

Preuve de concept : une petite suite de widgets Elementor dont le balisage est
rendu par **Twig**, dont la configuration vit dans un **`.env`**, et dont toute
la logique est **testée hors-ligne** et analysée au **niveau 9**.

[![CI](https://github.com/LeclercqLaurent/elementor-twig-kit/actions/workflows/ci.yml/badge.svg)](https://github.com/LeclercqLaurent/elementor-twig-kit/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-%E2%89%A5%208.2-777BB4)](https://www.php.net/)
[![Licence](https://img.shields.io/badge/licence-MIT-blue)](LICENSE)
[![Couverture](https://img.shields.io/badge/couverture-97%25-brightgreen)](#qualité)

---

## Ce que ce dépôt démontre

Un widget Elementor est, dans l'immense majorité des extensions, une classe PHP
qui **concatène du HTML dans des `echo`**. Trois conséquences, toujours les
mêmes :

1. **L'échappement est manuel**, donc oublié. Il suffit d'un `esc_html()` omis
   sur une valeur venue d'une API pour ouvrir une XSS stockée.
2. **Le balisage devient illisible** dès la deuxième condition, et surchargeable
   nulle part : un thème qui veut changer une carte doit dupliquer la classe.
3. **Rien n'est testable.** La logique de filtrage, de pagination et de rendu vit
   dans une classe qui n'existe qu'à l'intérieur de WordPress, donc jamais
   couverte.

Ce dépôt répond aux trois : Twig échappe par défaut et rend les gabarits
surchargeables ; le domaine et l'infrastructure ne connaissent ni WordPress ni
Elementor, donc s'exécutent dans PHPUnit sans site installé ; la couche
WordPress est réduite à une coquille : déclarer des contrôles, lire des
réglages, afficher une chaîne.

**Ce que ce n'est pas** : un plugin de production. Les offres sont fictives, le
domaine « emploi » n'est qu'un prétexte crédible, et il n'y a ni cache, ni
pagination, ni interface d'administration. C'est une démonstration
d'architecture, volontairement courte pour rester lisible d'un bout à l'autre.

---

## Architecture

Ports et adaptateurs, avec un flux de dépendance qui ne va que vers l'intérieur.

```
src/
├── Domain/                  PHP pur : ni WordPress, ni Twig, ni réseau
│   ├── JobOffer.php             entité immuable, valide par construction
│   ├── JobQuery.php             critères de recherche (objet, pas 4 paramètres)
│   ├── ContractType.php         enum + libellés
│   ├── Location.php             value object normalisé
│   └── Port/JobOfferSource.php  « d'où viennent les offres » = une interface
│
├── Infrastructure/          les adaptateurs concrets
│   ├── Api/                     source distante : cURL + mapping + pannes typées
│   ├── Fixture/                 source de démonstration, jeu embarqué
│   ├── Config/                  lecture du .env, configuration validée
│   ├── Rendering/               Twig derrière une interface Renderer
│   └── Logging/                 journal serveur, jamais la page
│
└── WordPress/               la seule couche qui connaît WordPress
    ├── Plugin.php               racine de composition : tout est câblé ici
    ├── Services.php             compromis assumé, expliqué plus bas
    └── Widget/                  déclaration des contrôles + « echo »
```

Le port `JobOfferSource` a **deux** implémentations réelles, et c'est ce qui rend
la démonstration exécutable : `HttpJobOfferSource` appelle une API,
`FixtureJobOfferSource` lit un jeu d'offres fictives embarqué. Le widget ne sait
pas laquelle il utilise.

### Le rendu

```php
protected function render(): void
{
    echo Services::get()->renderer->render($this->templateName(), $this->templateContext());
}
```

C'est tout ce qu'un widget fait de son balisage. Le gabarit correspondant vit
dans `templates/`, en Twig, avec `autoescape` et `strict_variables` actifs : une
valeur d'API est échappée sans qu'un développeur ait à y penser, et une clé de
contexte oubliée devient une **panne journalisée** plutôt qu'un trou silencieux
dans la page.

Un test le vérifie plutôt que de l'affirmer :

```php
public function testLeContenuVenuDeLApiEstEchappeSansGesteDuDeveloppeur(): void
```

### Le mode dégradé

Le point de conception le plus important n'est pas le rendu, c'est **l'échec**.

- **Pas de `.env`** → le plugin démarre en mode démonstration sur le jeu
  embarqué. Un intégrateur peut composer ses pages Elementor avant que l'API
  n'existe.
- **Mode réel demandé mais clé vitale absente** → les widgets **ne
  s'enregistrent pas**, la cause part dans le journal du serveur, le site
  continue d'être servi.
- **API injoignable, JSON illisible** → panne typée (`TransportFailure`), bloc
  vide, journal renseigné.
- **Une offre mal saisie** → elle est ignorée et journalisée ; les autres
  s'affichent. Une donnée fautive côté back-office ne vide pas la page.
- **`vendor/` absent** → le plugin s'abstient et affiche un avis en
  administration, au lieu d'une erreur fatale sur toutes les pages du site.

Un plugin qui interrompt le rendu d'un site en production parce qu'une clé
manque coûte plus cher que la fonctionnalité qu'il apporte.

### Accessibilité

Les gabarits sont écrits pour le RGAA, pas rendus conformes après coup : niveau
de titre paramétrable (un widget ignore la hiérarchie de la page qui l'accueille
et ne doit pas imposer un `h2`), `aria-labelledby` sur la section, un `label`
associé à chaque champ par un identifiant **unique par instance de widget** (deux
formulaires sur la même page ne doivent pas produire deux fois le même `id`),
recherche soumise en `GET` pour rester partageable, et un vrai `<button>` plutôt
qu'un lien stylé.

---

## Essayer

```bash
git clone https://github.com/LeclercqLaurent/elementor-twig-kit.git
cd elementor-twig-kit
composer install
scripts/qa.sh
```

La suite tourne **sans WordPress, sans Elementor, sans réseau et sans base de
données**. C'est la conséquence directe de l'architecture, pas un tour de force :
seule la couche `src/WordPress` a besoin d'un site, et elle ne contient rien
qu'on ait envie de tester.

Pour l'installer réellement : déposer le dossier dans `wp-content/plugins/`,
lancer `composer install`, activer. Sans `.env`, il fonctionne en mode
démonstration. Pour brancher une API, copier `.env.dist` en `.env` et passer
`JOBS_DEMO_MODE=false`.

---

## Qualité

```bash
scripts/qa.sh    # CS-Fixer (PSR-12) + PHPStan level 9 + PHPUnit + couverture ≥ 90 %
```

Garde-fou à lancer avant chaque commit ; le même enchaînement tourne en
intégration continue sur PHP 8.2, 8.3 et 8.4.

**État actuel** : PHPStan level 9 sans erreur, PSR-12 respecté, 53 tests,
**97 % de couverture**.

La couverture exclut `src/WordPress`, seule couche qui exige un site installé.
L'exclusion n'est tenable que parce que cette couche est délibérément anémique :
si de la logique commençait à s'y accumuler, le chiffre resterait beau et
mentirait. Elementor est décrit par des stubs (`stubs/elementor.php`) plutôt
qu'ignoré par l'analyse.

`stubs/`, `composer.lock` et la plateforme figée à PHP 8.2 vont ensemble : le
lock est résolu pour la version la plus basse supportée, de sorte que la CI
puisse installer les mêmes versions sur toute la matrice. Un lock résolu sous
8.4 tire des composants exigeant 8.4 et rend la borne annoncée invérifiable.

---

## Le compromis assumé

`WordPress\Services` est un localisateur de services statique, ce qu'on évite
partout ailleurs dans ce dépôt.

Elementor instancie lui-même les classes de widgets, sans argument, à chaque
rendu : leurs dépendances ne peuvent pas passer par le constructeur. Les deux
issues sont un localisateur ou des fonctions globales. Le localisateur au moins
nomme ses dépendances, se remplace intégralement dans un test, et **confine la
contrainte à une seule classe**. Tout le reste reçoit ses collaborateurs par
injection.

Documenter l'écart et son motif vaut mieux que prétendre qu'il n'existe pas.

---

## Licence

MIT, voir [LICENSE](LICENSE).
