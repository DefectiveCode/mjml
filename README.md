<p align="center">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="https://defectivecode.com/logos/logo-animated-dark.png">
      <img width="450" alt="Defective Code Logo" src="https://defectivecode.com/logos/logo-animated-light.png">
    </picture>
</p>

[English](https://www.defectivecode.com/packages/mjml/en) |
[العربية](https://www.defectivecode.com/packages/mjml/ar) |
[বাংলা](https://www.defectivecode.com/packages/mjml/bn) |
[Bosanski](https://www.defectivecode.com/packages/mjml/bs) |
[Deutsch](https://www.defectivecode.com/packages/mjml/de) |
[Español](https://www.defectivecode.com/packages/mjml/es) |
[Français](https://www.defectivecode.com/packages/mjml/fr) |
[हिन्दी](https://www.defectivecode.com/packages/mjml/hi) |
[Italiano](https://www.defectivecode.com/packages/mjml/it) |
[日本語](https://www.defectivecode.com/packages/mjml/ja) |
[한국어](https://www.defectivecode.com/packages/mjml/ko) |
[मराठी](https://www.defectivecode.com/packages/mjml/mr) |
[Português](https://www.defectivecode.com/packages/mjml/pt) |
[Русский](https://www.defectivecode.com/packages/mjml/ru) |
[Kiswahili](https://www.defectivecode.com/packages/mjml/sw) |
[தமிழ்](https://www.defectivecode.com/packages/mjml/ta) |
[తెలుగు](https://www.defectivecode.com/packages/mjml/te) |
[Türkçe](https://www.defectivecode.com/packages/mjml/tr) |
[اردو](https://www.defectivecode.com/packages/mjml/ur) |
[Tiếng Việt](https://www.defectivecode.com/packages/mjml/vi) |
[中文](https://www.defectivecode.com/packages/mjml/zh)

# Introduction

[MJML](https://mjml.io/) is a markup language specifically designed to simplify the process of coding responsive emails.
Its semantic syntax ensures ease and simplicity, while its extensive library of standard components accelerates
development and reduces the complexity of your email codebase. The open-source engine of MJML generates high-quality,
responsive HTML that adheres to best practices. If you've experienced the frustrations of working with Outlook, this
package is tailored for you.

Our MJML implementation serves as a wrapper for the official MJML API. It enables convenient compilation of MJML into
HTML directly within PHP, _**without the need for NodeJS**_. This package is ideal for PHP applications that wish to
incorporate MJML without the hassle of installing NodeJS and the MJML CLI.

## Example

```php
// Without Laravel
(new MJML)->render(
    '<mjml><mj-body><mj-section><mj-column><mj-text>Hello World</mj-text></mj-column></mj-section></mj-body></mjml>'
);

// Minified HTML
(new MJML)->minify()->render(
    '<mjml><mj-body><mj-section><mj-column><mj-text>Hello World</mj-text></mj-column></mj-section></mj-body></mjml>'
);

// With Laravel
MJML::render(
    '<mjml><mj-body><mj-section><mj-column><mj-text>Hello World</mj-text></mj-column></mj-section></mj-body></mjml>'
);

// With Laravel and minified HTML
MJML::minify()->render(
    '<mjml><mj-body><mj-section><mj-column><mj-text>Hello World</mj-text></mj-column></mj-section></mj-body></mjml>'
);
```

# Documentation

You may read the [documentation on our website](https://www.defectivecode.com/packages/mjml/en).

## Development

Use PHP 8.5 or newer, Composer 2, and Bun 1.4.5. Install development dependencies with the discovery hook enabled:

```sh
composer install
test -f bun.lock
bun install --frozen-lockfile
```

Run the tests and quality checks:

```sh
composer validate --strict
composer test
composer test -- --order-by random
composer analyse -- --no-progress
composer lint -- --test
bun --bun run test
bun --bun run typecheck
bun --bun run format:check
bun --bun run package:check
```

Replay random test order with the reported seed by adding `--random-order-seed <seed>`. CI checks both lowest and stable Composer dependency resolutions on PHP 8.5 against Laravel 12 and 13, with randomized test order. The lowest resolution installs the oldest releases Composer accepts at or above the `illuminate/support` floors; Composer skips releases with security advisories, so it can run newer releases than those floors.

Use `composer lint` and `bun --bun run format` to apply formatting. Bun is pinned in `package.json`, and formatter scripts force the Bun runtime. Keep `bun.lock` tracked; `composer.lock` remains ignored for independent library dependency resolution.

The renderer lives in `build/mjml.ts`. `build/build.sh` is the release script, not a local compile helper: it prompts for a version and rewrites `VERSION`, builds the platform binaries, signs and notarizes the macOS ARM64 binary, uploads the binaries to the download CDN, and then deletes the local build artifacts, including the binaries in `bin/`.

# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
