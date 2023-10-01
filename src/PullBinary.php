<?php

declare(strict_types=1);

namespace DefectiveCode\MJML;

use RuntimeException;

class PullBinary
{
    public const MJML_VERSION = '4.14.1';

    public const BASE_DOWNLOAD_URL = 'https://defectivecode-packages.nyc3.digitaloceanspaces.com/packages/mjml/';

    public static function pull(): void
    {
        $operatingSystem = php_uname('s');
        $architecture = php_uname('m');
        $binaryPath = __DIR__.'/../bin/mjml'.($operatingSystem === 'Windows' ? '.exe' : '');
        $downloadUrl = self::resolveDownloadUrl($operatingSystem, $architecture);

        echo "Downloading MJML binary for {$operatingSystem} - {$architecture}.\n";

        file_put_contents(
            $binaryPath,
            file_get_contents($downloadUrl)
        );

        echo "Granting run permissions to MJML binary.\n";
        chmod($binaryPath, 0755);

    }

    protected static function resolveDownloadUrl(string $operatingSystem, string $architecture): string
    {
        $architecture = $architecture === 'x86_64' ? 'x64' : $architecture;

        return self::BASE_DOWNLOAD_URL.self::MJML_VERSION.'/'.match ($operatingSystem) {
            'Darwin' => "mjml-darwin-{$architecture}",
            'Linux' => "mjml-linux-{$architecture}",
            'Windows' => "mjml-win-{$architecture}.exe",
            default => throw new RuntimeException('Unsupported operating system'),
        };
    }
}
