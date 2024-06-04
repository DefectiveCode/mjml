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

# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
