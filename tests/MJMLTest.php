<?php

declare(strict_types=1);

namespace DefectiveCode\MJML\Tests;

use Exception;
use Mockery\MockInterface;
use DefectiveCode\MJML\MJML;
use DefectiveCode\MJML\Config;

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
    MJML;

    protected string $invalidMjml = '<mjml><mj-body><mj-column></mjml></mj-body>';

    /** @test */
    public function itPassesTheMjmlToTheMJMLBinary(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args): bool {
                return str_contains($args, $this->validMjml);
            })
            ->andReturn([
                '<html></html>',
                0,
            ]);

        $mjml->render($this->validMjml);
    }

    /** @test */
    public function itPassesTheConfigToTheMJMLBinary(): void
    {
        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args): bool {
                return str_contains($args, (new Config())->toJson());
            })
            ->andReturn([
                '<html></html>',
                0,
            ]);

        $mjml->render($this->validMjml);
    }

    /** @test */
    public function itThrowsAnExceptionIfExitCodeIsGreaterThan0(): void
    {
        $this->expectException(Exception::class);

        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args): bool {
                return str_contains($args, (new Config())->toJson());
            })
            ->andReturn([
                '<html></html>',
                1,
            ]);

        $mjml->render($this->validMjml);
    }

    /** @test */
    public function itReturnsTheConfig(): void
    {
        $mjml = new MJML();

        $this->assertInstanceOf(Config::class, $mjml->getConfig());
    }

    /** @test */
    public function itSetsTheConfig(): void
    {
        $config = new Config;
        $config->minify();

        $mjml = $this->mockShellCall();

        $mjml->shouldReceive('exec')
            ->once()
            ->withArgs(function ($args): bool {
                return str_contains($args, (new Config())->toJson());
            })
            ->andReturn([
                '<html></html>',
                0,
            ]);

        $mjml->render($this->validMjml);
    }

    /** @test */
    public function itForwardsCallsToTheConfigObject(): void
    {
        $mjml = new MJML();
        $mjml->minify()->beautify()->removeComments();

        $this->assertTrue($mjml->getConfig()->minify);
        $this->assertTrue($mjml->getConfig()->beautify);
        $this->assertFalse($mjml->getConfig()->keepComments);
    }

    protected function mockShellCall(Config $config = null): MockInterface
    {
        $mjml = $this->mock(MJML::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mjml->setConfig($config ?? new Config);

        return $mjml;
    }
}
