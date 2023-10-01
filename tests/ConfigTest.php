<?php

declare(strict_types=1);

namespace DefectiveCode\MJML\Tests;

use BadMethodCallException;
use DefectiveCode\MJML\Config;
use DefectiveCode\MJML\ValidationLevel;

class ConfigTest extends TestCase
{
    /** @test */
    public function itSetsThePropertiesFromAnArray(): void
    {
        $arrayConfig = [
            'keepComments' => false,
            'ignoreIncludes' => true,
        ];

        $config = new Config($arrayConfig);

        $this->assertFalse($config->keepComments);
        $this->assertTrue($config->ignoreIncludes);
    }

    /** @test */
    public function itReturnsAJsonObjectOfTheConfig(): void
    {
        $this->assertEquals(
            '{"fonts":{"Open Sans":"https:\/\/fonts.googleapis.com\/css?family=Open+Sans:300,400,500,700","Droid Sans":"https:\/\/fonts.googleapis.com\/css?family=Droid+Sans:300,400,500,700","Lato":"https:\/\/fonts.googleapis.com\/css?family=Lato:300,400,500,700","Roboto":"https:\/\/fonts.googleapis.com\/css?family=Roboto:300,400,500,700","Ubuntu":"https:\/\/fonts.googleapis.com\/css?family=Ubuntu:300,400,500,700"},"keepComments":true,"ignoreIncludes":false,"beautify":false,"beautifyOptions":{"indentSize":2,"wrapAttributesIndentSize":2,"maxPreserveNewline":0,"preserveNewlines":false},"minify":false,"minifyOptions":{"collapseWhitespace":true,"minifyCSS":false,"caseSensitive":true,"removeEmptyAttributes":true},"validationLevel":"soft","filePath":".","juiceOptions":[],"juicePreserveTags":[]}',
            (new Config())->toJson()
        );
    }

    /** @test */
    public function itSetsKeepsComments(): void
    {
        $this->assertTrue((new Config)->keepComments()->keepComments);
    }

    /** @test */
    public function itSetsRemovesComments(): void
    {
        $this->assertFalse((new Config)->removeComments()->keepComments);
    }

    /** @test */
    public function itSetsIgnoresIncludes(): void
    {
        $this->assertTrue((new Config)->ignoreIncludes()->ignoreIncludes);
        $this->assertFalse((new Config)->ignoreIncludes(false)->ignoreIncludes);
    }

    /** @test */
    public function itSetsBeautify(): void
    {
        $this->assertTrue((new Config)->beautify()->beautify);
        $this->assertFalse((new Config)->beautify(false)->beautify);
    }

    /** @test */
    public function itSetsMinify(): void
    {
        $this->assertTrue((new Config)->minify()->minify);
        $this->assertFalse((new Config)->minify(false)->minify);
    }

    /** @test */
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

    /** @test */
    public function itSetsTheFilePath(): void
    {
        $this->assertEquals(
            '/tmp',
            (new Config)->filePath('/tmp')->filePath
        );
    }

    /** @test */
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

    /** @test */
    public function itAddsAnOptionOnTheCorrectProperty(): void
    {
        $this->assertEquals(
            2,
            (new Config)->addBeautifyOption('indentSize', 2)->beautifyOptions['indentSize']
        );
    }

    /** @test */
    public function itRemovesAnOptionFromTheCorrectProperty(): void
    {
        $this->assertFalse(
            isset((new Config)->removeBeautifyOption('indentSize')->beautifyOptions['indentSize'])
        );
    }

    /** @test */
    public function itThrowsAnExceptionIfThePropertyDoesntExist(): void
    {
        $this->expectException(BadMethodCallException::class);

        (new Config)->setFoo();
        (new Config)->addFooOption('bar', 'baz');
        (new Config)->removeFooOption('bar');
    }
}
