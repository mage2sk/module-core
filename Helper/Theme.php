<?php
/**
 * Copyright (c) Panth Infotech. All rights reserved.
 *
 * Shared Theme Detection Helper for all Panth modules.
 * Detects whether Hyva or Luma theme is active.
 */
declare(strict_types=1);

namespace Panth\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\DesignInterface;

class Theme extends AbstractHelper
{
    public const THEME_HYVA = 'hyva';
    public const THEME_LUMA = 'luma';

    private ?string $cachedThemeType = null;

    public function __construct(
        Context $context,
        private readonly DesignInterface $design,
        private readonly ModuleManager $moduleManager
    ) {
        parent::__construct($context);
    }

    public function isHyva(): bool
    {
        return $this->getCurrentTheme() === self::THEME_HYVA;
    }

    public function isLuma(): bool
    {
        return !$this->isHyva();
    }

    public function getCurrentTheme(): string
    {
        if ($this->cachedThemeType !== null) {
            return $this->cachedThemeType;
        }

        try {
            // Primary check: if Hyva_Theme module is enabled, it's a Hyva-based theme
            // This works for child themes that inherit from Panth/Infotech → Hyva/default
            if ($this->moduleManager->isEnabled('Hyva_Theme')) {
                $themePath = $this->getThemePath();
                // Only override if theme is explicitly a Luma theme
                if ($this->isLumaThemePath($themePath)) {
                    $this->cachedThemeType = self::THEME_LUMA;
                    return $this->cachedThemeType;
                }
                $this->cachedThemeType = self::THEME_HYVA;
                return $this->cachedThemeType;
            }

            // Secondary: check theme path for Hyva indicators
            $themePath = $this->getThemePath();
            if (stripos($themePath, 'hyva') !== false) {
                $this->cachedThemeType = self::THEME_HYVA;
                return $this->cachedThemeType;
            }

            $this->cachedThemeType = self::THEME_LUMA;
            return $this->cachedThemeType;
        } catch (\Exception $e) {
            $this->cachedThemeType = self::THEME_LUMA;
            return $this->cachedThemeType;
        }
    }

    public function getTemplateForTheme(string $hyvaTemplate, string $lumaTemplate): string
    {
        return $this->isHyva() ? $hyvaTemplate : $lumaTemplate;
    }

    public function useAlpineJs(): bool
    {
        return $this->isHyva();
    }

    public function useKnockoutJs(): bool
    {
        return $this->isLuma();
    }

    public function resetCache(): void
    {
        $this->cachedThemeType = null;
    }

    private function getThemePath(): string
    {
        try {
            $theme = $this->design->getDesignTheme();
            return $theme ? ($theme->getThemePath() ?? '') : '';
        } catch (\Exception $e) {
            return '';
        }
    }

    private function isLumaThemePath(string $themePath): bool
    {
        $lumaIndicators = ['Magento/luma', 'Magento/blank', 'luma'];
        foreach ($lumaIndicators as $indicator) {
            if (stripos($themePath, $indicator) !== false) {
                return true;
            }
        }
        return false;
    }
}
