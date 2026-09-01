<?php

declare(strict_types=1);

namespace App\Domaining\Form;

use App\Domaining\Entity\DomainDeclaration;
use App\Domaining\Enum\DomainApplicationRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DomainDeclarationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('applicationKey', TextType::class)
            ->add('brandKey', TextType::class)
            ->add('environment', TextType::class)
            ->add('domainName', TextType::class)
            ->add('role', ChoiceType::class, [
                'choices' => DomainApplicationRole::cases(),
                'choice_label' => static fn (DomainApplicationRole $role): string => $role->value,
                'choice_value' => static fn (?DomainApplicationRole $role): ?string => $role?->value,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DomainDeclaration::class,
        ]);
    }
}
