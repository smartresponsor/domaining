<?php

declare(strict_types=1);

namespace App\Domaining\Command;

use App\Domaining\Dto\DomainClaimRequest;
use App\Domaining\Enum\DomainApplicationRole;
use App\Domaining\Enum\DomainClaimStatus;
use App\Domaining\Enum\DomainSurfaceType;
use App\Domaining\Enum\DomainVerificationStatus;
use App\Domaining\Repository\DomainVerificationChallengeRepository;
use App\Domaining\ServiceInterface\Claim\DomainClaimCreationServiceInterface;
use App\Domaining\ServiceInterface\Declaration\DomainDeclarationServiceInterface;
use App\Domaining\ServiceInterface\Verification\DomainVerificationChallengeServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'domaining:consumer:ensure', description: 'Ensure a declared custom-domain consumer and its verification claim exist.')]
final class DomainConsumerEnsureCommand extends Command
{
    public function __construct(
        private readonly DomainDeclarationServiceInterface $declarationService,
        private readonly DomainClaimCreationServiceInterface $claimCreationService,
        private readonly DomainVerificationChallengeServiceInterface $challengeService,
        private readonly DomainVerificationChallengeRepository $challengeRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('domain', null, InputOption::VALUE_REQUIRED)
            ->addOption('application', null, InputOption::VALUE_REQUIRED)
            ->addOption('brand', null, InputOption::VALUE_REQUIRED)
            ->addOption('environment', null, InputOption::VALUE_REQUIRED, 'Deployment environment', 'prod')
            ->addOption('owner', null, InputOption::VALUE_REQUIRED)
            ->addOption('surface', null, InputOption::VALUE_REQUIRED, 'Domaining surface type', DomainSurfaceType::Application->value)
            ->addOption('surface-key', null, InputOption::VALUE_REQUIRED)
            ->addOption('declaration-only', null, InputOption::VALUE_NONE, 'Create or refresh only the application/domain declaration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $domain = trim((string) $input->getOption('domain'));
        $application = trim((string) $input->getOption('application'));
        $brand = trim((string) $input->getOption('brand'));
        $environment = trim((string) $input->getOption('environment'));
        $owner = trim((string) $input->getOption('owner'));
        $surfaceKey = trim((string) $input->getOption('surface-key'));
        $surface = DomainSurfaceType::tryFrom(trim((string) $input->getOption('surface')));
        $declarationOnly = (bool) $input->getOption('declaration-only');

        if ('' === $domain || '' === $application || '' === $brand || '' === $environment) {
            $output->writeln('<error>domain, application, brand and environment must be valid.</error>');

            return Command::INVALID;
        }
        if (!$declarationOnly && ('' === $owner || '' === $surfaceKey || null === $surface)) {
            $output->writeln('<error>owner, surface and surface-key must be valid for an external-domain claim.</error>');

            return Command::INVALID;
        }

        $declaration = $this->declarationService->declare($application, $brand, $environment, $domain, DomainApplicationRole::Primary);
        if ((bool) $input->getOption('declaration-only')) {
            $output->writeln(sprintf('Declaration: %s [%s/%s] %s', $declaration->domainName(), $declaration->applicationKey(), $declaration->brandKey(), $declaration->status()->value));

            return Command::SUCCESS;
        }

        $claim = $this->claimCreationService->createClaim(new DomainClaimRequest($domain, $owner, $surface, $surfaceKey));

        if (DomainClaimStatus::Verified === $claim->status()) {
            $output->writeln(sprintf('Domain %s is already verified.', $domain));

            return Command::SUCCESS;
        }

        $challenge = $this->challengeRepository->findOneBy(['claim' => $claim], ['createdAt' => 'DESC']);
        if (null === $challenge || in_array($challenge->status(), [DomainVerificationStatus::Failed, DomainVerificationStatus::Expired], true)) {
            $challenge = $this->challengeService->issueTxtChallenge($claim);
        }

        $output->writeln(sprintf('Declaration: %s [%s/%s] %s', $declaration->domainName(), $declaration->applicationKey(), $declaration->brandKey(), $declaration->status()->value));
        $output->writeln(sprintf('Claim: %s [%s] %s/%s', $claim->domainName(), $claim->status()->value, $claim->surfaceType()->value, $claim->surfaceKey()));
        $output->writeln(sprintf('TXT name: %s', $challenge->recordName()));
        $output->writeln(sprintf('TXT value: %s', $challenge->recordValue()));
        $output->writeln(sprintf('Challenge status: %s', $challenge->status()->value));

        return Command::SUCCESS;
    }
}
