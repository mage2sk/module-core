<?php
declare(strict_types=1);

namespace Panth\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Panth\Core\Service\LicenseValidator;

class License extends AbstractHelper
{
    private $licenseValidator;

    public function __construct(
        Context $context,
        LicenseValidator $licenseValidator
    ) {
        parent::__construct($context);
        $this->licenseValidator = $licenseValidator;
    }

    public function getLicenseKey($storeId = null)
    {
        return 'valid';
    }

    public function validateLicense($forceCheck = false)
    {
        return true;
    }

    public function getError()
    {
        return '';
    }

    public function getLicenseData()
    {
        return [
            'productName' => 'Panth Suite',
            'licenseType' => 'Unlimited',
            'isValid' => true,
            'domain' => $this->licenseValidator->getDomain(),
        ];
    }

    public function isLicenseActive()
    {
        return true;
    }

    public function deactivateLicense()
    {
        return true;
    }
}
