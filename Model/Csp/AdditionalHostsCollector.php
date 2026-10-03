<?php
declare(strict_types=1);

namespace Panth\Core\Model\Csp;

use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AdditionalHostsCollector implements PolicyCollectorInterface
{
    public const XML_PATH_ADDITIONAL_IMAGE_HOSTS = 'panth_core/csp/additional_image_hosts';

    public const DIRECTIVES = ['img-src', 'connect-src'];

    private const HOST_PATTERN = '/^(?:[a-z][a-z0-9+.\-]*:\/\/)?(?:\*\.)?[a-z0-9\-]+(?:\.[a-z0-9\-]+)*(?::(?:\*|\d{1,5}))?(?:\/[^\s;,\'"]*)?$/i';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function collect(array $defaultPolicies = []): array
    {
        $hosts = $this->getConfiguredHosts();
        if ($hosts === []) {
            return $defaultPolicies;
        }

        foreach (self::DIRECTIVES as $directive) {
            $defaultPolicies[] = new FetchPolicy($directive, false, $hosts);
        }

        return $defaultPolicies;
    }

    public function getConfiguredHosts(): array
    {
        $raw = (string) $this->scopeConfig->getValue(
            self::XML_PATH_ADDITIONAL_IMAGE_HOSTS,
            ScopeInterface::SCOPE_STORE
        );

        return self::parseHosts($raw);
    }

    public static function parseHosts(string $raw): array
    {
        $hosts = [];
        foreach (preg_split('/[\s,]+/', $raw) ?: [] as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '' || !preg_match(self::HOST_PATTERN, $candidate)) {
                continue;
            }
            $hosts[$candidate] = true;
        }

        return array_keys($hosts);
    }
}
