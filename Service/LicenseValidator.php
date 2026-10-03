<?php
declare(strict_types=1);

namespace Panth\Core\Service;

use Psr\Log\LoggerInterface;

class LicenseValidator
{
    private $logger;

    private $lastError = '';

    private $lastLicenseData = [];

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function validate($moduleName)
    {
        return true;
    }

    public function getDomain()
    {
        return 'localhost';
    }

    public function forceRevalidate($moduleName)
    {
    }

    public function checkIntegrity()
    {
        return true;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getLastLicenseData()
    {
        return $this->lastLicenseData;
    }
}
