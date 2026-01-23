<?php

namespace Src\Classes\Commands;

use Src\Classes\Controller\AccessTokenController;
use MaxBrennemann\PhpUtilities\Tools;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: "manageToken",
)]

class TokenManager extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription("Token Manager - generate, delete, update auth tokens")
            ->addArgument("name", InputArgument::REQUIRED, "Name of the token")
            ->addArgument("action", InputArgument::REQUIRED, "Creation or deactivation  of the token");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument("name");
        $action = (string) $input->getArgument("action");

        if ($name == null || $action == null) {
            return Command::FAILURE;
        }

        $tag = "AccesToken";

        switch ($action) {
            case "create":
                $token = AccessTokenController::create($name);
                Tools::outputLog("Created token $name", $tag);
                Tools::outputLog("Your token is: $token", $tag);
                Tools::outputLog("Please note down this token as it will only be displayed once.", $tag, "warning");
                break;
            case "deactivate":
                $name = (int) $name;
                AccessTokenController::deactivate($name);
                Tools::outputLog("Deleted token $name", $tag);
                break;
            default:
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
