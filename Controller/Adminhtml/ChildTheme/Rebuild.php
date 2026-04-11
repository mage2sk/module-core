<?php
/**
 * Copyright (c) Panth Infotech. All rights reserved.
 * Child Theme Rebuild CSS AJAX Controller
 */
declare(strict_types=1);

namespace Panth\Core\Controller\Adminhtml\ChildTheme;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Panth\Core\Api\ThemeBuildExecutorInterface;

class Rebuild extends Action
{
    public const ADMIN_RESOURCE = 'Panth_Core::core_config';

    private JsonFactory $jsonFactory;
    private ThemeBuildExecutorInterface $buildExecutor;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ThemeBuildExecutorInterface $buildExecutor
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->buildExecutor = $buildExecutor;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            $buildResult = $this->buildExecutor->exportAndBuild(true);
            return $result->setData([
                'success' => $buildResult['success'],
                'message' => $buildResult['message'],
                'output' => $buildResult['output'] ?? ''
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => 'Build error: ' . $e->getMessage()
            ]);
        }
    }
}
