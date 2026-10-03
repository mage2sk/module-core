<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Panth\Core\Plugin\FixGridDataProvider;
use PHPUnit\Framework\TestCase;

class FixGridDataProviderTest extends TestCase
{
    private FixGridDataProvider $plugin;
    private DataProvider $subject;

    protected function setUp(): void
    {
        $this->plugin = new FixGridDataProvider();
        $this->subject = $this->createStub(DataProvider::class);
    }

    public function testReturnsProceedResultUnchanged(): void
    {
        $data = ['items' => [['id' => 1]], 'totalRecords' => 1];

        $this->assertSame($data, $this->plugin->aroundGetData($this->subject, fn () => $data));
    }

    public function testSuppressesOnlyTheForeachWarningFromDataProvider(): void
    {
        $handler = function ($errno, $errstr, $errfile, $errline) {
            return $this->plugin->aroundGetData($this->subject, function () use ($errno, $errstr, $errfile, $errline) {
                $current = set_error_handler(fn () => false);
                restore_error_handler();
                return ['handled' => $current($errno, $errstr, $errfile, $errline)];
            });
        };

        set_error_handler(fn () => false);
        try {
            $results = [
                $handler(E_WARNING, 'foreach() argument must be of type array|object', '/x/DataProvider.php', 10),
                $handler(E_WARNING, 'Undefined variable $x', '/x/DataProvider.php', 10),
                $handler(E_WARNING, 'foreach() argument must be of type array|object', '/x/Other.php', 10),
                $handler(E_NOTICE, 'foreach() argument must be of type array|object', '/x/DataProvider.php', 10),
            ];
        } finally {
            restore_error_handler();
        }

        $this->assertSame(
            [['handled' => true], ['handled' => false], ['handled' => false], ['handled' => false]],
            $results
        );
    }

    public function testOtherWarningsAreForwardedToPreviousHandler(): void
    {
        $seen = [];
        set_error_handler(function ($errno, $errstr) use (&$seen) {
            $seen[] = $errstr;
            return true;
        });
        try {
            $this->plugin->aroundGetData($this->subject, function () {
                trigger_error('custom warning', E_USER_WARNING);
                return [];
            });
        } finally {
            restore_error_handler();
        }

        $this->assertSame(['custom warning'], $seen);
    }

    public function testHandlerIsRestoredEvenWhenProceedThrows(): void
    {
        $marker = fn () => true;
        set_error_handler($marker);
        try {
            try {
                $this->plugin->aroundGetData($this->subject, function () {
                    throw new \RuntimeException('boom');
                });
                $this->fail('Exception expected');
            } catch (\RuntimeException $e) {
                $this->assertSame('boom', $e->getMessage());
            }
            $current = set_error_handler(fn () => false);
            restore_error_handler();
            $this->assertSame($marker, $current);
        } finally {
            restore_error_handler();
        }
    }
}
