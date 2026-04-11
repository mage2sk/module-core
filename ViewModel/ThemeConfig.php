<?php
declare(strict_types=1);

namespace Panth\Core\ViewModel;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

/**
 * Panth Theme Config — Shared ViewModel for all Panth modules.
 *
 * Reads each module's etc/theme-config.json and outputs CSS variables.
 * Then loads the active theme hierarchy's web/tailwind/theme-config.json
 * on top, so child themes can override colors/spacing from a single file.
 *
 * Modules register themselves via di.xml (registeredModules array).
 * Works on both Hyva and Luma themes.
 */
class ThemeConfig implements ArgumentInterface
{
    private ?array $mergedConfig = null;

    public function __construct(
        private readonly ModuleDirReader $moduleDirReader,
        private readonly Json $json,
        private readonly LoggerInterface $logger,
        private readonly DesignInterface $design,
        private readonly ComponentRegistrar $componentRegistrar,
        private readonly array $registeredModules = []
    ) {}

    /**
     * Generate CSS variables string for all registered modules
     */
    public function getCssVariables(): string
    {
        $config = $this->getMergedConfig();
        if (empty($config)) {
            return '';
        }

        $vars = [];
        $this->flattenToVars($config, '', $vars);

        if (empty($vars)) {
            return '';
        }

        $css = ":root {\n";
        foreach ($vars as $name => $value) {
            $css .= "  --{$name}: {$value};\n";
        }
        $css .= "}\n";

        return $css;
    }

    /**
     * Get a specific config value by dot-notation path
     */
    public function getValue(string $path, ?string $default = null): ?string
    {
        $config = $this->getMergedConfig();
        $keys = explode('.', $path);

        $current = $config;
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                return $default;
            }
            $current = $current[$key];
        }

        return is_string($current) ? $current : $default;
    }

    private function getMergedConfig(): array
    {
        if ($this->mergedConfig !== null) {
            return $this->mergedConfig;
        }

        $this->mergedConfig = [];

        // Step 1: Load all module configs (defaults)
        foreach ($this->registeredModules as $moduleName) {
            $moduleConfig = $this->loadModuleConfig((string) $moduleName);
            $this->mergedConfig = array_replace_recursive($this->mergedConfig, $moduleConfig);
        }

        // Step 2: Override with active theme's theme-config.json (highest priority)
        $themeConfig = $this->loadActiveThemeConfig();
        if (!empty($themeConfig)) {
            $this->mergedConfig = array_replace_recursive($this->mergedConfig, $themeConfig);
        }

        return $this->mergedConfig;
    }

    private function loadModuleConfig(string $moduleName): array
    {
        try {
            $moduleDir = $this->moduleDirReader->getModuleDir(Dir::MODULE_ETC_DIR, $moduleName);
            $filePath = $moduleDir . '/theme-config.json';

            if (!file_exists($filePath)) {
                return [];
            }

            $content = file_get_contents($filePath);
            return $this->json->unserialize($content) ?: [];
        } catch (\Exception $e) {
            $this->logger->warning("Panth ThemeConfig: Failed to load {$moduleName}", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Load theme-config.json from the active theme hierarchy.
     * Walks up the chain (grandparent → parent → child), child values win.
     */
    private function loadActiveThemeConfig(): array
    {
        try {
            $theme = $this->design->getDesignTheme();
            $themeConfigs = [];
            $currentTheme = $theme;

            while ($currentTheme) {
                $themeCode = $currentTheme->getFullPath();
                if (!$themeCode) {
                    break;
                }

                $themePath = $this->componentRegistrar->getPath(
                    ComponentRegistrar::THEME,
                    $themeCode
                );

                if ($themePath) {
                    $configFile = $themePath . '/web/tailwind/theme-config.json';
                    if (file_exists($configFile)) {
                        $content = file_get_contents($configFile);
                        $config = $this->json->unserialize($content);
                        if (is_array($config)) {
                            // Prepend so grandparent is first, child is last
                            array_unshift($themeConfigs, $config);
                        }
                    }
                }

                $currentTheme = $currentTheme->getParentTheme();
            }

            // Merge in order: grandparent → parent → child (child wins)
            $merged = [];
            foreach ($themeConfigs as $config) {
                $merged = array_replace_recursive($merged, $config);
            }

            return $merged;
        } catch (\Exception $e) {
            $this->logger->warning('ThemeConfig: Failed to load theme config', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    private function flattenToVars(array $data, string $prefix, array &$vars): void
    {
        foreach ($data as $key => $value) {
            $varName = $prefix ? $prefix . '-' . $key : $key;

            if (is_array($value)) {
                $this->flattenToVars($value, $varName, $vars);
            } else {
                $vars[$varName] = (string) $value;
            }
        }
    }
}
