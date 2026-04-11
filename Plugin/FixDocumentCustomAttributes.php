<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 * Fixes "foreach() null" error in DataProvider when Document::getCustomAttributes() returns null
 * This happens in developer mode with SearchResult-based grid collections
 */
declare(strict_types=1);

namespace Panth\Core\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\Document;

class FixDocumentCustomAttributes
{
    /**
     * Ensure getCustomAttributes never returns null
     */
    public function afterGetCustomAttributes(Document $subject, $result): array
    {
        return $result ?? [];
    }
}
