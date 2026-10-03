<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Controller\Adminhtml\ChildTheme;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Panth\Core\Api\ThemeBuildExecutorInterface;
use Panth\Core\Controller\Adminhtml\ChildTheme\Rebuild;
use Panth\Core\Controller\Adminhtml\ChildTheme\Validate;
use Panth\Core\Model\ChildTheme\Validator;
use Panth\Core\Model\NoopThemeBuildExecutor;
use PHPUnit\Framework\TestCase;

class ChildThemeActionsTest extends TestCase
{
    private ?array $payload = null;

    private function jsonFactory(): JsonFactory
    {
        $json = $this->createStub(Json::class);
        $json->method('setData')->willReturnCallback(function ($data) use ($json) {
            $this->payload = $data;
            return $json;
        });
        $factory = $this->createStub(JsonFactory::class);
        $factory->method('create')->willReturn($json);
        return $factory;
    }

    public function testActionsArePostOnlyAndUseCoreConfigAcl(): void
    {
        $this->assertSame('Panth_Core::core_config', Validate::ADMIN_RESOURCE);
        $this->assertSame('Panth_Core::core_config', Rebuild::ADMIN_RESOURCE);
        $this->assertTrue(is_subclass_of(Validate::class, HttpPostActionInterface::class));
        $this->assertTrue(is_subclass_of(Rebuild::class, HttpPostActionInterface::class));
    }

    public function testValidateReturnsValidatorResults(): void
    {
        $results = ['theme_info' => ['active_theme' => 'Panth/Infotech'], 'checks' => []];
        $validator = $this->createStub(Validator::class);
        $validator->method('runAllChecks')->willReturn($results);

        (new Validate($this->createStub(Context::class), $this->jsonFactory(), $validator))->execute();

        $this->assertSame(['success' => true, 'data' => $results], $this->payload);
    }

    public function testValidateReportsErrors(): void
    {
        $validator = $this->createStub(Validator::class);
        $validator->method('runAllChecks')->willThrowException(new \RuntimeException('broken'));

        (new Validate($this->createStub(Context::class), $this->jsonFactory(), $validator))->execute();

        $this->assertSame(['success' => false, 'message' => 'Validation error: broken'], $this->payload);
    }

    public function testRebuildWithoutThemeCustomizerReturnsNoopMessage(): void
    {
        (new Rebuild($this->createStub(Context::class), $this->jsonFactory(), new NoopThemeBuildExecutor()))->execute();

        $this->assertFalse($this->payload['success']);
        $this->assertStringContainsString('Panth_ThemeCustomizer', $this->payload['message']);
        $this->assertStringContainsString('not installed or is disabled', $this->payload['message']);
        $this->assertSame('', $this->payload['output']);
    }

    public function testRebuildForcesBuildAndPassesOutput(): void
    {
        $executor = $this->createMock(ThemeBuildExecutorInterface::class);
        $executor->expects($this->once())->method('exportAndBuild')->with(true)
            ->willReturn(['success' => true, 'message' => 'Built']);

        (new Rebuild($this->createStub(Context::class), $this->jsonFactory(), $executor))->execute();

        $this->assertSame(['success' => true, 'message' => 'Built', 'output' => ''], $this->payload);
    }

    public function testRebuildReportsErrors(): void
    {
        $executor = $this->createStub(ThemeBuildExecutorInterface::class);
        $executor->method('exportAndBuild')->willThrowException(new \RuntimeException('npm missing'));

        (new Rebuild($this->createStub(Context::class), $this->jsonFactory(), $executor))->execute();

        $this->assertSame(['success' => false, 'message' => 'Build error: npm missing'], $this->payload);
    }
}
