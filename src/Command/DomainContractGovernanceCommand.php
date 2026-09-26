<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\ServiceInterface\Contract\DomainContractGovernanceServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:contract:governance', description: 'Inspect provider-neutral Domaining contract versions and export surfaces.')]
final class DomainContractGovernanceCommand extends Command
{
    public function __construct(private readonly DomainContractGovernanceServiceInterface $governanceService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = $this->governanceService->buildReport();
        $output->writeln((string) json_encode(
            ['domainContractGovernance' => $report->toArray()],
            JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
        ));

        return $report->ready ? Command::SUCCESS : Command::FAILURE;
    }
}
