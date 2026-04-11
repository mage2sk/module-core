<?php
/**
 * Panth Core License Deactivate Controller (Disabled)
 * Always returns success
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Controller\Adminhtml\License;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Deactivate extends Action
{
    /**
     * Authorization resource
     */
    const ADMIN_RESOURCE = 'Panth_Core::core_config';

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute deactivation - always returns success
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        return $result->setData([
            'success' => true,
            'message' => 'License cache cleared successfully.'
        ]);
    }
}
