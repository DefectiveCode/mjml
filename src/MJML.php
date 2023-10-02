<?php

declare(strict_types=1);

namespace DefectiveCode\MJML;

use Exception;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @method MJML addFont(string $font, string $url)
 * @method MJML removeFont(string $font)
 * @method MJML setFonts(array $fonts)
 * @method MJML addBeautifyOption(string $option, mixed $value)
 * @method MJML removeBeautifyOption(string $option)
 * @method MJML setBeautifyOptions(array $options)
 * @method MJML addMinifyOption(string $option, mixed $value)
 * @method MJML removeMinifyOption(string $option)
 * @method MJML setMinifyOptions(array $options)
 * @method MJML addJuiceOption(string $option, mixed $value)
 * @method MJML removeJuiceOption(string $option)
 * @method MJML setJuiceOptions(array $options)
 * @method MJML addJuicePreserveTag(string $tag, mixed $value)
 * @method MJML removeJuicePreserveTag(string $tag)
 * @method MJML setJuicePreserveTags(array $tags)
 * @method MJML keepComments()
 * @method MJML removeComments()
 * @method MJML ignoreIncludes(bool $ignoreIncludes = true)
 * @method MJML beautify(bool $beautify = true)
 * @method MJML minify(bool $minify = true)
 * @method MJML validationLevel(ValidationLevel $validationLevel)
 * @method MJML filePath(string $filePath = '.')
 */
class MJML
{
    use ForwardsCalls;

    protected Config $config;

    public function __construct(Config $config = null)
    {
        $this->config = $config ?? new Config();
    }

    public function isValid(string $mjml): bool
    {
        try {
            $currentValidationLevel = $this->config->validationLevel;
            $this->validationLevel(ValidationLevel::strict);
            $this->render($mjml);
            $this->validationLevel($currentValidationLevel);

            return true;
        } catch (Exception $exception) {
            return ! str_contains($exception->getMessage(), 'ValidationError');
        }
    }

    public function render(string $mjml): string
    {
        $mjml = escapeshellarg($mjml);
        $options = escapeshellarg($this->config->toJson());

        [$output, $code] = $this->exec(__DIR__."/../bin/mjml {$mjml} {$options}");

        if ($code > 0) {
            throw new Exception($output);
        }

        return $output;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function setConfig(Config $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function __call(string $method, array $parameters): self
    {
        return $this->forwardDecoratedCallTo($this->config, $method, $parameters);
    }

    protected function exec(string $arguments): array
    {
        $binary = PullBinary::resolveBinaryPath(php_uname('s'), php_uname('m'));

        exec("{$binary} {$arguments}", $output, $code);

        return [
            implode("\n", $output),
            $code,
        ];
    }
}
