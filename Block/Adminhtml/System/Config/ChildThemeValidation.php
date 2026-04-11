<?php
/**
 * Copyright (c) Panth Infotech. All rights reserved.
 * Child Theme Validation - Dynamic checks block
 */
declare(strict_types=1);

namespace Panth\Core\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Panth\Core\Model\ChildTheme\Validator;

class ChildThemeValidation extends Field
{
    protected $_template = 'Panth_Core::system/config/child_theme_validation.phtml';

    private Validator $themeValidator;

    public function __construct(
        Context $context,
        Validator $validator,
        array $data = []
    ) {
        $this->themeValidator = $validator;
        parent::__construct($context, $data);
    }

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
        return parent::render($element);
    }

    public function getValidationResults(): array
    {
        return $this->themeValidator->runAllChecks();
    }

    public function getValidateUrl(): string
    {
        return $this->getUrl('panthcore/childtheme/validate');
    }

    public function getRebuildUrl(): string
    {
        return $this->getUrl('panthcore/childtheme/rebuild');
    }
}
