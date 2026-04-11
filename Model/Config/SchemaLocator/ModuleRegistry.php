<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Model\Config\SchemaLocator;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;

class ModuleRegistry implements SchemaLocatorInterface
{
    /**
     * @var string
     */
    private $schema;

    /**
     * @param ModuleDirReader $moduleReader
     */
    public function __construct(ModuleDirReader $moduleReader)
    {
        $this->schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Panth_Core') . '/panth_modules.xsd';
    }

    /**
     * Get path to merged config schema
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string
     */
    public function getPerFileSchema()
    {
        return $this->schema;
    }
}
