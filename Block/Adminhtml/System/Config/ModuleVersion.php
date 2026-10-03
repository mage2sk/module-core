<?php
declare(strict_types=1);

namespace Panth\Core\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Serialize\Serializer\Json;

class ModuleVersion extends Field
{
    private const MODULE_NAME = 'Panth_Core';

    private ComponentRegistrarInterface $componentRegistrar;
    private ReadFactory $readFactory;
    private Json $json;

    public function __construct(
        Context $context,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        Json $json,
        array $data = []
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    public function getModuleVersion(): string
    {
        try {
            $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::MODULE_NAME);
            if (!$path) {
                return '';
            }
            $directory = $this->readFactory->create($path);
            if (!$directory->isFile('composer.json')) {
                return '';
            }
            $data = $this->json->unserialize($directory->readFile('composer.json'));
            return is_array($data) && isset($data['version']) ? (string) $data['version'] : '';
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $version = $this->getModuleVersion();
        $text = $version !== '' ? 'Panth Core v' . $version : 'Panth Core';
        return '<span>' . $this->escapeHtml($text) . '</span>';
    }

    protected function _renderScopeLabel(AbstractElement $element): string
    {
        return '';
    }

    protected function _renderInheritCheckbox(AbstractElement $element): string
    {
        return '';
    }

    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
}
