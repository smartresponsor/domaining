<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\ServiceInterface\Manifest\DomainReleaseManifestServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:release:manifest', description: 'Export the Domaining release manifest for RC review.')]
final class DomainReleaseManifestCommand extends Command
{
    public function __construct(private readonly DomainReleaseManifestServiceInterface $manifestService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $manifest = $this->manifestService->buildManifest();
        $output->writeln((string) json_encode(['domainReleaseManifest' => $manifest->toArray()], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return $manifest->releaseCandidateReady ? Command::SUCCESS : Command::FAILURE;
    }
}
