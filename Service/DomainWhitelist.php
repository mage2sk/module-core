<?php
/**
 * Panth Domain Whitelist Service (Disabled)
 * All domain checks return true
 *
 * @category  Panth
 * @package   Panth_Core
 */
declare(strict_types=1);

namespace Panth\Core\Service;

class DomainWhitelist
{
    /**
     * Check if current domain is whitelisted - always returns true
     *
     * @return bool
     */
    public function isCurrentDomainApproved()
    {
        return true;
    }

    /**
     * Check if domain is approved - always returns true
     *
     * @param string $domain
     * @return bool
     */
    public function isDomainApproved($domain)
    {
        return true;
    }

    /**
     * Add domain to whitelist (no-op)
     *
     * @param string $domain
     * @return bool
     */
    public function addDomain($domain)
    {
        return true;
    }

    /**
     * Remove domain from whitelist (no-op)
     *
     * @param string $domain
     * @return bool
     */
    public function removeDomain($domain)
    {
        return true;
    }
}
