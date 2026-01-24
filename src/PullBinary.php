<?php

declare(strict_types=1);

namespace DefectiveCode\MJML;

use RuntimeException;

class PullBinary
{
    public const string BASE_DOWNLOAD_URL = 'https://downloads.defectivecode.com/packages/mjml/';

    public const array ALLOWED_BINARIES = [
        'darwin-arm64',
        'darwin-x64',
        'linux-arm64',
        'linux-arm64-musl',
        'linux-x64',
        'linux-x64-musl',
    ];

    public static function resolveBinaryPath(string $operatingSystem, string $architecture): string
    {
        $architecture = self::resolveArchitecture($architecture);
        $operatingSystem = self::resolveOperatingSystem($operatingSystem);
        $libc = self::resolveLibc($operatingSystem);

        $suffix = $libc === 'musl' ? '-musl' : '';

        return __DIR__."/../bin/mjml-{$operatingSystem}-{$architecture}{$suffix}";
    }

    public static function __callStatic(string $name, array $arguments): void
    {
        if ($name === 'all') {
            foreach (self::ALLOWED_BINARIES as $binary) {
                self::pull(...self::extractBinaryParts($binary));
            }

            return;
        }

        if (! in_array($name, self::ALLOWED_BINARIES)) {
            throw new RuntimeException('Unsupported binary.');
        }

        self::pull(...self::extractBinaryParts($name));
    }

    protected static function extractBinaryParts(string $binary): array
    {
        $parts = explode('-', $binary);
        $operatingSystem = $parts[0];
        $architecture = $parts[1];
        $libc = $parts[2] ?? 'glibc';

        return [$operatingSystem, $architecture, $libc];
    }

    protected static function hasLatestBinary(string $binaryPath, string $downloadUrl): bool
    {
        if (! file_exists($binaryPath)) {
            return false;
        }

        $headers = get_headers($downloadUrl, true);

        if (! isset($headers['Content-Length'])) {
            return false;
        }

        return filesize($binaryPath) === (int) $headers['Content-Length'];
    }

    protected static function pull(string $operatingSystem, string $architecture, string $libc = 'glibc'): void
    {
        $suffix = $libc === 'musl' ? '-musl' : '';
        $binaryPath = __DIR__."/../bin/mjml-{$operatingSystem}-{$architecture}{$suffix}";
        $downloadUrl = self::BASE_DOWNLOAD_URL.self::getVersion().'/'."mjml-{$operatingSystem}-{$architecture}{$suffix}";

        if (self::hasLatestBinary($binaryPath, $downloadUrl)) {
            echo "Latest MJML binary already exists for {$operatingSystem}-{$architecture}{$suffix}.\n";

            return;
        }

        echo "Downloading MJML binary for {$operatingSystem}-{$architecture}{$suffix}.\n";

        file_put_contents(
            $binaryPath,
            file_get_contents($downloadUrl)
        );

        echo "Granting run permissions to {$binaryPath} binary.\n";
        chmod($binaryPath, 0755);
    }

    protected static function resolveOperatingSystem(string $operatingSystem): string
    {
        return match (strtolower($operatingSystem)) {
            'darwin' => 'darwin',
            'linux' => 'linux',
            default => throw new RuntimeException("Unsupported operating system: {$operatingSystem}"),
        };
    }

    protected static function resolveArchitecture(string $architecture): string
    {
        return match (strtolower($architecture)) {
            'arm64', 'aarch64' => 'arm64',
            'x64', 'amd64', 'x86_64' => 'x64',
            default => throw new RuntimeException("Unsupported architecture: {$architecture}")
        };
    }

    protected static function resolveLibc(string $operatingSystem): string
    {
        if ($operatingSystem !== 'linux') {
            return 'glibc';
        }

        if (file_exists('/lib/ld-musl-aarch64.so.1') || file_exists('/lib/ld-musl-x86_64.so.1')) {
            return 'musl';
        }

        $ldd = @shell_exec('ldd /bin/ls 2>&1');
        if ($ldd && str_contains($ldd, 'musl')) {
            return 'musl';
        }

        return 'glibc';
    }

    protected static function resolveDownloadUrl(string $operatingSystem, string $architecture): string
    {
        $architecture = self::resolveArchitecture($architecture);
        $operatingSystem = self::resolveOperatingSystem($operatingSystem);
        $libc = self::resolveLibc($operatingSystem);
        $suffix = $libc === 'musl' ? '-musl' : '';

        return self::BASE_DOWNLOAD_URL.self::getVersion().'/'."mjml-{$operatingSystem}-{$architecture}{$suffix}";
    }

    public static function getVersion(): string
    {
        $versionFile = __DIR__.'/../VERSION';

        if (! file_exists($versionFile)) {
            throw new RuntimeException('VERSION file not found.');
        }

        return trim(file_get_contents($versionFile));
    }
}
