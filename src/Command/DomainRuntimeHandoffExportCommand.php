<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\ServiceInterface\Runtime\DomainRuntimeHandoffServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:runtime:handoff-export', description: 'Export provider-neutral runtime handoff instructions for domain routes.')]
final class DomainRuntimeHandoffExportCommand extends Command
{
    public function __construct(private readonly DomainRuntimeHandoffServiceInterface $handoffService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln((string) json_encode(
            ['domainRuntimeHandoff' => $this->handoffService->buildReport()->toArray()],
            JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
        ));

        return Command::SUCCESS;
    }
}

