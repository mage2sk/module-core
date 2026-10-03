<?php
declare(strict_types=1);

namespace Panth\Core\Model\Config\SchemaLocator;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;

class ModuleRegistry implements SchemaLocatorInterface
{
    private $schema;

    public function __construct(ModuleDirReader $moduleReader)
    {
        $this->schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Panth_Core') . '/panth_modules.xsd';
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function getPerFileSchema()
    {
        return $this->schema;
    }
}
