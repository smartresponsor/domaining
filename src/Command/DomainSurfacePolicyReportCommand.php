<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\Enum\DomainSurfaceType;
use App\Domaining\ServiceInterface\Policy\DomainSurfacePolicyServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:policy:surface-report', description: 'Export provider-neutral domain surface policy diagnostics.')]
final class DomainSurfacePolicyReportCommand extends Command
{
    public function __construct(private readonly DomainSurfacePolicyServiceInterface $policyService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('ownerId', InputArgument::REQUIRED, 'Owner, tenant, workspace, or application identifier.')
            ->addOption('surface-type', null, InputOption::VALUE_OPTIONAL, 'Optional surface type: tenant, workspace, application, storefront, landing, api.')
            ->addOption('surface-key', null, InputOption::VALUE_OPTIONAL, 'Optional surface key.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $surfaceTypeValue = $input->getOption('surface-type');
        $surfaceType = is_string($surfaceTypeValue) && '' !== $surfaceTypeValue ? DomainSurfaceType::from($surfaceTypeValue) : null;
        $surfaceKeyValue = $input->getOption('surface-key');
        $surfaceKey = is_string($surfaceKeyValue) && '' !== $surfaceKeyValue ? $surfaceKeyValue : null;

        $report = $this->policyService->buildReport((string) $input->getArgument('ownerId'), $surfaceType, $surfaceKey);
        $output->writeln(json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return $report->pass ? Command::SUCCESS : Command::FAILURE;
    }
}

