<?php

declare(strict_types=1);

namespace DefectiveCode\MJML;

use BadMethodCallException;

/**
 * @method Config addFont(string $font, string $url)
 * @method Config removeFont(string $font)
 * @method Config setFonts(array $fonts)
 * @method Config addBeautifyOption(string $option, mixed $value)
 * @method Config removeBeautifyOption(string $option)
 * @method Config setBeautifyOptions(array $options)
 * @method Config addMinifyOption(string $option, mixed $value)
 * @method Config removeMinifyOption(string $option)
 * @method Config setMinifyOptions(array $options)
 * @method Config addJuiceOption(string $option, mixed $value)
 * @method Config removeJuiceOption(string $option)
 * @method Config setJuiceOptions(array $options)
 * @method Config addJuicePreserveTag(string $tag, mixed $value)
 * @method Config removeJuicePreserveTag(string $tag)
 * @method Config setJuicePreserveTags(array $tags)
 */
class Config
{
    public array $fonts = [
        'Open Sans' => 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700',
        'Droid Sans' => 'https://fonts.googleapis.com/css?family=Droid+Sans:300,400,500,700',
        'Lato' => 'https://fonts.googleapis.com/css?family=Lato:300,400,500,700',
        'Roboto' => 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700',
        'Ubuntu' => 'https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700',
    ];

    public bool $keepComments = true;

    public bool $ignoreIncludes = false;

    public bool $beautify = false;

    public array $beautifyOptions = [
        'indentSize' => 2,
        'wrapAttributesIndentSize' => 2,
        'maxPreserveNewline' => 0,
        'preserveNewlines' => false,
    ];

    public bool $minify = false;

    public array $minifyOptions = [
        'collapseWhitespace' => true,
        'minifyCSS' => false,
        'caseSensitive' => true,
        'removeEmptyAttributes' => true,
    ];

    public ValidationLevel $validationLevel;

    public string $filePath = '.';

    public array $juiceOptions = [];

    public array $juicePreserveTags = [];

    public function __construct(array $config = [])
    {
        $this->validationLevel = ValidationLevel::soft;

        foreach ($config as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    public function toJson(): string
    {
        return json_encode([
            'fonts' => $this->fonts,
            'keepComments' => $this->keepComments,
            'ignoreIncludes' => $this->ignoreIncludes,
            'beautify' => $this->beautify,
            'beautifyOptions' => $this->beautifyOptions,
            'minify' => $this->minify,
            'minifyOptions' => $this->minifyOptions,
            'validationLevel' => $this->validationLevel->value,
            'filePath' => $this->filePath,
            'juiceOptions' => $this->juiceOptions,
            'juicePreserveTags' => $this->juicePreserveTags,
        ]);
    }

    public function keepComments(): self
    {
        $this->keepComments = true;

        return $this;
    }

    public function removeComments(): self
    {
        $this->keepComments = false;

        return $this;
    }

    public function ignoreIncludes(bool $ignoreIncludes = true): self
    {
        $this->ignoreIncludes = $ignoreIncludes;

        return $this;
    }

    public function beautify(bool $beautify = true): self
    {
        $this->beautify = $beautify;

        return $this;
    }

    public function minify(bool $minify = true): self
    {
        $this->minify = $minify;

        return $this;
    }

    public function validationLevel(ValidationLevel $validationLevel): self
    {
        $this->validationLevel = $validationLevel;

        return $this;
    }

    public function filePath(string $filePath = '.'): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function __call(string $name, array $arguments): self
    {
        $property = $this->prepareProperty($name);

        if (! property_exists($this, $property)) {
            throw new BadMethodCallException("Method {$name} does not exist.");
        }

        if (str_starts_with($name, 'add')) {
            return $this->addOption($property, $arguments[0], $arguments[1]);
        }

        if (str_starts_with($name, 'remove')) {
            return $this->removeOption($property, $arguments[0]);
        }

        return $this->setOptions($property, $arguments[0]);
    }

    protected function prepareProperty(string $property): string
    {
        $property = lcfirst(preg_replace('/^add|remove|set/', '', $property));

        if (strlen($property) > 0 && substr($property, -1) !== 's') {
            $property .= 's';
        }

        return $property;
    }

    protected function setOptions(string $property, array $options): self
    {
        $this->{$property} = $options;

        return $this;
    }

    protected function addOption(string $property, string $name, mixed $value): self
    {
        $this->{$property}[$name] = $value;

        return $this;
    }

    protected function removeOption(string $property, string $name): self
    {
        unset($this->{$property}[$name]);

        return $this;
    }
}
