import { readFileSync } from 'node:fs';
import mjml2html from 'mjml';
import { html } from 'js-beautify';
import { snakecase } from 'snakecase';
import mapKeys from 'lodash/mapKeys.js';
import merge from 'lodash/merge.js';

const fileArgumentPrefix = '--mjml-file=';
const args = process.argv.slice(2);
const mjmlInput = args[0];
const optionsInput = args[1] || '{}';

const defaultBeautifyConfig = {
    indent_size: 2,
    wrap_attributes_indent_size: 2,
    max_preserve_newline: 0,
    preserve_newlines: false,
};

if (!mjmlInput) {
    console.log('No MJML content provided.');
    process.exit(1);
}

function resolveMjmlContent(input) {
    if (!input.startsWith(fileArgumentPrefix)) {
        return input;
    }

    const filePath = input.slice(fileArgumentPrefix.length);

    if (!filePath) {
        throw new Error('No MJML input file path provided.');
    }

    return readFileSync(filePath, 'utf8');
}

render();

async function render() {
    try {
        const options = JSON.parse(optionsInput);
        const shouldBeautify = options.beautify || false;
        const beautifyOptions = merge(
            defaultBeautifyConfig,
            mapKeys(options.beautifyOptions || {}, (value, key) => {
                return snakecase(key);
            })
        );

        delete options.minify;
        delete options.minifyOptions;
        delete options.beautify;
        delete options.beautifyOptions;

        const mjmlContent = resolveMjmlContent(mjmlInput);
        let htmlOutput = (await mjml2html(mjmlContent, options)).html;

        if (shouldBeautify) {
            htmlOutput = html(htmlOutput, beautifyOptions);
        }

        console.log(htmlOutput);
        process.exit(0);
    } catch (exception) {
        console.log(exception.message);
        process.exit(1);
    }
}
