import mjml2html from 'mjml';
import { html } from 'js-beautify';
import { snakecase } from 'snakecase';
import mapKeys from 'lodash/mapKeys.js';
import merge from 'lodash/merge.js';

const args = process.argv.slice(2);
const mjmlContent = args[0];
const options = JSON.parse(args[1] || '{}');

const defaultBeautifyConfig = {
    indent_size: 2,
    wrap_attributes_indent_size: 2,
    max_preserve_newline: 0,
    preserve_newlines: false,
};

const shouldBeautify = options.beautify || false;
const beautifyOptions = merge(
    defaultBeautifyConfig,
    mapKeys(options.beautifyOptions || {}, (value, key) => {
        return snakecase(key);
    })
);

delete options.minify;
delete options.beautify;
delete options.beautifyOptions;

if (!mjmlContent) {
    console.log('No MJML content provided.');
    process.exit(1);
}

try {
    let htmlOutput = mjml2html(mjmlContent, options).html;

    if (shouldBeautify) {
        htmlOutput = html(htmlOutput, beautifyOptions);
    }

    console.log(htmlOutput);
    process.exit(0);
} catch (exception) {
    console.log(exception.message);
    process.exit(1);
}
