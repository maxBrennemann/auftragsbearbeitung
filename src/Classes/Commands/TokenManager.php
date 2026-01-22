<?php

namespace Src\Classes\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: "manageToken",
)]

class TokenManager extends Command
{
    protected function configure(): void
    {
        $this->setDescription("Token Manager - generate, delete, update auth tokens");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return parent::execute($input, $output);
    }
}
