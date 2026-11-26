<?php

declare(strict_types=1);

namespace DefectiveCode\MJML;

use TypeError;
use BadMethodCallException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @method Config addFont(string $font, string $url)
 * @method Config removeFont(string $font)
 * @method Config setFonts(array<string, string> $fonts)
 * @method Config addBeautifyOption(string $option, mixed $value)
 * @method Config removeBeautifyOption(string $option)
 * @method Config setBeautifyOptions(array<string, mixed> $options)
 * @method Config addJuiceOption(string $option, mixed $value)
 * @method Config removeJuiceOption(string $option)
 * @method Config setJuiceOptions(array<string, mixed> $options)
 * @method Config addJuicePreserveTag(string $tag, mixed $value)
 * @method Config removeJuicePreserveTag(string $tag)
 * @method Config setJuicePreserveTags(array<string, mixed> $tags)
 */
class Config
{
    /** @var array<mixed> */
    public array $fonts = [
        'Open Sans' => 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700',
        'Droid Sans' => 'https://fonts.googleapis.com/css?family=Droid+Sans:300,400,500,700',
        'Lato' => 'https://fonts.googleapis.com/css?family=Lato:300,400,500,700',
        'Roboto' => 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700',
        'Ubuntu' => 'https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700',
    ];

    public bool $keepComments = true;

    public bool $ignoreIncludes = true;

    public bool $beautify = false;

    /** @var array<mixed> */
    public array $beautifyOptions = [
        'indentSize' => 2,
        'wrapAttributesIndentSize' => 2,
        'maxPreserveNewlines' => 0,
        'preserveNewlines' => false,
    ];

    public bool $minify = false;

    public ValidationLevel $validationLevel;

    public string $filePath = '.';

    /** @var string|array<string>|null */
    public string|array|null $includePath = null;

    /** @var array<mixed> */
    public array $juiceOptions = [];

    /** @var array<mixed> */
    public array $juicePreserveTags = [];

    /** @param array<mixed> $config */
    public function __construct(array $config = [])
    {
        $this->validationLevel = ValidationLevel::soft;

        foreach ($config as $property => $value) {
            $property = Str::camel((string) $property);

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
            'validationLevel' => $this->validationLevel->value,
            'filePath' => $this->filePath,
            'includePath' => $this->includePath,
            'juiceOptions' => $this->juiceOptions,
            'juicePreserveTags' => $this->juicePreserveTags,
        ]) ?: throw new TypeError(json_last_error_msg());
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

    /** @param string|array<string>|null $includePath */
    public function includePath(string|array|null $includePath = null): self
    {
        $this->includePath = $includePath;

        return $this;
    }

    /** @param array<mixed> $arguments */
    public function __call(string $name, array $arguments): self
    {
        $property = $this->prepareProperty($name);

        if (! property_exists($this, $property)) {
            throw new BadMethodCallException("Method {$name} does not exist.");
        }

        if (str_starts_with($name, 'add')) {
            return $this->addOption($property, Arr::string($arguments, 0), $arguments[1]);
        }

        if (str_starts_with($name, 'remove')) {
            return $this->removeOption($property, Arr::string($arguments, 0));
        }

        return $this->setOptions($property, Arr::array($arguments, 0));
    }

    protected function prepareProperty(string $property): string
    {
        $property = Str::of($property)->replaceMatches('/^add|remove|set/', '')->lcfirst()->toString();

        if (strlen($property) > 0 && substr($property, -1) !== 's') {
            $property .= 's';
        }

        return $property;
    }

    /** @param array<mixed> $options */
    protected function setOptions(string $property, array $options): self
    {
        $this->{$property} = $options;

        return $this;
    }

    protected function addOption(string $property, string $name, mixed $value): self
    {
        $options = Arr::array(get_object_vars($this), $property);
        $options[$name] = $value;

        return $this->setOptions($property, $options);
    }

    protected function removeOption(string $property, string $name): self
    {
        $options = Arr::array(get_object_vars($this), $property);
        unset($options[$name]);

        return $this->setOptions($property, $options);
    }
}
