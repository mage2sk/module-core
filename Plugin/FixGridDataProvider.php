<?php
declare(strict_types=1);

namespace Panth\Core\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class FixGridDataProvider
{
    public function aroundGetData(DataProvider $subject, callable $proceed): array
    {
        $previousHandler = set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$previousHandler) {
            if ($errno === E_WARNING
                && strpos($errstr, 'foreach()') !== false
                && strpos($errfile, 'DataProvider.php') !== false
            ) {
                return true;
            }

            if ($previousHandler) {
                return $previousHandler($errno, $errstr, $errfile, $errline);
            }
            return false;
        });

        try {
            $result = $proceed();
        } finally {
            restore_error_handler();
        }

        return $result;
    }
}
