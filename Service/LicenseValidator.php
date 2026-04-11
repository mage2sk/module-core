<?php
/**
 * Panth License Validator (Disabled)
 * License validation has been removed - all checks return true
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Service;

use Psr\Log\LoggerInterface;

class LicenseValidator
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $lastError = '';

    /**
     * @var array
     */
    private $lastLicenseData = [];

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Validate module license - always returns true (license validation removed)
     *
     * @param string $moduleName
     * @return bool
     */
    public function validate($moduleName)
    {
        return true;
    }

    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return 'localhost';
    }

    /**
     * Force revalidation (no-op)
     *
     * @param string $moduleName
     * @return void
     */
    public function forceRevalidate($moduleName)
    {
        // No-op
    }

    /**
     * Check integrity - always returns true
     *
     * @return bool
     */
    public function checkIntegrity()
    {
        return true;
    }

    /**
     * Get last validation error message
     *
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Get last license data
     *
     * @return array
     */
    public function getLastLicenseData()
    {
        return $this->lastLicenseData;
    }
}
