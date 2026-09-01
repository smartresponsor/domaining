<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\ServiceInterface\Diagnostic\DomainDiagnosticServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:diagnostic:report', description: 'Export domain lifecycle diagnostics for runtime and operator review.')]
final class DomainDiagnosticReportCommand extends Command
{
    public function __construct(private readonly DomainDiagnosticServiceInterface $diagnosticService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = $this->diagnosticService->buildReport();

        $output->writeln((string) json_encode(
            ['domainDiagnosticReport' => $report->toArray()],
            JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
        ));

        return $report->pass ? Command::SUCCESS : Command::FAILURE;
    }
}

