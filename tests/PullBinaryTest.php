<?php

declare(strict_types=1);

namespace DefectiveCode\MJML\Tests;

use DefectiveCode\MJML\PullBinary;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use RuntimeException;

class PullBinaryTest extends TestCase
{
    #[Test]
    public function itReadsVersionFromFile(): void
    {
        $version = PullBinary::getVersion();

        $this->assertNotEmpty($version);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $version);
    }

    #[Test]
    public function itThrowsExceptionWhenVersionFileIsMissing(): void
    {
        $versionFile = __DIR__.'/../VERSION';
        $originalContent = file_get_contents($versionFile);
        unlink($versionFile);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('VERSION file not found.');
            PullBinary::getVersion();
        } finally {
            file_put_contents($versionFile, $originalContent);
        }
    }

    #[Test]
    #[DataProvider('binaryPathProvider')]
    public function itResolvesBinaryPath(string $os, string $arch, string $expectedSuffix): void
    {
        $path = PullBinary::resolveBinaryPath($os, $arch);

        $this->assertStringEndsWith($expectedSuffix, $path);
        $this->assertStringContainsString('/bin/', $path);
    }

    public static function binaryPathProvider(): array
    {
        return [
            'darwin arm64' => ['darwin', 'arm64', 'mjml-darwin-arm64'],
            'darwin x64' => ['darwin', 'x64', 'mjml-darwin-x64'],
            'linux arm64' => ['linux', 'arm64', 'mjml-linux-arm64'],
            'linux x64' => ['linux', 'x64', 'mjml-linux-x64'],
            'darwin aarch64' => ['darwin', 'aarch64', 'mjml-darwin-arm64'],
            'linux amd64' => ['linux', 'amd64', 'mjml-linux-x64'],
            'linux x86_64' => ['linux', 'x86_64', 'mjml-linux-x64'],
        ];
    }

    #[Test]
    #[DataProvider('architectureProvider')]
    public function itResolvesArchitecture(string $input, string $expected): void
    {
        $method = $this->getProtectedMethod('resolveArchitecture');
        $result = $method->invoke(null, $input);

        $this->assertEquals($expected, $result);
    }

    public static function architectureProvider(): array
    {
        return [
            'arm64' => ['arm64', 'arm64'],
            'aarch64' => ['aarch64', 'arm64'],
            'x64' => ['x64', 'x64'],
            'amd64' => ['amd64', 'x64'],
            'x86_64' => ['x86_64', 'x64'],
            'ARM64 uppercase' => ['ARM64', 'arm64'],
            'X64 uppercase' => ['X64', 'x64'],
        ];
    }

    #[Test]
    public function itThrowsExceptionForUnsupportedArchitecture(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported architecture: i386');

        PullBinary::resolveBinaryPath('linux', 'i386');
    }

    #[Test]
    #[DataProvider('operatingSystemProvider')]
    public function itResolvesOperatingSystem(string $input, string $expected): void
    {
        $method = $this->getProtectedMethod('resolveOperatingSystem');
        $result = $method->invoke(null, $input);

        $this->assertEquals($expected, $result);
    }

    public static function operatingSystemProvider(): array
    {
        return [
            'darwin' => ['darwin', 'darwin'],
            'linux' => ['linux', 'linux'],
            'Darwin uppercase' => ['Darwin', 'darwin'],
            'LINUX uppercase' => ['LINUX', 'linux'],
        ];
    }

    #[Test]
    public function itThrowsExceptionForUnsupportedOperatingSystem(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported operating system: windows');

        PullBinary::resolveBinaryPath('windows', 'x64');
    }

    #[Test]
    public function itResolvesDownloadUrl(): void
    {
        $method = $this->getProtectedMethod('resolveDownloadUrl');
        $url = $method->invoke(null, 'darwin', 'arm64');

        $version = PullBinary::getVersion();
        $expectedUrl = PullBinary::BASE_DOWNLOAD_URL.$version.'/mjml-darwin-arm64';

        $this->assertEquals($expectedUrl, $url);
    }

    #[Test]
    public function itResolvesDownloadUrlWithArchitectureNormalization(): void
    {
        $method = $this->getProtectedMethod('resolveDownloadUrl');
        $url = $method->invoke(null, 'linux', 'amd64');

        $version = PullBinary::getVersion();

        $this->assertStringStartsWith(PullBinary::BASE_DOWNLOAD_URL.$version.'/mjml-linux-x64', $url);
    }

    #[Test]
    public function itHasCorrectAllowedBinaries(): void
    {
        $expected = [
            'darwin-arm64',
            'darwin-x64',
            'linux-arm64',
            'linux-arm64-musl',
            'linux-x64',
            'linux-x64-musl',
        ];

        $this->assertEquals($expected, PullBinary::ALLOWED_BINARIES);
    }

    #[Test]
    public function itHasCorrectBaseDownloadUrl(): void
    {
        $this->assertEquals(
            'https://downloads.defectivecode.com/packages/mjml/',
            PullBinary::BASE_DOWNLOAD_URL
        );
    }

    #[Test]
    public function itThrowsExceptionForUnsupportedBinaryViaCallStatic(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported binary.');

        PullBinary::{'windows-x64'}();
    }

    #[Test]
    #[DataProvider('binaryPartsProvider')]
    public function itExtractsBinaryParts(string $binary, array $expected): void
    {
        $method = $this->getProtectedMethod('extractBinaryParts');
        $result = $method->invoke(null, $binary);

        $this->assertEquals($expected, $result);
    }

    public static function binaryPartsProvider(): array
    {
        return [
            'darwin-arm64' => ['darwin-arm64', ['darwin', 'arm64', 'glibc']],
            'darwin-x64' => ['darwin-x64', ['darwin', 'x64', 'glibc']],
            'linux-arm64' => ['linux-arm64', ['linux', 'arm64', 'glibc']],
            'linux-x64' => ['linux-x64', ['linux', 'x64', 'glibc']],
            'linux-arm64-musl' => ['linux-arm64-musl', ['linux', 'arm64', 'musl']],
            'linux-x64-musl' => ['linux-x64-musl', ['linux', 'x64', 'musl']],
        ];
    }

    #[Test]
    public function itResolvesLibcForDarwin(): void
    {
        $method = $this->getProtectedMethod('resolveLibc');
        $result = $method->invoke(null, 'darwin');

        $this->assertEquals('glibc', $result);
    }

    protected function getProtectedMethod(string $methodName): \ReflectionMethod
    {
        $reflection = new ReflectionClass(PullBinary::class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}
