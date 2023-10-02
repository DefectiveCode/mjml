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

# Installation

1. First add the following to your `composer.json` file to instruct our package to pull the correct binaries for your
   operating system when our package is installed.

    ```json
    {
        "post-update-cmd": ["DefectiveCode\\MJML\\PullBinary::all"]
    }
    ```

    > The MJML binary will be obtained from our CDN and saved in the "bin" folder of this package during composer's
    > installation or update. Ensure that you have the necessary binaries loaded for both your local and production
    > environments.

    By default, `all` will pull all binaries we support. We recommend scoping this down to the
    operating and architecture systems you need to save on bandwidth and install times. The following are the available
    binaries.

    | Operating System | Architecture | Composer Post Update Command                  |
    | ---------------- | ------------ | --------------------------------------------- |
    | All              | All          | `DefectiveCode\MJML\PullBinary::all`          |
    | Darwin (MacOS)   | arm64        | `DefectiveCode\MJML\PullBinary::darwin-arm64` |
    | Darwin (MacOS)   | x64          | `DefectiveCode\MJML\PullBinary::darwin-x64`   |
    | Linux            | arm64        | `DefectiveCode\MJML\PullBinary::linux-arm64`  |
    | Linux            | x64          | `DefectiveCode\MJML\PullBinary::linux-x64`    |

2. Next, install the PHP package by running the following composer command:
    ```bash
    composer require defectivecode/mjml
    ```
3. That's it! If using Laravel, our package will automatically install using Laravel's package discovery.

# Usage (Without Laravel)

> See the usage with Laravel below if you are using Laravel.

## Rendering MJML

To render MJML, simply pass your MJML string to the `render` method:

```php
use DefectiveCode\MJML;

$html = (new MJML)->render(
    '<mjml><mj-body><mj-section><mj-column><mj-text>Hello World</mj-text></mj-column></mj-section></mj-body></mjml>'
);
```

## Validating MJML

To validate MJML, simply pass your MJML string to the `isValid` method:

```php
use DefectiveCode\MJML;

$isValid = (new MJML)->isValid(
    '<mjml><mj-body><mj-section><mj-column><mj-text>Hello World</mj-text></mj-column></mj-section></mj-body></mjml>'
);
```

# Usage (With Laravel)

## Rendering MJML

To render MJML, simply pass your MJML string to the `render` on the MJML facade:

```php
use DefectiveCode\MJML\Facades\MJML;

$html = MJML::render(
    '<mjml><mj-body><mj-section><mj-column><mj-text>Hello World</mj-text></mj-column></mj-section></mj-body></mjml>'
);
```

## Validating MJML

To validate MJML, simply pass your MJML string to the `isValid` method on the MJML facade:

```php
use DefectiveCode\MJML\Facades\MJML;

$isValid = MJML::isValid(
    '<mjml><mj-body><mj-section><mj-column><mj-text>Hello World</mj-text></mj-column></mj-section></mj-body></mjml>'
);
```

## Configuration

You may publish the configuration file using the following command:

```bash
php artisan vendor:publish --provider="DefectiveCode\MJML\MJMLServiceProvider"
```

This will create a `mjml.php` configuration file in your `config` folder. All the options listed in the configuration
file are past to the `config` object when you use the MJML facade.

# Configuration

All configuration options can be set by calling the following methods directly on the MJML object.

```php
use DefectiveCode\MJML;

$html = (new MJML)
    ->setMinify(true)
    ->setBeautify(false)
    ->render(
        '<mjml><mj-body><mj-section><mj-column><mj-text>Hello World</mj-text></mj-column></mj-section></mj-body></mjml>'
    );
```

Our package follows the [same configuration](https://github.com/mjmlio/mjml/tree/master) as the official MJML package
except for the following:

-   `preprocessors` - This option is not available. Please open a pull request if you would like to add this option.
-   `minifyOptions` - We use `html-minifier-terser` while the official package uses `html-minifier` for minification. We
    decided to switch the processor because `html-minifer` is no longer maintained and has a few security issues associate
    with it.

## Fonts

Our package uses the following fonts by default:

-   Open Sans: 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700
-   Droid Sans: 'https://fonts.googleapis.com/css?family=Droid+Sans:300,400,500,700
-   Lato: https://fonts.googleapis.com/css?family=Lato:300,400,500,700
-   Roboto: https://fonts.googleapis.com/css?family=Roboto:300,400,500,700
-   Ubuntu: https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700

You may change the fonts by using the following methods:

-   `addFont(string $font, string $url)` - Add a font to the list of fonts.
-   `removeFont(string$font)` - Remove a font from the list of fonts.
-   `setFonts(array $fonts)` - Set the list of fonts. You should provide an array of fonts in this
    format: `['font-name' => 'font-url']`.

## Comments

Comments are kept by default. If you wish to remove comments, you may use the `removeComments()` method.

You may also revert the `removeComments()` by calling the `keepComments()` method.

## Ignore Includes

By default, our package will include any [`mj-include` tags](https://documentation.mjml.io/#mj-include). You may adjust
this behavior by calling the `ignoreIncludes(bool $ignore)` method.

## Beautify

Our package will beautify the HTML using [`js-beautify`](https://www.npmjs.com/package/js-beautify) with the following
default options:

-   indentSize: 2
-   wrapAttributesIndentSize: 2
-   maxPreserveNewline: 0
-   preserveNewlines: false

> While `js-beautify` uses snake_case to provide options, you should use camelCase when using our package. We made this
> choice to keep our package consistent with the rest of the configuration options. Our package will automatically
> convert the camelCase options to snake_case.

You may override any of these options by providing a valid `js-beautify` configuration using the following methods:

-   `setBeautifyOptions(array $options)` - Set the `js-beautify` options.
-   `addBeautifyOption(string $option, mixed $value)` - Adds a `js-beautify` option.
-   `removeBeautifyOption(string $option)` - Removes a `js-beautify` option.

## Minify

Our package will minify the HTML using [`html-minifier-terser`](https://www.npmjs.com/package/html-minifier-terser) with
the following default options:

-   collapseWhitespace: true
-   minifyCSS: false
-   caseSensitive: true
-   removeEmptyAttributes: true

You may override any of these options by providing a
valid [`html-minifier-terser`](https://www.npmjs.com/package/html-minifier-terser) configuration using the following
methods:

-   `setMinifyOptions(array $options)` - Set the `html-minifier-terser` options.
-   `addMinifyOption(string $option, mixed $value)` - Adds a `html-minifier-terser` option.
-   `removeMinifyOption(string $option)` - Removes a `html-minifier-terser` option.

## Validation Level

Our package will validate the MJML using the `soft` validation level by default. You may change this by using the
`validationLevel(ValidationLevel $validationLevel)` method. The following validation levels are available:

-   `strict` - Your document is going through validation and is not rendered if it has any error
-   `soft` - Your document is going through validation and is rendered, even if it has errors
-   `skip` - Your document is rendered without going through validation.

## File Path

Our package will use the `.` directory by default. You may change this by using calling the `filePath(string $path)`
method.

## Juice

We do not provide any [juice options](https://www.npmjs.com/package/juice) by default. You may add juice options by
using the following methods:

-   `setJuiceOptions(array $options)` - Set the juice options.
-   `addJuiceOption(string $option, mixed $value)` - Adds a juice option.
-   `removeJuiceOption(string $option)` - Removes a juice option.
-   `setJuicePreserveTags(array $tags)` - Set the juice preserve tags.
-   `addJuicePreserveTag(string $tag, mixed $value)` - Adds a juice preserve tag.
-   `removeJuicePreserveTag(string $tag)` - Removes a juice preserve tag.
