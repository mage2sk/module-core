<?php
declare(strict_types=1);

namespace Panth\Core\Model\Config\Source;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ColorPicker extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value') ?: '#000000';

        $escapedHtmlId = $this->escapeJsString($element->getHtmlId());
        $escapedValue = $this->escapeJsString($value);

        $html .= '
            <style>
                .color-picker-wrapper {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .color-picker-preview {
                    width: 36px;
                    height: 36px;
                    border: 2px solid #ddd;
                    border-radius: 4px;
                    cursor: pointer;
                    transition: all 0.2s;
                }
                .color-picker-preview:hover {
                    border-color: #888;
                    transform: scale(1.05);
                }
            </style>
            <script type="text/javascript">
            require(["jquery"], function($) {
                $(document).ready(function() {
                    var input = $("#' . $escapedHtmlId . '");
                    var wrapper = $("<div class=\"color-picker-wrapper\"></div>");
                    var preview = $("<div class=\"color-picker-preview\"></div>");
                    var picker = $("<input type=\"color\" style=\"opacity: 0; width: 0; height: 0;\">");

                    input.wrap(wrapper);
                    input.after(preview);
                    input.after(picker);

                    // Update preview color
                    function updatePreview(color) {
                        preview.css("background-color", color);
                    }

                    // Initialize preview
                    updatePreview(input.val() || "' . $escapedValue . '");

                    // Handle color picker change
                    picker.on("change", function() {
                        var color = $(this).val();
                        input.val(color).trigger("change");
                        updatePreview(color);
                    });

                    // Handle preview click
                    preview.on("click", function() {
                        picker.click();
                    });

                    // Handle manual input
                    input.on("blur", function() {
                        updatePreview($(this).val());
                    });
                });
            });
            </script>
        ';

        return $html;
    }

    private function escapeJsString(string $string): string
    {
        return addcslashes($string, "\\'\"\n\r\t/");
    }
}
