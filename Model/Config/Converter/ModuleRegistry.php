<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Model\Config\Converter;

use Magento\Framework\Config\ConverterInterface;

class ModuleRegistry implements ConverterInterface
{
    /**
     * Convert config XML to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];

        $xpath = new \DOMXPath($source);
        $modules = $xpath->query('/config/modules/module');

        foreach ($modules as $module) {
            $name = $module->getAttribute('name');
            $output[$name] = [
                'name' => $name,
                'config_section' => $module->getAttribute('config_section'),
                'enabled' => $module->getAttribute('enabled') !== '0'
            ];
        }

        return $output;
    }
}
