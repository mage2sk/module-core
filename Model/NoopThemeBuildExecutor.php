<?php
declare(strict_types=1);

namespace Panth\Core\Model;

use Panth\Core\Api\ThemeBuildExecutorInterface;

/**
 * Default implementation of ThemeBuildExecutorInterface used when
 * Panth_ThemeCustomizer is NOT installed.
 *
 * Returns a friendly payload telling the admin the feature requires
 * the theme customizer module — never throws and never tries to do
 * any real work. Once Panth_ThemeCustomizer is enabled it overrides
 * the DI preference with its real BuildExecutor and this class is
 * never instantiated.
 */
class NoopThemeBuildExecutor implements ThemeBuildExecutorInterface
{
    /**
     * @inheritDoc
     */
    public function exportAndBuild(bool $forceNpmBuild = false): array
    {
        return [
            'success' => false,
            'message' => 'The Panth_ThemeCustomizer module is not installed. '
                . 'Install it from the Magento Marketplace to enable child theme rebuilds.',
            'output'  => '',
        ];
    }
}
