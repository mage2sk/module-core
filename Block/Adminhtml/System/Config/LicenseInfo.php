<?php
declare(strict_types=1);

namespace Panth\Core\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class LicenseInfo extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        return '';
    }
}
