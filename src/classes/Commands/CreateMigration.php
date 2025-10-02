<?php

namespace Src\Classes\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: "createMigration",
)]

class CreateMigration extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription("Creates a new migration file.")
            ->addArgument("migrationName", InputArgument::REQUIRED, "What is the name of the migration?");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument("migrationName");
        if ($name == null) {
            return Command::FAILURE;
        }

        $date = date("Y-m-d");
        $content = file_get_contents("./.config/res/defaultMigration.txt");
        file_put_contents("database/Migrations/" . $date . "_" . $name . ".php", $content);

        return Command::SUCCESS;
    }
}
