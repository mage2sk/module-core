<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Model\Config\Reader;

use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;

class ModuleRegistry extends Filesystem
{
    /**
     * @param FileResolverInterface $fileResolver
     * @param \Panth\Core\Model\Config\Converter\ModuleRegistry $converter
     * @param \Panth\Core\Model\Config\SchemaLocator\ModuleRegistry $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        \Panth\Core\Model\Config\Converter\ModuleRegistry $converter,
        \Panth\Core\Model\Config\SchemaLocator\ModuleRegistry $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName = 'panth_modules.xml',
        $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
