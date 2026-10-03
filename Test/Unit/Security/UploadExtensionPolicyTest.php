<?php
declare(strict_types=1);

namespace Panth\Core\Test\Unit\Security;

use Magento\Framework\Exception\LocalizedException;
use Panth\Core\Security\UploadExtensionPolicy;
use PHPUnit\Framework\TestCase;

class UploadExtensionPolicyTest extends TestCase
{
    private UploadExtensionPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = new UploadExtensionPolicy();
    }

    public function testAllowsOrdinaryUploadTypes(): void
    {
        foreach (['banner.jpg', 'catalogue.PDF', 'logo.svg', 'archive.zip', 'sheet.xlsx'] as $filename) {
            $this->assertTrue($this->policy->isSafeExtension($filename), $filename);
        }
    }

    public function testDeniesExecutableTypesRegardlessOfCase(): void
    {
        foreach (['shell.php', 'shell.PHP', 'view.phtml', 'bundle.Phar', 'run.sh', '.htaccess', 'page.aspx'] as $filename) {
            $this->assertFalse($this->policy->isSafeExtension($filename), $filename);
        }
    }

    public function testDeniesFilesWithoutExtension(): void
    {
        $this->assertFalse($this->policy->isSafeExtension('README'));
        $this->assertFalse($this->policy->isSafeExtension(''));
    }

    public function testOnlyLastExtensionCounts(): void
    {
        $this->assertFalse($this->policy->isSafeExtension('image.jpg.php'));
        $this->assertTrue($this->policy->isSafeExtension('script.php.jpg'));
    }

    public function testDeniesExecutableTypesWithTrailingDotsOrWhitespace(): void
    {
        foreach (['shell.php ', 'shell.php. ', 'shell.PHP.. ', "view.phtml\t"] as $filename) {
            $this->assertFalse($this->policy->isSafeExtension($filename), $filename);
        }
    }

    public function testDeniesNullByteInName(): void
    {
        $this->assertFalse($this->policy->isSafeExtension("shell.php\0.jpg"));
    }

    public function testDeniesPhp8(): void
    {
        $this->assertFalse($this->policy->isSafeExtension('run.php8'));
    }

    public function testAssertSafeExtensionThrowsForDeniedType(): void
    {
        $this->expectException(LocalizedException::class);
        $this->policy->assertSafeExtension('backdoor.php5');
    }

    public function testHardDenyListIsExposed(): void
    {
        $list = $this->policy->getHardDenyExtensions();
        $this->assertContains('php', $list);
        $this->assertContains('phtml', $list);
        $this->assertSame(UploadExtensionPolicy::HARD_DENY_EXT, $list);
    }
}
