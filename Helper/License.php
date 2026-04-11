<?php
/**
 * Panth Core License Helper (Disabled)
 * License validation removed - all checks return true
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Panth\Core\Service\LicenseValidator;

class License extends AbstractHelper
{
    /**
     * @var LicenseValidator
     */
    private $licenseValidator;

    /**
     * @param Context $context
     * @param LicenseValidator $licenseValidator
     */
    public function __construct(
        Context $context,
        LicenseValidator $licenseValidator
    ) {
        parent::__construct($context);
        $this->licenseValidator = $licenseValidator;
    }

    /**
     * Get license key from configuration
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getLicenseKey($storeId = null)
    {
        return 'valid';
    }

    /**
     * Validate license - always returns true
     *
     * @param bool $forceCheck
     * @return bool
     */
    public function validateLicense($forceCheck = false)
    {
        return true;
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getError()
    {
        return '';
    }

    /**
     * Get license data
     *
     * @return array|null
     */
    public function getLicenseData()
    {
        return [
            'productName' => 'Panth Suite',
            'licenseType' => 'Unlimited',
            'isValid' => true,
            'domain' => $this->licenseValidator->getDomain(),
        ];
    }

    /**
     * Check if license is active - always true
     *
     * @return bool
     */
    public function isLicenseActive()
    {
        return true;
    }

    /**
     * Deactivate license (no-op)
     *
     * @return bool
     */
    public function deactivateLicense()
    {
        return true;
    }
}
