<?php

namespace Classes\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: "createMigration",
)]

class CreateMigration extends Command
{

    private string $text = '';

    protected function configure()
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
        file_put_contents("database/Migrations/" . $date . "_" . $name . ".php", $this->text);

        return Command::SUCCESS;
    }
}
