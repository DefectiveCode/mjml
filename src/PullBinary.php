<?php

declare(strict_types=1);

namespace DefectiveCode\MJML;

use RuntimeException;

class PullBinary
{
    public const MJML_VERSION = '4.14.1';

    public const BASE_DOWNLOAD_URL = 'https://downloads.defectivecode.com/packages/mjml/';

    public const ALLOWED_BINARIES = [
        'darwin-arm64',
        'darwin-x64',
        'linux-arm64',
        'linux-x64',
        'win-arm64',
        'win-x64',
    ];

    public static function resolveBinaryPath(string $operatingSystem, string $architecture): string
    {
        return __DIR__."/../bin/mjml-{$operatingSystem}-{$architecture}".self::resolveExtension($operatingSystem);
    }

    public static function __callStatic(string $name, array $arguments): void
    {
        if ($name === 'all') {
            foreach (self::ALLOWED_BINARIES as $binary) {
                self::pull(...self::extractOperatingSystemAndArchitecture($binary));
            }

            return;
        }

        if (! in_array($name, self::ALLOWED_BINARIES)) {
            throw new RuntimeException('Unsupported binary');
        }

        self::pull(...self::extractOperatingSystemAndArchitecture($name));
    }

    protected static function extractOperatingSystemAndArchitecture(string $binary): array
    {
        return explode('-', $binary);
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

    protected static function pull(string $operatingSystem, string $architecture): void
    {
        $binaryPath = self::resolveBinaryPath($operatingSystem, $architecture);
        $downloadUrl = self::resolveDownloadUrl($operatingSystem, $architecture);

        if (self::hasLatestBinary($binaryPath, $downloadUrl)) {
            echo "Latest MJML binary already exists for {$operatingSystem} - {$architecture}.\n";

            return;
        }

        echo "Downloading MJML binary for {$operatingSystem} - {$architecture}.\n";

        file_put_contents(
            $binaryPath,
            file_get_contents($downloadUrl)
        );

        echo "Granting run permissions to {$binaryPath} binary.\n";
        chmod($binaryPath, 0755);

    }

    protected static function resolveExtension(string $operatingSystem): string
    {
        return match ($operatingSystem) {
            'win' => '.exe',
            default => '',
        };
    }

    protected static function resolveDownloadUrl(string $operatingSystem, string $architecture): string
    {
        return self::BASE_DOWNLOAD_URL.self::MJML_VERSION.'/'."mjml-{$operatingSystem}-{$architecture}".self::resolveExtension($operatingSystem);
    }
}
