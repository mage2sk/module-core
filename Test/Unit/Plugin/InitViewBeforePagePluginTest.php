<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Plugin;

use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Panth\Core\Plugin\InitViewBeforePagePlugin;
use PHPUnit\Framework\TestCase;

class InitViewBeforePagePluginTest extends TestCase
{
    public function testInitialisesViewBeforeControllerPage(): void
    {
        $view = $this->createMock(ViewInterface::class);
        $view->expects($this->once())->method('getPage');
        $plugin = new InitViewBeforePagePlugin($view);

        $this->assertNull($plugin->beforeCreate($this->createStub(PageFactory::class)));
    }

    public function testSkipsPagesCreatedByTheViewItself(): void
    {
        $view = $this->createMock(ViewInterface::class);
        $view->expects($this->never())->method('getPage');
        $plugin = new InitViewBeforePagePlugin($view);

        $this->assertNull($plugin->beforeCreate($this->createStub(PageFactory::class), true));
    }

    public function testInitialisesViewOnlyForPageResults(): void
    {
        $view = $this->createMock(ViewInterface::class);
        $view->expects($this->once())->method('getPage');
        $plugin = new InitViewBeforePagePlugin($view);
        $factory = $this->createStub(ResultFactory::class);

        $plugin->beforeCreate($factory, ResultFactory::TYPE_JSON);
        $plugin->beforeCreate($factory, ResultFactory::TYPE_RAW);
        $plugin->beforeCreate($factory, ResultFactory::TYPE_PAGE);
    }
}
