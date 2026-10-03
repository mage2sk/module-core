<?php
declare(strict_types=1);

namespace Panth\Core\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ChildThemeGuide extends Field
{
    protected $_template = 'Panth_Core::system/config/child_theme_guide.phtml';

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    protected function _renderScopeLabel(\Magento\Framework\Data\Form\Element\AbstractElement $element): string
    {
        return '';
    }

    protected function _renderInheritCheckbox(\Magento\Framework\Data\Form\Element\AbstractElement $element): string
    {
        return '';
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return '<tr id="row_' . $this->_escaper->escapeHtmlAttr($element->getHtmlId()) . '">'
            . '<td colspan="4">' . $this->_toHtml() . '</td></tr>';
    }
}
