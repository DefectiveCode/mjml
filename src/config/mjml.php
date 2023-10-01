<?php

declare(strict_types=1);

return [
    /**
     * The fonts that are used in the mjml templates.
     */
    'fonts' => [
        'Open Sans' => 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700',
        'Droid Sans' => 'https://fonts.googleapis.com/css?family=Droid+Sans:300,400,500,700',
        'Lato' => 'https://fonts.googleapis.com/css?family=Lato:300,400,500,700',
        'Roboto' => 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700',
        'Ubuntu' => 'https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700',
    ],

    /**
     * Option to keep comments in the HTML output
     */
    'keep_comments' => true,

    /**
     * Enable or disable the use of the mj-include component.
     */
    'ignore_includes' => false,

    /**
     * Enable or disable beautification of the html using js-beautify.
     */
    'beautify' => false,

    /**
     * The options past to js-beautify.
     * See https://www.npmjs.com/package/js-beautify for a list of all the supported options.
     */
    'beautify_options' => [
        'indentSize' => 2,
        'wrapAttributesIndentSize' => 2,
        'maxPreserveNewline' => 0,
        'preserveNewlines' => false,
    ],

    /**
     * Enable or disable minification of the html using html-minifier-terser.
     */
    'minify' => true,

    /**
     * The options past to html-minifier-terser.
     * See https://www.npmjs.com/package/html-minifier-terser for a list of all the supported options.
     */
    'minify_options' => [
        'collapseWhitespace' => true,
        'minifyCSS' => false,
        'caseSensitive' => true,
        'removeEmptyAttributes' => true,
    ],

    /**
     * The validation level to use. See https://github.com/mjmlio/mjml/tree/master/packages/mjml-validator#validating-mjml
     * for more information.
     */
    'validation_level' => \DefectiveCode\MJML\ValidationLevel::soft,

    /**
     * Path of file, used for relative paths in mj-includes
     */
    'file_path' => '.',

    /**
     * The options past to juice.
     * See https://www.npmjs.com/package/juice for a list of all the supported options.
     */
    'juice_options' => [],

    /**
     * The tags that juice should not inline. This is useful if you want to inline your own styles.
     * See https://www.npmjs.com/package/juice for more information.
     */
    'juice_preserve_tags' => [],
];
