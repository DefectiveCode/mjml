<?php

declare(strict_types=1);
use DefectiveCode\MJML\ValidationLevel;

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
     * Ignore mj-include tags by default. Set this to false only when includes
     * are needed and scoped with file_path and include_path.
     */
    'ignore_includes' => true,

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
     * Enable or disable minification of the HTML output.
     * Minification removes comments (except Outlook conditionals), collapses whitespace,
     * and removes whitespace between tags.
     */
    'minify' => true,

    /**
     * The validation level to use. See https://github.com/mjmlio/mjml/tree/master/packages/mjml-validator#validating-mjml
     * for more information.
     */
    'validation_level' => ValidationLevel::soft,

    /**
     * Base path for resolving mj-includes.
     */
    'file_path' => '.',

    /**
     * Additional paths that mj-includes may read from.
     */
    'include_path' => null,

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
