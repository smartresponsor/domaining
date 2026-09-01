<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\ServiceInterface\Publication\DomainPublicationReadServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:publication:intent-export', description: 'Export current domain publication snapshots as provider-neutral JSON.')]
final class DomainPublicationIntentExportCommand extends Command
{
    public function __construct(
        private readonly DomainBindingRepository $bindingRepository,
        private readonly DomainPublicationReadServiceInterface $publicationReadService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = [];
        foreach ($this->bindingRepository->findAll() as $binding) {
            $rows[] = $this->publicationReadService->snapshot($binding)->toArray();
        }

        $output->writeln((string) json_encode(['domainPublication' => $rows], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }
}

