<?php
/**
 * Copyright (c) Panth Infotech. All rights reserved.
 * Child Theme Validation AJAX Controller
 */
declare(strict_types=1);

namespace Panth\Core\Controller\Adminhtml\ChildTheme;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Panth\Core\Model\ChildTheme\Validator;

class Validate extends Action
{
    public const ADMIN_RESOURCE = 'Panth_Core::core_config';

    private JsonFactory $jsonFactory;
    private Validator $validator;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Validator $validator
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->validator = $validator;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            $validationResults = $this->validator->runAllChecks();
            return $result->setData([
                'success' => true,
                'data' => $validationResults
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ]);
        }
    }
}
