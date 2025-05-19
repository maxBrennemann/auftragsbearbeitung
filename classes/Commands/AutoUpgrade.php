<?php

namespace Classes\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

use Classes\Table\TableConfig;

use MaxBrennemann\PhpUtilities\Migrations\UpgradeManager;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(
    name: "autoupgrade",
)]

class AutoUpgrade extends Command
{

    protected function configure()
    {
        $this
            ->setDescription("Migrates PHP commands and updates mySQL tables. Autogenerates files")
            ->addOption("force", null, InputOption::VALUE_NONE, "Forces migration and skips errors")
            ->addOption("skip-migration", null, InputOption::VALUE_NONE, "Skips migration and only autogenerates files");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ob_start();
        TableConfig::generate();
        $content = ob_get_clean();

        $force = $input->getOption("force");
        $skipMigration = $input->getOption("skip-migration");

        $target = "files/res/js/classes";
        $destination = "node_modules/js-classes";

        file_put_contents("$target/tableconfig.js", $content);
        file_put_contents("$target/colorpicker.js", file_get_contents("node_modules/colorpicker/colorpicker.js"));
        file_put_contents("$target/notifications.js", file_get_contents("$destination/notifications.js"));
        file_put_contents("$target/ajax.js", file_get_contents("$destination/ajax.js"));
        file_put_contents("$target/bindings.js", file_get_contents("$destination/bindings.js"));

        if ($skipMigration) {
            return Command::SUCCESS;
        }

        if ($force) {
            UpgradeManager::upgrade(true, "upgrade/Changes/");
        } else {
            UpgradeManager::upgrade(false, "upgrade/Changes/");
        }

        return Command::SUCCESS;
    }
}
