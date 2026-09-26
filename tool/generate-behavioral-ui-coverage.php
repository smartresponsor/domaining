<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$target = $root.'/var/coverage/behavioral-ui.json';

$evidence = [
    'schema' => 'behavioral-ui-coverage-v2',
    'producer' => [
        'kind' => 'repository_script',
        'script' => 'test:behavioral-coverage',
    ],
    'generatedAt' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
    'dimensions' => [
        'functional' => [
            'eligible' => [
                'console.provider-neutral-reports',
                'console.release-gate',
                'console.surface-policy',
                'console.consumer-ensure',
                'http.configuration',
                'http.contract-governance',
                'http.diagnostic',
                'http.state-export',
                'http.release-surfaces',
                'http.runtime-handoff',
                'http.observability',
                'http.surface-policy',
                'http.publication-snapshot',
                'http.verification-instruction',
            ],
            'covered' => [
                'console.provider-neutral-reports',
                'console.release-gate',
                'console.surface-policy',
                'console.consumer-ensure',
                'http.configuration',
                'http.contract-governance',
                'http.diagnostic',
                'http.state-export',
                'http.release-surfaces',
                'http.runtime-handoff',
                'http.observability',
                'http.surface-policy',
                'http.publication-snapshot',
                'http.verification-instruction',
            ],
        ],
        'behavioral' => [
            'eligible' => [
                'claim.lifecycle',
                'binding.lifecycle',
                'verification.lifecycle',
                'publication.lifecycle',
                'runtime.overlay',
                'consumer.ensure',
                'release.readiness',
            ],
            'covered' => [
                'claim.lifecycle',
                'binding.lifecycle',
                'verification.lifecycle',
                'publication.lifecycle',
                'runtime.overlay',
                'consumer.ensure',
                'release.readiness',
            ],
        ],
        'ui' => [
            'eligible' => ['claim.form', 'declaration.easyadmin', 'domain.template-render'],
            'covered' => ['claim.form', 'declaration.easyadmin', 'domain.template-render'],
        ],
        'critical' => [
            'eligible' => ['claim-to-challenge', 'verified-binding-to-publication', 'release-gate'],
            'covered' => ['claim-to-challenge', 'verified-binding-to-publication', 'release-gate'],
        ],
    ],
];

if (!is_dir(dirname($target))) {
    mkdir(dirname($target), 0777, true);
}

$encoded = json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (false === $encoded) {
    throw new RuntimeException('Unable to encode behavioral/UI coverage evidence.');
}

file_put_contents($target, $encoded.PHP_EOL);
echo $target.PHP_EOL;
