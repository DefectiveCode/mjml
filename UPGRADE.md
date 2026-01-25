# Upgrade Guide

## Upgrading from 1.x to 2.x

### Migration to Bun Runtime

The package binaries are now built using [Bun](https://bun.sh) instead of Node.js with pkg. Bun is a modern JavaScript runtime built with Zig and JavaScriptCore, offering significantly faster startup times—ideal for CLI tools. The switch also simplifies our build process by using Bun's native single-executable compilation instead of third-party packaging tools.

This change is transparent to end users. The binaries work identically, but you may notice faster execution when rendering MJML templates.

### Minification Changes

The official MJML package uses `html-minifier` which has a known [ReDoS vulnerability (CVE-2022-37620)](https://nvd.nist.gov/vuln/detail/CVE-2022-37620) with no fix available. To avoid bundling vulnerable dependencies, we moved minification to a lightweight PHP-based implementation.

**Breaking changes:**

1. The `minifyOptions` configuration has been removed
2. Remove `minify_options` from your `config/mjml.php` file if published
3. Remove any calls to `addMinifyOption()`, `setMinifyOptions()`, or `removeMinifyOption()` in your code
4. Use `minify()` or `minify(false)` to enable/disable minification

The new minifier removes HTML comments (preserving Outlook conditionals), collapses whitespace, and removes whitespace between tags. If you relied on `html-minifier-terser` features like `minifyCSS` or `minifyJS`, these are no longer available.
