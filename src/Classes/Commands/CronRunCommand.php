<?php

namespace Src\Classes\Commands;

use Src\Classes\Cron\CronManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: "runCron",
)]

class CronRunCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription("Runs crontasks.");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        CronManager::run();

        return Command::SUCCESS;
    }
}
