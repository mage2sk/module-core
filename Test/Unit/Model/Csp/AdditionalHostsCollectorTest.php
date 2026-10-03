<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Model\Csp;

use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Panth\Core\Model\Csp\AdditionalHostsCollector;
use PHPUnit\Framework\TestCase;

class AdditionalHostsCollectorTest extends TestCase
{
    private function collectorWithValue($value): AdditionalHostsCollector
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')
            ->with(AdditionalHostsCollector::XML_PATH_ADDITIONAL_IMAGE_HOSTS)
            ->willReturn($value);

        return new AdditionalHostsCollector($scopeConfig);
    }

    private function hostsByDirective(array $policies): array
    {
        $result = [];
        foreach ($policies as $policy) {
            $this->assertInstanceOf(FetchPolicy::class, $policy);
            $result[$policy->getId()] = $policy->getHostSources();
        }

        return $result;
    }

    public function testEmptyValueAddsNothing(): void
    {
        $existing = [new FetchPolicy('script-src', false, ['cdn.jsdelivr.net'])];

        $this->assertSame($existing, $this->collectorWithValue('')->collect($existing));
        $this->assertSame($existing, $this->collectorWithValue(null)->collect($existing));
        $this->assertSame($existing, $this->collectorWithValue(' , ,  ')->collect($existing));
    }

    public function testSingleHostIsAddedToImgAndConnectOnly(): void
    {
        $policies = $this->collectorWithValue('example-cdn.com')->collect([]);

        $this->assertSame(
            ['img-src' => ['example-cdn.com'], 'connect-src' => ['example-cdn.com']],
            $this->hostsByDirective($policies)
        );
        foreach ($policies as $policy) {
            $this->assertFalse($policy->isNoneAllowed());
            $this->assertFalse($policy->isInlineAllowed());
            $this->assertFalse($policy->isEvalAllowed());
            $this->assertFalse($policy->isSelfAllowed());
        }
    }

    public function testExistingPoliciesArePreservedAndOursAppended(): void
    {
        $existing = new FetchPolicy('img-src', false, ['www.googletagmanager.com']);
        $policies = $this->collectorWithValue('example-cdn.com')->collect([$existing]);

        $this->assertCount(3, $policies);
        $this->assertSame($existing, $policies[0]);
        $this->assertSame('img-src', $policies[1]->getId());
        $this->assertSame('connect-src', $policies[2]->getId());
    }

    public function testCommaSeparatedHostsAreTrimmed(): void
    {
        $this->assertSame(
            ['a.com', 'b.com'],
            AdditionalHostsCollector::parseHosts('a.com ,  b.com')
        );
        $this->assertSame(
            ['a.com', 'b.com', 'c.com'],
            AdditionalHostsCollector::parseHosts("\ta.com,\n b.com ,c.com,")
        );
    }

    public function testDuplicatesAreCollapsed(): void
    {
        $this->assertSame(['a.com'], AdditionalHostsCollector::parseHosts('a.com, a.com ,a.com'));
    }

    public function testWildcardSchemePortAndPathForms(): void
    {
        $this->assertSame(
            ['*.images.example.com', 'https://cdn.example.com', 'cdn.example.com:8443', 'cdn.example.com/assets/'],
            AdditionalHostsCollector::parseHosts(
                '*.images.example.com, https://cdn.example.com, cdn.example.com:8443, cdn.example.com/assets/'
            )
        );
    }

    public function testEntriesThatCouldBreakTheHeaderAreDropped(): void
    {
        $this->assertSame(
            ['good.com'],
            AdditionalHostsCollector::parseHosts("good.com, evil.com;default-src, 'unsafe-inline', \"quoted.com\", bad_host.com")
        );
    }
}
