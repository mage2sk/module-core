<?php
declare(strict_types=1);

namespace Panth\Core\Model;

use Panth\Core\Api\ThemeBuildExecutorInterface;

class NoopThemeBuildExecutor implements ThemeBuildExecutorInterface
{
    public function exportAndBuild(bool $forceNpmBuild = false): array
    {
        return [
            'success' => false,
            'message' => 'The Panth_ThemeCustomizer module is not installed or is disabled. '
                . 'Install and enable it to rebuild child theme CSS from the admin.',
            'output'  => '',
        ];
    }
}
