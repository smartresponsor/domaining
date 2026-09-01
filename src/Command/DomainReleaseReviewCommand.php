<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\ServiceInterface\Review\DomainReleaseReviewServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:release:review', description: 'Build the aggregated Domaining RC release review report.')]
final class DomainReleaseReviewCommand extends Command
{
    public function __construct(private readonly DomainReleaseReviewServiceInterface $reviewService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = $this->reviewService->buildReport();
        $output->writeln((string) json_encode(['domainReleaseReview' => $report->toArray()], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return $report->releaseCandidateReady ? Command::SUCCESS : Command::FAILURE;
    }
}

