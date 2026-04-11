<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 * Fixes "foreach() null" error in developer mode when Document::getCustomAttributes() returns null.
 * Wraps the searchResultToOutput method to catch the warning.
 */
declare(strict_types=1);

namespace Panth\Core\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class FixGridDataProvider
{
    /**
     * Wrap getData to suppress the foreach null warning in developer mode
     */
    public function aroundGetData(DataProvider $subject, callable $proceed): array
    {
        // Temporarily set error handler to suppress the specific foreach warning
        $previousHandler = set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$previousHandler) {
            // Suppress only the specific foreach null warning in DataProvider
            if ($errno === E_WARNING
                && strpos($errstr, 'foreach()') !== false
                && strpos($errfile, 'DataProvider.php') !== false
            ) {
                return true; // Suppress this warning
            }
            // Pass all other errors to the previous handler
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
