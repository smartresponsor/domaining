<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\ServiceInterface\Export\DomainStateExportServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:state:export', description: 'Export provider-neutral Domaining state for Administering/runtime review.')] 
final class DomainStateExportCommand extends Command
{
    public function __construct(private readonly DomainStateExportServiceInterface $exportService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln((string) json_encode(
            ['domainStateExport' => $this->exportService->buildExport()->toArray()],
            JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
        ));

        return Command::SUCCESS;
    }
}

