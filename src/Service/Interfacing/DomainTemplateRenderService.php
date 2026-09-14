<?php

declare(strict_types=1);

namespace App\Domaining\Service\Interfacing;

use App\Domaining\Dto\DomainInterfacingPayload;
use App\Domaining\Dto\DomainTemplateRenderResult;
use App\Domaining\ServiceInterface\Interfacing\DomainTemplateRenderServiceInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class DomainTemplateRenderService implements DomainTemplateRenderServiceInterface
{
    /**
     * @param list<string> $templateCandidates
     */
    public function __construct(
        #[Autowire(service: 'service_container')]
        private ContainerInterface $container,
        #[Autowire('%domaining.template_surface%')]
        private string $templateSurface,
        #[Autowire('%domaining.interfacing_template_candidates%')]
        private array $templateCandidates,
    ) {
    }

    public function render(DomainInterfacingPayload $payload): DomainTemplateRenderResult
    {
        $payloadArray = $payload->toArray();

        if (!$this->container->has('twig')) {
            return new DomainTemplateRenderResult(false, $this->templateSurface, null, $payloadArray);
        }

        $twig = $this->container->get('twig');
        if (!is_object($twig) || !method_exists($twig, 'getLoader') || !method_exists($twig, 'render')) {
            return new DomainTemplateRenderResult(false, $this->templateSurface, null, $payloadArray);
        }

        $loader = $twig->getLoader();
        foreach ($this->templateCandidates as $template) {
            if ('' === trim($template)) {
                continue;
            }

            if (method_exists($loader, 'exists') && !$loader->exists($template)) {
                continue;
            }

            try {
                $html = $twig->render($template, [
                    'surface' => $this->templateSurface,
                    'domain' => $payloadArray,
                    'payload' => $payloadArray,
                    'locations' => $payloadArray['locations'],
                    'location' => $payloadArray['locations'],
                    'binding' => $payloadArray['binding'],
                    'publication' => $payloadArray['publication'],
                    'slotContract' => $payloadArray['slotContract'],
                    'title' => $payloadArray['domainName'],
                    'subtitle' => 'Custom-domain lifecycle surface',
                ]);

                return new DomainTemplateRenderResult(true, $this->templateSurface, $template, $payloadArray, $html);
            } catch (\Throwable) {
                continue;
            }
        }

        return new DomainTemplateRenderResult(false, $this->templateSurface, null, $payloadArray);
    }
}
