<?php
declare(strict_types=1);

namespace Panth\Core\Service;

class DomainWhitelist
{
    public function isCurrentDomainApproved()
    {
        return true;
    }

    public function isDomainApproved($domain)
    {
        return true;
    }

    public function addDomain($domain)
    {
        return true;
    }

    public function removeDomain($domain)
    {
        return true;
    }
}
