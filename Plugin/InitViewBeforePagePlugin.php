<?php
declare(strict_types=1);

namespace Panth\Core\Plugin;

use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class InitViewBeforePagePlugin
{
    private bool $initialising = false;

    public function __construct(
        private readonly ViewInterface $view
    ) {
    }

    public function beforeCreate(object $subject, ...$args): ?array
    {
        if ($subject instanceof PageFactory && empty($args[0])) {
            $this->initView();
        } elseif ($subject instanceof ResultFactory && ($args[0] ?? null) === ResultFactory::TYPE_PAGE) {
            $this->initView();
        }

        return null;
    }

    private function initView(): void
    {
        if ($this->initialising) {
            return;
        }
        $this->initialising = true;
        try {
            $this->view->getPage();
        } finally {
            $this->initialising = false;
        }
    }
}
