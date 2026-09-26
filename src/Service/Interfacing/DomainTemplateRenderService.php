<?php

declare(strict_types=1);

namespace App\Domaining\Service\Interfacing;

use App\Domaining\DTO\DomainInterfacingPayloadDTO;
use App\Domaining\DTO\DomainTemplateRenderResultDTO;
use App\Domaining\ServiceInterface\Interfacing\DomainTemplateRenderServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

final readonly class DomainTemplateRenderService implements DomainTemplateRenderServiceInterface
{
    /**
     * @param list<string> $templateCandidates
     */
    public function __construct(
        private Environment $twig,
        #[Autowire('%domaining.template_surface%')]
        private string $templateSurface,
        #[Autowire('%domaining.interfacing_template_candidates%')]
        private array $templateCandidates,
    ) {
    }

    public function render(DomainInterfacingPayloadDTO $payload): DomainTemplateRenderResultDTO
    {
        $payloadArray = $payload->toArray();

        $loader = $this->twig->getLoader();
        foreach ($this->templateCandidates as $template) {
            if ('' === trim($template)) {
                continue;
            }

            if (!$loader->exists($template)) {
                continue;
            }

            try {
                $html = $this->twig->render($template, [
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

                return new DomainTemplateRenderResultDTO(true, $this->templateSurface, $template, $payloadArray, $html);
            } catch (\Throwable) {
                continue;
            }
        }

        return new DomainTemplateRenderResultDTO(false, $this->templateSurface, null, $payloadArray);
    }
}
