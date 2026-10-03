<?php
declare(strict_types=1);

namespace Panth\Core\Service;

class ValidationCache
{
    public function isValid(string $moduleName, string $licenseKey): bool
    {
        return true;
    }

    public function store(string $moduleName, string $licenseKey, bool $isValid, array $licenseData = []): void
    {
    }

    public function get(string $moduleName, string $licenseKey): ?array
    {
        return ['module' => $moduleName, 'validated' => true, 'license_data' => []];
    }

    public function clear(string $moduleName, string $licenseKey): void
    {
    }

    public function clearAll(): void
    {
    }
}
