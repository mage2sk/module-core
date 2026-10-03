<?php
declare(strict_types=1);

namespace Panth\Core\Model;

use Panth\Core\Model\Config\Reader\ModuleRegistry as ModuleRegistryReader;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

class ModuleRegistry
{
    const CACHE_ID = 'panth_module_registry';

    private $reader;

    private $cache;

    private $serializer;

    private $modules = null;

    public function __construct(
        ModuleRegistryReader $reader,
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->reader = $reader;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    public function getModules(): array
    {
        if ($this->modules === null) {
            $this->loadModules();
        }
        return $this->modules;
    }

    public function getConfigSection(string $moduleName): ?string
    {
        $modules = $this->getModules();
        return $modules[$moduleName]['config_section'] ?? null;
    }

    public function isRegistered(string $moduleName): bool
    {
        $modules = $this->getModules();
        return isset($modules[$moduleName]);
    }

    public function getAllConfigSections(): array
    {
        $sections = [];
        foreach ($this->getModules() as $module) {
            $sections[] = $module['config_section'];
        }
        return $sections;
    }

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
