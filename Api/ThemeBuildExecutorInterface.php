<?php
declare(strict_types=1);

namespace Panth\Core\Api;

/**
 * Optional contract Panth_Core publishes for sibling Panth modules
 * (currently Panth_ThemeCustomizer) that perform a CSS / theme rebuild.
 *
 * Why this lives in Core: the admin "Rebuild Child Theme" button must be
 * usable from the Core admin config screen, but the actual build logic
 * is owned by Panth_ThemeCustomizer (which is optional). To avoid a
 * hard cross-module class import — which would prevent Panth_Core from
 * being installed standalone — Core ships ONLY this interface plus a
 * no-op default implementation that returns a friendly "module not
 * installed" payload. When Panth_ThemeCustomizer is also installed it
 * overrides the DI preference with its real BuildExecutor and the
 * button starts working.
 *
 * @api
 */
interface ThemeBuildExecutorInterface
{
    /**
     * Run an export + theme rebuild and return a JSON-friendly array.
     *
     * @param bool $forceNpmBuild Force a fresh npm build even when nothing
     *                            obviously changed since the last run.
     * @return array{
     *     success: bool,
     *     message: string,
     *     output?: string
     * }
     */
    public function exportAndBuild(bool $forceNpmBuild = false): array;
}
