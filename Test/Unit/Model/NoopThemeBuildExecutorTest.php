<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Model;

use Panth\Core\Api\ThemeBuildExecutorInterface;
use Panth\Core\Model\NoopThemeBuildExecutor;
use PHPUnit\Framework\TestCase;

class NoopThemeBuildExecutorTest extends TestCase
{
    public function testImplementsTheOptionalContract(): void
    {
        $this->assertInstanceOf(ThemeBuildExecutorInterface::class, new NoopThemeBuildExecutor());
    }

    public function testReturnsFailurePayloadWithoutRunningAnything(): void
    {
        $executor = new NoopThemeBuildExecutor();

        foreach ([false, true] as $force) {
            $result = $executor->exportAndBuild($force);

            $this->assertFalse($result['success']);
            $this->assertStringContainsString('Panth_ThemeCustomizer', $result['message']);
            $this->assertSame('', $result['output']);
        }
    }
}
