<?php
/**
 * Secure License Validation Cache (Disabled)
 * License validation cache has been removed
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Service;

class ValidationCache
{
    /**
     * Check if validation cache exists and is valid - always returns true
     *
     * @param string $moduleName
     * @param string $licenseKey
     * @return bool
     */
    public function isValid(string $moduleName, string $licenseKey): bool
    {
        return true;
    }

    /**
     * Store validation result in cache (no-op)
     *
     * @param string $moduleName
     * @param string $licenseKey
     * @param bool $isValid
     * @param array $licenseData
     * @return void
     */
    public function store(string $moduleName, string $licenseKey, bool $isValid, array $licenseData = []): void
    {
        // No-op
    }

    /**
     * Get cached validation data
     *
     * @param string $moduleName
     * @param string $licenseKey
     * @return array|null
     */
    public function get(string $moduleName, string $licenseKey): ?array
    {
        return ['module' => $moduleName, 'validated' => true, 'license_data' => []];
    }

    /**
     * Clear validation cache for module (no-op)
     *
     * @param string $moduleName
     * @param string $licenseKey
     * @return void
     */
    public function clear(string $moduleName, string $licenseKey): void
    {
        // No-op
    }

    /**
     * Clear all validation caches (no-op)
     *
     * @return void
     */
    public function clearAll(): void
    {
        // No-op
    }
}
