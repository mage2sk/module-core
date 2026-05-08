<?php
/**
 * Daily heartbeat cron. Schedule is defined in etc/crontab.xml.
 * Local Flag dedup ensures only the first cron of the day actually
 * sends; subsequent runs (or sibling modules' crons firing the same
 * day) silently no-op.
 */
declare(strict_types=1);

namespace Panth\Core\Cron;

use Panth\Core\Service\InstallReporter;

class SendHeartbeat
{
    public function __construct(
        private readonly InstallReporter $reporter
    ) {
    }

    public function execute(): void
    {
        $this->reporter->reportHeartbeat();
    }
}
