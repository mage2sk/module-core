<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 */
declare(strict_types=1);

namespace Panth\Core\Console\Command;

use Magento\Framework\App\State as AppState;
use Panth\Core\Service\NotificationsFetcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * `bin/magento panth:core:notifications:fetch` — run the notifications
 * fetcher synchronously. Useful right after enabling the feature on a new
 * site (so the operator sees the latest announcement without waiting for
 * cron) and for debugging the feed URL / parsing.
 */
class FetchNotificationsCommand extends Command
{
    private const NAME = 'panth:core:notifications:fetch';

    public function __construct(
        private readonly NotificationsFetcher $fetcher,
        private readonly AppState $appState
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Fetch the Panth notifications feed and import new messages into the admin inbox.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Throwable) {
            // Area already set — fine.
        }

        $output->writeln('<info>Fetching Panth notifications...</info>');
        $result = $this->fetcher->fetch();

        $status = (string) ($result['status'] ?? 'unknown');
        if ($status === 'error') {
            $output->writeln(sprintf(
                '<error>Fetch failed: %s</error>',
                (string) ($result['error'] ?? 'unknown_error')
            ));
            return Command::FAILURE;
        }

        $fetched = (int) ($result['fetched'] ?? 0);
        $skipped = (int) ($result['skipped'] ?? 0);
        $output->writeln(sprintf(
            '<info>Done.</info> %d new message(s) imported, %d skipped (duplicates / not applicable).',
            $fetched,
            $skipped
        ));
        return Command::SUCCESS;
    }
}
