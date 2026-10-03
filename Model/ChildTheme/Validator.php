<?php
declare(strict_types=1);

namespace Panth\Core\Model\ChildTheme;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
class Validator
{
    private ModuleManager $moduleManager;
    private Filesystem $filesystem;
    private ThemeCollectionFactory $themeCollectionFactory;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ModuleManager $moduleManager,
        Filesystem $filesystem,
        ThemeCollectionFactory $themeCollectionFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->moduleManager = $moduleManager;
        $this->filesystem = $filesystem;
        $this->themeCollectionFactory = $themeCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function runAllChecks(): array
    {
        return [
            'theme_info' => $this->getThemeInfo(),
            'checks' => [
                $this->checkHyvaModule(),
                $this->checkThemeParentChain(),
                $this->checkTailwindSource(),
                $this->checkCssFileSize(),
                $this->checkCssMergeDisabled(),
                $this->checkViewXml(),
            ]
        ];
    }

    private function getFrontendTheme()
    {
        try {
            $themeId = $this->scopeConfig->getValue(
                'design/theme/theme_id',
                ScopeInterface::SCOPE_STORE
            );

            if (!$themeId) {
                return null;
            }

            $collection = $this->themeCollectionFactory->create();
            $collection->addFieldToFilter('theme_id', $themeId);
            $theme = $collection->getFirstItem();

            return ($theme && $theme->getId()) ? $theme : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getThemeInfo(): array
    {
        try {
            $theme = $this->getFrontendTheme();
            $themePath = $theme ? ($theme->getThemePath() ?? 'Unknown') : 'Unknown';

            $parentChain = [];
            $current = $theme;
            while ($current) {
                $parentChain[] = $current->getThemePath() ?: 'Unknown';
                $current = $current->getParentTheme();
            }

            $isHyva = $this->moduleManager->isEnabled('Hyva_Theme');

            return [
                'active_theme' => $themePath,
                'parent_chain' => implode(' -> ', $parentChain),
                'hyva_detected' => $isHyva,
                'is_child_theme' => $themePath !== 'Panth/Infotech'
                    && in_array('Panth/Infotech', array_slice($parentChain, 1), true),
            ];
        } catch (\Exception $e) {
            return [
                'active_theme' => 'Error: ' . $e->getMessage(),
                'parent_chain' => 'Unknown',
                'hyva_detected' => false,
                'is_child_theme' => false,
            ];
        }
    }

    private function checkHyvaModule(): array
    {
        $enabled = $this->moduleManager->isEnabled('Hyva_Theme');
        return [
            'label' => 'Hyva_Theme Module',
            'status' => $enabled ? 'pass' : 'fail',
            'message' => $enabled
                ? 'Hyva_Theme module is enabled'
                : 'Hyva_Theme module is NOT enabled. Panth modules require Hyva theme.',
        ];
    }

    private function checkThemeParentChain(): array
    {
        try {
            $theme = $this->getFrontendTheme();
            if (!$theme) {
                return [
                    'label' => 'Theme Parent Chain',
                    'status' => 'fail',
                    'message' => 'Cannot detect active frontend theme. Check design/theme/theme_id in config.',
                ];
            }

            $chain = [];
            $current = $theme;
            while ($current) {
                $chain[] = $current->getThemePath();
                $current = $current->getParentTheme();
            }

            $hasHyvaRoot = false;
            foreach ($chain as $path) {
                if ($path && stripos($path, 'Hyva/') === 0) {
                    $hasHyvaRoot = true;
                    break;
                }
            }

            $hasPanth = in_array('Panth/Infotech', $chain);

            if ($hasHyvaRoot && $hasPanth) {
                return [
                    'label' => 'Theme Parent Chain',
                    'status' => 'pass',
                    'message' => 'Parent chain is correct: ' . implode(' -> ', $chain),
                ];
            }

            $themePath = $theme->getThemePath();
            if ($themePath === 'Panth/Infotech' && !$hasHyvaRoot) {
                return [
                    'label' => 'Theme Parent Chain',
                    'status' => 'fail',
                    'message' => 'Panth/Infotech is missing Hyva/default as parent. Fix parent_id in theme table. Chain: ' . implode(' -> ', $chain),
                ];
            }

            if ($themePath === 'Panth/Infotech') {
                return [
                    'label' => 'Theme Parent Chain',
                    'status' => 'pass',
                    'message' => 'Active theme is Panth/Infotech (no child theme). Chain: ' . implode(' -> ', $chain),
                ];
            }

            $issues = [];
            if (!$hasPanth) {
                $issues[] = 'Panth/Infotech is missing from parent chain';
            }
            if (!$hasHyvaRoot) {
                $issues[] = 'Hyva/default is missing from parent chain (check theme table parent_id)';
            }

            return [
                'label' => 'Theme Parent Chain',
                'status' => 'fail',
                'message' => 'Parent chain issue: ' . implode('; ', $issues) . '. Current chain: ' . implode(' -> ', $chain),
            ];
        } catch (\Exception $e) {
            return [
                'label' => 'Theme Parent Chain',
                'status' => 'fail',
                'message' => 'Error checking parent chain: ' . $e->getMessage(),
            ];
        }
    }

    private function checkTailwindSource(): array
    {
        try {
            $theme = $this->getFrontendTheme();
            if (!$theme || !$theme->getThemePath()) {
                return [
                    'label' => 'Tailwind Source Config',
                    'status' => 'info',
                    'message' => 'Cannot detect active frontend theme path.',
                ];
            }

            $themePath = $theme->getThemePath();

            if ($themePath === 'Panth/Infotech') {
                return [
                    'label' => 'Tailwind Source Config',
                    'status' => 'info',
                    'message' => 'Active theme is Panth/Infotech (not a child theme). Check not applicable.',
                ];
            }

            $appDir = $this->filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath();
            $tailwindSource = $appDir . 'design/frontend/' . $themePath . '/web/tailwind/tailwind-source.css';

            if (!file_exists($tailwindSource)) {
                return [
                    'label' => 'Tailwind Source Config',
                    'status' => 'fail',
                    'message' => 'tailwind-source.css not found at: frontend/' . $themePath . '/web/tailwind/',
                ];
            }

            $content = file_get_contents($tailwindSource);
            $hasParentSource = stripos($content, 'Panth/Infotech') !== false;

            if ($hasParentSource) {
                return [
                    'label' => 'Tailwind Source Config',
                    'status' => 'pass',
                    'message' => 'tailwind-source.css includes @source lines for parent theme (Panth/Infotech).',
                ];
            }

            return [
                'label' => 'Tailwind Source Config',
                'status' => 'fail',
                'message' => 'tailwind-source.css is MISSING @source lines for parent theme. Add: @source "../../../../Panth/Infotech/**/*.phtml"; and @source "../../../../Panth/Infotech/**/*.xml";',
            ];
        } catch (\Exception $e) {
            return [
                'label' => 'Tailwind Source Config',
                'status' => 'fail',
                'message' => 'Error checking tailwind-source.css: ' . $e->getMessage(),
            ];
        }
    }

    private function checkCssFileSize(): array
    {
        try {
            $theme = $this->getFrontendTheme();
            if (!$theme || !$theme->getThemePath() || $theme->getThemePath() === 'Panth/Infotech') {
                return [
                    'label' => 'CSS File Size',
                    'status' => 'info',
                    'message' => 'Check applicable only for child themes.',
                ];
            }

            $appDir = $this->filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath();
            $childCss = $appDir . 'design/frontend/' . $theme->getThemePath() . '/web/css/styles.css';
            $parentCss = $appDir . 'design/frontend/Panth/Infotech/web/css/styles.css';

            if (!file_exists($childCss)) {
                return [
                    'label' => 'CSS File Size',
                    'status' => 'fail',
                    'message' => 'Child theme styles.css not found. Run npm run build in the child theme\'s tailwind directory.',
                ];
            }

            if (!file_exists($parentCss)) {
                return [
                    'label' => 'CSS File Size',
                    'status' => 'warning',
                    'message' => 'Parent theme styles.css not found for comparison. Child CSS size: ' . $this->formatSize(filesize($childCss)),
                ];
            }

            $childSize = filesize($childCss);
            $parentSize = filesize($parentCss);

            $ratio = $parentSize > 0 ? ($childSize / $parentSize) * 100 : 0;

            if ($ratio >= 80) {
                return [
                    'label' => 'CSS File Size',
                    'status' => 'pass',
                    'message' => sprintf(
                        'Child CSS (%s) is comparable to parent (%s) - %.0f%% ratio.',
                        $this->formatSize($childSize),
                        $this->formatSize($parentSize),
                        $ratio
                    ),
                ];
            }

            return [
                'label' => 'CSS File Size',
                'status' => 'fail',
                'message' => sprintf(
                    'Child CSS (%s) is significantly smaller than parent (%s) - %.0f%% ratio. This usually means @source lines for parent theme are missing in tailwind-source.css.',
                    $this->formatSize($childSize),
                    $this->formatSize($parentSize),
                    $ratio
                ),
            ];
        } catch (\Exception $e) {
            return [
                'label' => 'CSS File Size',
                'status' => 'fail',
                'message' => 'Error checking CSS sizes: ' . $e->getMessage(),
            ];
        }
    }

    private function checkCssMergeDisabled(): array
    {
        $mergeCss = $this->scopeConfig->getValue('dev/css/merge_css_files');
        $minifyCss = $this->scopeConfig->getValue('dev/css/minify_files');

        if ($mergeCss && $mergeCss !== '0') {
            return [
                'label' => 'CSS Merge/Minify',
                'status' => 'fail',
                'message' => 'CSS merge is ENABLED. Disable it for Hyva themes - Hyva produces a single optimized CSS via Tailwind. Magento\'s CSS merge can interfere.',
            ];
        }

        $msg = 'CSS merge is disabled.';
        if ($minifyCss && $minifyCss !== '0') {
            $msg .= ' Note: CSS minification is enabled (generally OK but can be disabled if issues arise).';
        }

        return [
            'label' => 'CSS Merge/Minify',
            'status' => 'pass',
            'message' => $msg,
        ];
    }

    private function checkViewXml(): array
    {
        try {
            $theme = $this->getFrontendTheme();
            if (!$theme || !$theme->getThemePath() || $theme->getThemePath() === 'Panth/Infotech') {
                return [
                    'label' => 'view.xml Configuration',
                    'status' => 'info',
                    'message' => 'Check applicable only for child themes.',
                ];
            }

            $appDir = $this->filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath();
            $viewXml = $appDir . 'design/frontend/' . $theme->getThemePath() . '/etc/view.xml';

            if (file_exists($viewXml)) {
                return [
                    'label' => 'view.xml Configuration',
                    'status' => 'pass',
                    'message' => 'Child theme has etc/view.xml.',
                ];
            }

            return [
                'label' => 'view.xml Configuration',
                'status' => 'warning',
                'message' => 'Child theme is missing etc/view.xml. Copy from Panth/Infotech/etc/view.xml for image dimensions and RequireJS config.',
            ];
        } catch (\Exception $e) {
            return [
                'label' => 'view.xml Configuration',
                'status' => 'fail',
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        return round($bytes / 1024, 1) . ' KB';
    }
}
