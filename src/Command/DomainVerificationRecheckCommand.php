<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\Repository\DomainVerificationChallengeRepository;
use App\Domaining\ServiceInterface\Verification\DomainDnsVerificationServiceInterface;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:verification:recheck', description: 'Recheck pending Domaining DNS verification challenges that are ready for another attempt.')]
final class DomainVerificationRecheckCommand extends Command
{
    public function __construct(
        private readonly DomainVerificationChallengeRepository $challengeRepository,
        private readonly DomainDnsVerificationServiceInterface $verificationService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of pending challenges to check.', '50');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = max(1, (int) $input->getOption('limit'));
        $checked = 0;
        $passed = 0;

        foreach ($this->challengeRepository->findPendingReadyForCheck(new DateTimeImmutable(), $limit) as $challenge) {
            $result = $this->verificationService->recheck($challenge);
            ++$checked;
            if ('passed' === $result->status->value) {
                ++$passed;
            }
        }

        $output->writeln(sprintf('Checked %d pending domain verification challenge(s); %d passed.', $checked, $passed));

        return Command::SUCCESS;
    }
}

