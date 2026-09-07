# Elementor Twig Kit

A proof of concept: a small set of Elementor widgets whose markup is rendered by
**Twig**, whose configuration lives in a **`.env`**, and whose logic is **tested
offline** and analysed at **level 9**.

[![CI](https://github.com/LeclercqLaurent/elementor-twig-kit/actions/workflows/ci.yml/badge.svg)](https://github.com/LeclercqLaurent/elementor-twig-kit/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-%E2%89%A5%208.2-777BB4)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)
[![Coverage](https://img.shields.io/badge/coverage-97%25-brightgreen)](#quality)

---

## What this repository demonstrates

In the vast majority of extensions, an Elementor widget is a PHP class that
**concatenates HTML into `echo` statements**. Three consequences follow, always
the same ones:

1. **Escaping is manual**, so it gets forgotten. One missing `esc_html()` on a
   value coming from an API is enough to open a stored XSS.
2. **The markup becomes unreadable** by the second condition, and overridable
   nowhere: a theme wanting to change a card has to duplicate the class.
3. **Nothing is testable.** The filtering, pagination and rendering logic lives
   in a class that only exists inside WordPress, and is therefore never covered.

This repository answers all three: Twig escapes by default and makes templates
overridable; the domain and the infrastructure know neither WordPress nor
Elementor, so they run under PHPUnit with no site installed; the WordPress layer
is reduced to a shell that declares controls, reads settings and echoes a string.

**What it is not**: a production plugin. The offers are fictional, the "jobs"
domain is only a plausible pretext, and there is no cache, no pagination and no
admin screen. It is an architecture demonstration, deliberately short so it can
be read end to end.

---

## Architecture

Ports and adapters, with a dependency flow that only ever points inwards.

```
src/
├── Domain/                  pure PHP: no WordPress, no Twig, no network
│   ├── JobOffer.php             immutable entity, valid by construction
│   ├── JobQuery.php             search criteria (one object, not 4 parameters)
│   ├── ContractType.php         enum plus labels
│   ├── Location.php             normalised value object
│   └── Port/JobOfferSource.php  "where offers come from" is an interface
│
├── Infrastructure/          the concrete adapters
│   ├── Api/                     remote source: cURL, mapping, typed failures
│   ├── Fixture/                 demo source, bundled dataset
│   ├── Config/                  .env reading, validated configuration
│   ├── Rendering/               Twig behind a Renderer interface
│   └── Logging/                 server log, never the page
│
└── WordPress/               the only layer that knows about WordPress
    ├── Plugin.php               composition root: everything is wired here
    ├── Services.php             a deliberate trade-off, explained below
    └── Widget/                  control declarations plus an "echo"
```

The `JobOfferSource` port has **two** real implementations, and that is what
makes the demonstration runnable: `HttpJobOfferSource` calls an API,
`FixtureJobOfferSource` reads a bundled set of fictional offers. The widget does
not know which one it is using.

### Rendering

```php
protected function render(): void
{
    echo Services::get()->renderer->render($this->templateName(), $this->templateContext());
}
```

That is everything a widget does with its markup. The matching template lives in
`templates/`, in Twig, with `autoescape` and `strict_variables` on: an API value
is escaped without a developer having to think about it, and a forgotten context
key becomes a **logged failure** rather than a silent hole in the page.

A test verifies this rather than asserting it:

```php
public function testContentComingFromTheApiIsEscapedWithNoDeveloperEffort(): void
```

### Degraded mode

The most important design point is not the rendering, it is **failure**.

- **No `.env`** and the plugin starts in demo mode on the bundled dataset. A
  builder can lay out their Elementor pages before the API exists.
- **Live mode requested but a vital key missing** and the widgets **do not
  register**, the cause goes to the server log, and the site keeps being served.
- **API unreachable, unreadable JSON** and you get a typed failure
  (`TransportFailure`), an empty block, and a filled-in log.
- **One badly filled offer** is skipped and logged; the others are displayed. Bad
  data in the back office does not empty the page.
- **No `vendor/`** and the plugin stands down with an admin notice, instead of a
  fatal error on every page of the site.

A plugin that interrupts the rendering of a production site because one key is
missing costs more than the feature it brings.

### Accessibility

The templates are written for accessibility rather than made compliant
afterwards: a configurable heading level (a widget knows nothing of the hierarchy
of the page hosting it and must not impose an `h2`), `aria-labelledby` on the
section, a `label` bound to each field by an id **unique per widget instance**
(two forms on the same page must not produce the same `id` twice), a search
submitted over `GET` so it stays shareable, and a real `<button>` rather than a
styled link.

---

## Trying it

```bash
git clone https://github.com/LeclercqLaurent/elementor-twig-kit.git
cd elementor-twig-kit
composer install
scripts/qa.sh
```

The suite runs **without WordPress, without Elementor, without a network and
without a database**. That is a direct consequence of the architecture, not a
feat: only the `src/WordPress` layer needs a site, and it holds nothing anyone
would want to test.

To install it for real: drop the directory into `wp-content/plugins/`, run
`composer install`, activate. With no `.env` it runs in demo mode. To plug in an
API, copy `.env.dist` to `.env` and set `JOBS_DEMO_MODE=false`.

---

## Quality

```bash
scripts/qa.sh    # CS-Fixer (PSR-12) + PHPStan level 9 + PHPUnit + coverage floor 90%
```

A guard to run before every commit; the same sequence runs in CI on PHP 8.2, 8.3
and 8.4.

**Current state**: PHPStan level 9 clean, PSR-12, 53 tests, **97% coverage**.

Coverage excludes `src/WordPress`, the only layer that requires an installed
site. That exclusion is only defensible because the layer is deliberately
anaemic: if logic started accumulating there, the figure would stay pretty and
start lying. Elementor is described by stubs (`stubs/elementor.php`) rather than
ignored by the analysis.

`stubs/`, `composer.lock` and the platform pinned to PHP 8.2 belong together: the
lock file is resolved for the lowest supported version, so CI can install the
same versions across the whole matrix. A lock resolved under 8.4 pulls in
components requiring 8.4 and makes the advertised range unverifiable.

---

## The deliberate trade-off

`WordPress\Services` is a static service locator, which is exactly what this
repository avoids everywhere else.

Elementor instantiates widget classes itself, with no arguments, on every render,
so their dependencies cannot come through the constructor. The two ways out are a
locator or global functions. The locator at least names its dependencies, can be
replaced wholesale in a test, and **confines the constraint to a single class**.
Everything else receives its collaborators by injection.

Documenting the gap and its reason beats pretending it does not exist.

---

## License

MIT, see [LICENSE](LICENSE).
