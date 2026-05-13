<?php

declare(strict_types=1);

namespace DefectiveCode\MJML\Tests;

use Exception;
use TypeError;
use Mockery\MockInterface;
use DefectiveCode\MJML\MJML;
use DefectiveCode\MJML\Config;
use PHPUnit\Framework\Attributes\Test;

class MJMLTest extends TestCase
{
    protected string $validMjml = <<<'MJML'
        <mjml>
            <mj-body>
                <mj-section>
                    <mj-column>
                        <mj-text>
                            Hello World!
                        </mj-text>
                    </mj-column>
                </mj-section>
            </mj-body>
        </mjml>
    MJML;

    protected string $invalidMjml = '<mjml><mj-body><mj-column></mjml></mj-body>';

    #[Test]
    public function itPassesTheMjmlToTheMJMLBinary(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args): bool {
                return str_contains($args, $this->validMjml) && ! str_contains($args, '--mjml-file=');
            })
            ->andReturn([
                '<html></html>',
                0,
            ]);

        $mjml->render($this->validMjml);
    }

    #[Test]
    public function itPassesLargeMjmlToTheMJMLBinaryByFilePath(): void
    {
        $largeMjml = $this->largeMjml();
        $inputFile = null;
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args) use ($largeMjml, &$inputFile): bool {
                if (str_contains($args, $largeMjml)) {
                    return false;
                }

                if (! preg_match("/'--mjml-file=([^']+)'/", $args, $matches)) {
                    return false;
                }

                $inputFile = $matches[1];

                return file_exists($inputFile) && file_get_contents($inputFile) === $largeMjml;
            })
            ->andReturn([
                '<html></html>',
                0,
            ]);

        $mjml->render($largeMjml);

        $this->assertNotNull($inputFile);
        $this->assertFileDoesNotExist($inputFile);
    }

    #[Test]
    public function itPassesThresholdSizedMjmlInline(): void
    {
        $mjmlContent = str_repeat('A', 30000 - strlen($this->mjmlWrapper('')));
        $thresholdMjml = $this->mjmlWrapper($mjmlContent);
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args) use ($thresholdMjml): bool {
                return str_contains($args, $thresholdMjml) && ! str_contains($args, '--mjml-file=');
            })
            ->andReturn([
                '<html></html>',
                0,
            ]);

        $mjml->render($thresholdMjml);
    }

    #[Test]
    public function itPassesAboveThresholdMjmlByFilePath(): void
    {
        $mjmlContent = str_repeat('A', 30001 - strlen($this->mjmlWrapper('')));
        $aboveThresholdMjml = $this->mjmlWrapper($mjmlContent);
        $inputFile = null;
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args) use ($aboveThresholdMjml, &$inputFile): bool {
                if (str_contains($args, $aboveThresholdMjml)) {
                    return false;
                }

                if (! preg_match("/'--mjml-file=([^']+)'/", $args, $matches)) {
                    return false;
                }

                $inputFile = $matches[1];

                return file_exists($inputFile) && file_get_contents($inputFile) === $aboveThresholdMjml;
            })
            ->andReturn([
                '<html></html>',
                0,
            ]);

        $mjml->render($aboveThresholdMjml);

        $this->assertNotNull($inputFile);
        $this->assertFileDoesNotExist($inputFile);
    }

    #[Test]
    public function itRemovesLargeMjmlInputFileWhenRenderingFails(): void
    {
        $largeMjml = $this->largeMjml();
        $inputFile = null;
        $exceptionThrown = false;
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args) use (&$inputFile): bool {
                if (! preg_match("/'--mjml-file=([^']+)'/", $args, $matches)) {
                    return false;
                }

                $inputFile = $matches[1];

                return file_exists($inputFile);
            })
            ->andReturn([
                'Invalid MJML',
                1,
            ]);

        try {
            $mjml->render($largeMjml);
        } catch (Exception $exception) {
            $exceptionThrown = true;

            $this->assertSame('Invalid MJML', $exception->getMessage());
        }

        $this->assertTrue($exceptionThrown);
        $this->assertNotNull($inputFile);
        $this->assertFileDoesNotExist($inputFile);
    }

    #[Test]
    public function itRemovesLargeMjmlInputFileWhenPreparingArgumentsFails(): void
    {
        $largeMjml = $this->largeMjml();
        $inputFile = tempnam(sys_get_temp_dir(), 'mjml_input');
        $handle = fopen(__FILE__, 'r');
        $exceptionThrown = false;
        $config = new Config;
        $config->fonts = [$handle];

        file_put_contents($inputFile, $largeMjml);

        $mjml = $this->mockShellCall($config);

        $mjml->shouldReceive('writeLargeMjmlToInputFile')
            ->once()
            ->andReturn($inputFile);

        $mjml->shouldReceive('exec')
            ->never();

        try {
            $mjml->render($largeMjml);
        } catch (TypeError $exception) {
            $exceptionThrown = true;
        } finally {
            fclose($handle);
        }

        $this->assertTrue($exceptionThrown);
        $this->assertFileDoesNotExist($inputFile);
    }

    #[Test]
    public function itThrowsWhenLargeMjmlInputFileCannotBeCreated(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to create MJML input file.');

        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('createInputFile')
            ->once()
            ->andReturn(null);

        $mjml->shouldReceive('exec')
            ->never();

        $mjml->render($this->largeMjml());
    }

    #[Test]
    public function itRemovesInputFileWhenLargeMjmlCannotBeWritten(): void
    {
        $inputFile = tempnam(sys_get_temp_dir(), 'mjml_input');
        $exceptionThrown = false;
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('createInputFile')
            ->once()
            ->andReturn($inputFile);

        $mjml->shouldReceive('writeInputFile')
            ->once()
            ->andReturn(false);

        $mjml->shouldReceive('exec')
            ->never();

        try {
            $mjml->render($this->largeMjml());
        } catch (Exception $exception) {
            $exceptionThrown = true;

            $this->assertSame('Unable to write MJML input file.', $exception->getMessage());
        }

        $this->assertTrue($exceptionThrown);
        $this->assertFileDoesNotExist($inputFile);
    }

    #[Test]
    public function itPassesTheConfigToTheMJMLBinary(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args): bool {
                return str_contains($args, (new Config)->toJson());
            })
            ->andReturn([
                '<html></html>',
                0,
            ]);

        $mjml->render($this->validMjml);
    }

    #[Test]
    public function itThrowsAnExceptionIfExitCodeIsGreaterThan0(): void
    {
        $this->expectException(Exception::class);

        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args): bool {
                return str_contains($args, (new Config)->toJson());
            })
            ->andReturn([
                '<html></html>',
                1,
            ]);

        $mjml->render($this->validMjml);
    }

    #[Test]
    public function itReturnsTheConfig(): void
    {
        $mjml = new MJML;

        $this->assertInstanceOf(Config::class, $mjml->getConfig());
    }

    #[Test]
    public function itSetsTheConfig(): void
    {
        $config = new Config;
        $config->minify();

        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args): bool {
                return str_contains($args, (new Config)->toJson());
            })
            ->andReturn([
                '<html></html>',
                0,
            ]);

        $mjml->render($this->validMjml);
    }

    #[Test]
    public function itForwardsCallsToTheConfigObject(): void
    {
        $mjml = new MJML;
        $mjml->minify()->beautify()->removeComments();

        $this->assertTrue($mjml->getConfig()->minify);
        $this->assertTrue($mjml->getConfig()->beautify);
        $this->assertFalse($mjml->getConfig()->keepComments);
    }

    protected function mockShellCall(?Config $config = null): MockInterface
    {
        $mjml = $this->mock(MJML::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mjml->setConfig($config ?? new Config);

        return $mjml;
    }

    protected function largeMjml(): string
    {
        return $this->mjmlWrapper(str_repeat('Hello World ', 10000));
    }

    protected function mjmlWrapper(string $content): string
    {
        return '<mjml><mj-body><mj-section><mj-column><mj-text>'.$content.'</mj-text></mj-column></mj-section></mj-body></mjml>';
    }

    #[Test]
    public function itMinifiesHtmlWhenEnabled(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html>    <body>   <p>Hello</p>   </body>    </html>',
                0,
            ]);

        $result = $mjml->minify()->render($this->validMjml);

        $this->assertEquals('<html><body><p>Hello</p></body></html>', $result);
    }

    #[Test]
    public function itKeepsCommentsWhenMinifyingByDefault(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!-- This is a comment --><body><p>Hello</p></body></html>',
                0,
            ]);

        $result = $mjml->minify()->render($this->validMjml);

        $this->assertStringContainsString('<!-- This is a comment -->', $result);
    }

    #[Test]
    public function itRemovesCommentsWhenRemoveCommentsIsCalled(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!-- This is a comment --><body><p>Hello</p></body></html>',
                0,
            ]);

        $result = $mjml->minify()->removeComments()->render($this->validMjml);

        $this->assertStringNotContainsString('This is a comment', $result);
    }

    #[Test]
    public function itPreservesOutlookMsoConditional(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!--[if mso]><table><tr><td><![endif]--><p>Hello</p><!--[if mso]></td></tr></table><![endif]--></html>',
                0,
            ]);

        $result = $mjml->minify()->removeComments()->render($this->validMjml);

        $this->assertStringContainsString('<!--[if mso]>', $result);
        $this->assertStringContainsString('<![endif]-->', $result);
    }

    #[Test]
    public function itPreservesOutlookNotMsoConditional(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!--[if !mso]><div class="mobile-only"><![endif]--><p>Hello</p><!--[if !mso]></div><![endif]--></html>',
                0,
            ]);

        $result = $mjml->minify()->removeComments()->render($this->validMjml);

        $this->assertStringContainsString('<!--[if !mso]>', $result);
    }

    #[Test]
    public function itPreservesOutlookGteMsoConditional(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!--[if gte mso 9]><xml><o:OfficeDocumentSettings></o:OfficeDocumentSettings></xml><![endif]--></html>',
                0,
            ]);

        $result = $mjml->minify()->removeComments()->render($this->validMjml);

        $this->assertStringContainsString('<!--[if gte mso 9]>', $result);
    }

    #[Test]
    public function itPreservesOutlookLteMsoConditional(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!--[if lte mso 11]><style>.outlook-fix { width: 100%; }</style><![endif]--></html>',
                0,
            ]);

        $result = $mjml->minify()->removeComments()->render($this->validMjml);

        $this->assertStringContainsString('<!--[if lte mso 11]>', $result);
    }

    #[Test]
    public function itPreservesOutlookMsoOrIeConditional(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!--[if (gte mso 9)|(IE)]><table><tr><td><![endif]--><p>Hello</p><!--[if (gte mso 9)|(IE)]></td></tr></table><![endif]--></html>',
                0,
            ]);

        $result = $mjml->minify()->removeComments()->render($this->validMjml);

        $this->assertStringContainsString('<!--[if (gte mso 9)|(IE)]>', $result);
    }

    #[Test]
    public function itPreservesOutlookMsoPipeIeConditional(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!--[if mso | IE]><table role="presentation"><![endif]--><p>Hello</p><!--[if mso | IE]></table><![endif]--></html>',
                0,
            ]);

        $result = $mjml->minify()->removeComments()->render($this->validMjml);

        $this->assertStringContainsString('<!--[if mso | IE]>', $result);
    }

    #[Test]
    public function itPreservesDownlevelHiddenConditional(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!--[if !mso]><!--><div class="non-outlook">Content</div><!--<![endif]--></html>',
                0,
            ]);

        $result = $mjml->minify()->removeComments()->render($this->validMjml);

        $this->assertStringContainsString('<!--[if !mso]><!-->', $result);
        $this->assertStringContainsString('<!--<![endif]-->', $result);
    }

    #[Test]
    public function itPreservesOutlookGtMsoConditional(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!--[if gt mso 15]><style>.new-outlook { display: block; }</style><![endif]--></html>',
                0,
            ]);

        $result = $mjml->minify()->removeComments()->render($this->validMjml);

        $this->assertStringContainsString('<!--[if gt mso 15]>', $result);
    }

    #[Test]
    public function itPreservesOutlookLtMsoConditional(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!--[if lt mso 12]><style>.old-outlook { display: block; }</style><![endif]--></html>',
                0,
            ]);

        $result = $mjml->minify()->removeComments()->render($this->validMjml);

        $this->assertStringContainsString('<!--[if lt mso 12]>', $result);
    }

    #[Test]
    public function itPreservesIeVersionConditional(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html><!--[if IE 9]><link rel="stylesheet" href="ie9.css"><![endif]--></html>',
                0,
            ]);

        $result = $mjml->minify()->removeComments()->render($this->validMjml);

        $this->assertStringContainsString('<!--[if IE 9]>', $result);
    }

    #[Test]
    public function itDoesNotMinifyWhenDisabled(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->andReturn([
                '<html>    <body>   <p>Hello</p>   </body>    </html>',
                0,
            ]);

        $result = $mjml->minify(false)->render($this->validMjml);

        $this->assertStringContainsString('    ', $result);
    }
}
