<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 *
 * Centralized Module Registry
 * Provides access to all registered Panth modules
 */
declare(strict_types=1);

namespace Panth\Core\Model;

use Panth\Core\Model\Config\Reader\ModuleRegistry as ModuleRegistryReader;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

class ModuleRegistry
{
    const CACHE_ID = 'panth_module_registry';

    /**
     * @var ModuleRegistryReader
     */
    private $reader;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array|null
     */
    private $modules = null;

    /**
     * @param ModuleRegistryReader $reader
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ModuleRegistryReader $reader,
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->reader = $reader;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * Get all registered modules
     *
     * @return array
     */
    public function getModules(): array
    {
        if ($this->modules === null) {
            $this->loadModules();
        }
        return $this->modules;
    }

    /**
     * Get config section for a module
     *
     * @param string $moduleName
     * @return string|null
     */
    public function getConfigSection(string $moduleName): ?string
    {
        $modules = $this->getModules();
        return $modules[$moduleName]['config_section'] ?? null;
    }

    /**
     * Check if module is registered
     *
     * @param string $moduleName
     * @return bool
     */
    public function isRegistered(string $moduleName): bool
    {
        $modules = $this->getModules();
        return isset($modules[$moduleName]);
    }

    /**
     * Get all config sections
     *
     * @return array
     */
    public function getAllConfigSections(): array
    {
        $sections = [];
        foreach ($this->getModules() as $module) {
            $sections[] = $module['config_section'];
        }
        return $sections;
    }

    /**
     * Load modules from cache or XML
     *
     * @return void
     */
    private function loadModules(): void
    {
        $cached = $this->cache->load(self::CACHE_ID);

        if ($cached) {
            $this->modules = $this->serializer->unserialize($cached);
        } else {
            $this->modules = $this->reader->read();
            $this->cache->save(
                $this->serializer->serialize($this->modules),
                self::CACHE_ID,
                ['config']
            );
        }
    }
}
