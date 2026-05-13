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

    protected const int MAX_INLINE_ARGUMENT_LENGTH = 30000;

    protected const string FILE_ARGUMENT_PREFIX = '--mjml-file=';

    protected Config $config;

    public function __construct(?Config $config = null)
    {
        $this->config = $config ?? new Config;
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
        $inputFile = $this->writeLargeMjmlToInputFile($mjml);

        try {
            $mjmlArgument = $inputFile === null ? $mjml : self::FILE_ARGUMENT_PREFIX.$inputFile;
            $options = escapeshellarg($this->config->toJson());

            [$output, $code] = $this->exec(escapeshellarg($mjmlArgument)." {$options}");
        } finally {
            $this->deleteInputFile($inputFile);
        }

        if ($code > 0) {
            throw new Exception($output);
        }

        if ($this->config->minify) {
            $output = $this->minifyHtml($output, $this->config->keepComments);
        }

        return $output;
    }

    protected function minifyHtml(string $html, bool $keepComments = true): string
    {
        if (! $keepComments) {
            $html = preg_replace('/<!--(?![<\[])(?!.*?(?:\[if|endif)).*?-->/s', '', $html);
        }

        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);

        return trim($html);
    }

    protected function writeLargeMjmlToInputFile(string $mjml): ?string
    {
        if (strlen($mjml) <= self::MAX_INLINE_ARGUMENT_LENGTH) {
            return null;
        }

        $inputFile = $this->createInputFile();

        if ($inputFile === null) {
            throw new Exception('Unable to create MJML input file.');
        }

        if ($this->writeInputFile($inputFile, $mjml)) {
            return $inputFile;
        }

        $this->deleteInputFile($inputFile);

        throw new Exception('Unable to write MJML input file.');
    }

    protected function createInputFile(): ?string
    {
        return tempnam(sys_get_temp_dir(), 'mjml_input') ?: null;
    }

    protected function writeInputFile(string $inputFile, string $mjml): bool
    {
        return file_put_contents($inputFile, $mjml) !== false;
    }

    protected function deleteInputFile(?string $inputFile): void
    {
        if ($inputFile === null) {
            return;
        }

        if (! file_exists($inputFile)) {
            return;
        }

        @unlink($inputFile);
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

    /**
     * @return array{string, int}
     */
    protected function exec(string $arguments): array
    {
        $binary = PullBinary::resolveBinaryPath(php_uname('s'), php_uname('m'));

        $output = tempnam(sys_get_temp_dir(), 'mjml_output');

        exec("{$binary} {$arguments} > {$output} 2>&1", result_code: $code);

        return [
            file_get_contents($output),
            $code,
        ];
    }
}
