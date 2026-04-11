<?php
/**
 * Copyright (c) Panth Infotech. All rights reserved.
 * Child Theme Setup Guide - Admin Configuration Block
 */
declare(strict_types=1);

namespace Panth\Core\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ChildThemeGuide extends Field
{
    protected $_template = 'Panth_Core::system/config/child_theme_guide.phtml';

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Remove scope label
     */
    protected function _renderScopeLabel(\Magento\Framework\Data\Form\Element\AbstractElement $element): string
    {
        return '';
    }

    /**
     * Remove inherited checkbox
     */
    protected function _renderInheritCheckbox(\Magento\Framework\Data\Form\Element\AbstractElement $element): string
    {
        return '';
    }

    /**
     * Render element as full row
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
}
