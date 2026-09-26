<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\ServiceInterface\Release\DomainReleaseGateServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:release:gate', description: 'Evaluate Domaining component release gate readiness.')]
final class DomainReleaseGateCommand extends Command
{
    public function __construct(private readonly DomainReleaseGateServiceInterface $releaseGateService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = $this->releaseGateService->evaluate();
        $output->writeln(json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return $report->passed ? Command::SUCCESS : Command::FAILURE;
    }
}
