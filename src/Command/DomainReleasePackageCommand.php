<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\ServiceInterface\Package\DomainReleasePackageServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:release:package', description: 'Build the final provider-neutral Domaining RC package report.')]
final class DomainReleasePackageCommand extends Command
{
    public function __construct(private readonly DomainReleasePackageServiceInterface $packageService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = $this->packageService->buildPackage();
        $output->writeln((string) json_encode(['domainReleasePackage' => $report->toArray()], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return $report->packageReady ? Command::SUCCESS : Command::FAILURE;
    }
}

