# Upgrade Guide

## Upgrading from 2.x to 3.x

### MJML 5

This release upgrades the bundled MJML renderer from MJML 4.18 to MJML 5.2.

MJML 5 includes several upstream breaking changes:

1. `mj-include` is ignored by default for security.
2. `filePath` is now the base include sandbox.
3. `includePath` explicitly allows additional include directories.
4. The generated outer HTML/body structure changed; `mj-body` now drives the `<body>` tag.
5. The old `html-minifier` dependency was removed upstream and replaced with `htmlnano`/`cssnano`.

### Include Changes

Includes are now ignored by default. If your templates use `mj-include`, enable includes and scope the allowed paths:

```php
$html = (new MJML)
    ->ignoreIncludes(false)
    ->filePath(resource_path('mail/templates'))
    ->includePath(resource_path('mail/partials'))
    ->render($mjml);
```

If you published `config/mjml.php`, update it to include:

```php
'ignore_includes' => true,
'file_path' => '.',
'include_path' => null,
```

Set `ignore_includes` to `false` only when includes are needed.

### Minification

This package continues to use the PHP minifier introduced in 2.x. MJML 5 no longer uses the old vulnerable
`html-minifier` dependency, but keeping the PHP minifier avoids changing the package's minified output shape in the same
release as the MJML major upgrade.

`minifyOptions` remains unavailable. The package minifier removes HTML comments when comments are disabled, preserves
Outlook conditionals, collapses whitespace, and removes whitespace between tags.

### Build Changes

The old `uglify-js` and `clean-css` build stubs are removed. MJML 5 no longer installs those packages.

## Upgrading from 1.x to 2.x

### Migration to Bun Runtime

The package binaries are now built using [Bun](https://bun.sh) instead of Node.js with pkg. Bun is a modern JavaScript runtime built with Zig and JavaScriptCore, offering significantly faster startup times—ideal for CLI tools. The switch also simplifies our build process by using Bun's native single-executable compilation instead of third-party packaging tools.

This change is transparent to end users. The binaries work identically, but you may notice faster execution when rendering MJML templates.

### Minification Changes

At the time of the 2.0 release, MJML 4 used `html-minifier`, which had a known [ReDoS vulnerability (CVE-2022-37620)](https://nvd.nist.gov/vuln/detail/CVE-2022-37620) with no fix available. To avoid bundling vulnerable dependencies, we moved minification to a lightweight PHP-based implementation.

**Breaking changes:**

1. The `minifyOptions` configuration has been removed
2. Remove `minify_options` from your `config/mjml.php` file if published
3. Remove any calls to `addMinifyOption()`, `setMinifyOptions()`, or `removeMinifyOption()` in your code
4. Use `minify()` or `minify(false)` to enable/disable minification

The 2.x minifier removes HTML comments (preserving Outlook conditionals), collapses whitespace, and removes whitespace between tags. If you relied on MJML 4 `html-minifier` features like `minifyCSS` or `minifyJS`, these are no longer available.
