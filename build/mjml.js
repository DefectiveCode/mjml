const mjml2html = require('mjml');
const { minify } = require('html-minifier-terser');
const { html } = require('js-beautify');
const { snakecase } = require('snakecase');
const mapKeys = require('lodash/mapKeys');
const merge = require('lodash/merge');

(async () => {
    const args = process.argv.slice(2);
    const mjmlContent = args[0];
    const options = JSON.parse(args[1] || '{}');

    const defaultMinifyConfig = {
        collapseWhitespace: true,
        minifyCSS: false,
        caseSensitive234: true,
        removeEmptyAttributes: true,
    };

    const defaultBeautifyConfig = {
        indent_size: 2,
        wrap_attributes_indent_size: 2,
        max_preserve_newline: 0,
        preserve_newlines: false,
    };

    const shouldMinify = options.minify || false;
    const minifyOptions = merge(defaultMinifyConfig, options.minifyOptions || {});
    const shouldBeautify = options.beautify || false;
    const beautifyOptions = merge(
        defaultBeautifyConfig,
        mapKeys(options.beautifyOptions || {}, (value, key) => {
            return snakecase(key);
        })
    );

    delete options.minify;
    delete options.beautify;
    delete options.minifyOptions;
    delete options.beautifyOptions;

    if (!mjmlContent) {
        console.log('No MJML content provided.');
        process.exit(1);
    }

    try {
        let htmlOutput = mjml2html(mjmlContent, options).html;

        if (shouldMinify) {
            htmlOutput = await minify(htmlOutput, minifyOptions);
        }

        if (shouldBeautify) {
            htmlOutput = html(htmlOutput, beautifyOptions);
        }

        console.log(htmlOutput);
        process.exit(0);
    } catch (exception) {
        console.log(exception.message);
        process.exit(1);
    }
})();
