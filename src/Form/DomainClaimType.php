<?php

declare(strict_types=1);

namespace App\Domaining\Form;

use App\Domaining\DTO\DomainClaimRequestDTO;
use App\Domaining\Enum\DomainSurfaceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DomainClaimType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('domainName', TextType::class)
            ->add('ownerId', TextType::class)
            ->add('surfaceType', ChoiceType::class, [
                'choices' => DomainSurfaceType::cases(),
                'choice_label' => static fn (DomainSurfaceType $type): string => $type->value,
                'choice_value' => static fn (?DomainSurfaceType $type): ?string => $type?->value,
            ])
            ->add('surfaceKey', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DomainClaimRequestDTO::class,
        ]);
    }
}
