<?php

declare(strict_types=1);

namespace DefectiveCode\MJML\Tests;

use BadMethodCallException;
use DefectiveCode\MJML\Config;
use PHPUnit\Framework\Attributes\Test;
use DefectiveCode\MJML\ValidationLevel;

class ConfigTest extends TestCase
{
    #[Test]
    public function itSetsThePropertiesFromAnArray(): void
    {
        $arrayConfig = [
            'keep_comments' => false,
            'ignore_includes' => false,
            'include_path' => ['/tmp/includes'],
        ];

        $config = new Config($arrayConfig);

        $this->assertFalse($config->keepComments);
        $this->assertFalse($config->ignoreIncludes);
        $this->assertSame(['/tmp/includes'], $config->includePath);
    }

    #[Test]
    public function itReturnsAJsonObjectOfTheConfig(): void
    {
        $this->assertEquals(
            '{"fonts":{"Open Sans":"https:\/\/fonts.googleapis.com\/css?family=Open+Sans:300,400,500,700","Droid Sans":"https:\/\/fonts.googleapis.com\/css?family=Droid+Sans:300,400,500,700","Lato":"https:\/\/fonts.googleapis.com\/css?family=Lato:300,400,500,700","Roboto":"https:\/\/fonts.googleapis.com\/css?family=Roboto:300,400,500,700","Ubuntu":"https:\/\/fonts.googleapis.com\/css?family=Ubuntu:300,400,500,700"},"keepComments":true,"ignoreIncludes":true,"beautify":false,"beautifyOptions":{"indentSize":2,"wrapAttributesIndentSize":2,"maxPreserveNewline":0,"preserveNewlines":false},"minify":false,"validationLevel":"soft","filePath":".","includePath":null,"juiceOptions":[],"juicePreserveTags":[]}',
            (new Config)->toJson()
        );
    }

    #[Test]
    public function itSetsKeepsComments(): void
    {
        $this->assertTrue((new Config)->keepComments()->keepComments);
    }

    #[Test]
    public function itSetsRemovesComments(): void
    {
        $this->assertFalse((new Config)->removeComments()->keepComments);
    }

    #[Test]
    public function itSetsIgnoresIncludes(): void
    {
        $this->assertTrue((new Config)->ignoreIncludes()->ignoreIncludes);
        $this->assertFalse((new Config)->ignoreIncludes(false)->ignoreIncludes);
    }

    #[Test]
    public function itSetsBeautify(): void
    {
        $this->assertTrue((new Config)->beautify()->beautify);
        $this->assertFalse((new Config)->beautify(false)->beautify);
    }

    #[Test]
    public function itSetsMinify(): void
    {
        $this->assertTrue((new Config)->minify()->minify);
        $this->assertFalse((new Config)->minify(false)->minify);
    }

    #[Test]
    public function itSetsTheValidationLevel(): void
    {
        $this->assertEquals(
            ValidationLevel::soft,
            (new Config)->validationLevel(ValidationLevel::soft)->validationLevel
        );

        $this->assertEquals(
            ValidationLevel::skip,
            (new Config)->validationLevel(ValidationLevel::skip)->validationLevel
        );

        $this->assertEquals(
            ValidationLevel::strict,
            (new Config)->validationLevel(ValidationLevel::strict)->validationLevel
        );
    }

    #[Test]
    public function itSetsTheFilePath(): void
    {
        $this->assertEquals(
            '/tmp',
            (new Config)->filePath('/tmp')->filePath
        );
    }

    #[Test]
    public function itSetsTheIncludePath(): void
    {
        $this->assertSame(
            '/tmp/includes',
            (new Config)->includePath('/tmp/includes')->includePath
        );

        $this->assertSame(
            ['/tmp/includes', '/tmp/shared'],
            (new Config)->includePath(['/tmp/includes', '/tmp/shared'])->includePath
        );
    }

    #[Test]
    public function itSetsTheOptionsOnTheCorrectProperty(): void
    {
        $fonts = [
            'Open Sans' => 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700',
        ];

        $this->assertEquals(
            $fonts,
            (new Config)->setFonts($fonts)->fonts
        );
    }

    #[Test]
    public function itAddsAnOptionOnTheCorrectProperty(): void
    {
        $this->assertEquals(
            2,
            (new Config)->addBeautifyOption('indentSize', 2)->beautifyOptions['indentSize']
        );
    }

    #[Test]
    public function itRemovesAnOptionFromTheCorrectProperty(): void
    {
        $this->assertFalse(
            isset((new Config)->removeBeautifyOption('indentSize')->beautifyOptions['indentSize'])
        );
    }

    #[Test]
    public function itThrowsAnExceptionIfThePropertyDoesntExist(): void
    {
        $this->expectException(BadMethodCallException::class);

        (new Config)->setFoo();
        (new Config)->addFooOption('bar', 'baz');
        (new Config)->removeFooOption('bar');
    }
}
