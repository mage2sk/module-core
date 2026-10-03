<?php
declare(strict_types=1);

namespace Panth\Core\Security;

use Magento\Framework\Exception\LocalizedException;

class UploadExtensionPolicy
{
    public const HARD_DENY_EXT = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps', 'pht', 'phpt', 'inc',
        'htaccess', 'htpasswd', 'shtml', 'cgi', 'pl', 'py', 'sh', 'asp', 'aspx', 'jsp',
    ];

    public function assertSafeExtension(string $filename): void
    {
        if (!$this->isSafeExtension($filename)) {
            throw new LocalizedException(__('This file type is not allowed.'));
        }
    }

    public function isSafeExtension(string $filename): bool
    {
        if (strpos($filename, "\0") !== false) {
            return false;
        }
        $ext = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext === '') {
            return false;
        }
        $trimmedExt = strtolower((string) pathinfo(rtrim($filename, " .\t\n\r\x0B"), PATHINFO_EXTENSION));
        return !in_array($ext, self::HARD_DENY_EXT, true)
            && !in_array($trimmedExt, self::HARD_DENY_EXT, true);
    }

    public function getHardDenyExtensions(): array
    {
        return self::HARD_DENY_EXT;
    }
}
