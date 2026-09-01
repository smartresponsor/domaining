<?php

declare(strict_types=1);

namespace App\Domaining\Controller\Admin;

use App\Domaining\Entity\DomainDeclaration;
use App\Domaining\Enum\DomainApplicationRole;
use App\Domaining\Enum\DomainDeclarationStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class DomainDeclarationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DomainDeclaration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Domain declaration')
            ->setEntityLabelInPlural('Domain declarations')
            ->setPageTitle(Crud::PAGE_INDEX, 'Domain declarations')
            ->setDefaultSort(['applicationKey' => 'ASC', 'domainName' => 'ASC']);
    }

    public function createEntity(string $entityFqcn): DomainDeclaration
    {
        return new DomainDeclaration('application', 'brand', 'sandbox', 'example.invalid');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('applicationKey');
        yield TextField::new('brandKey');
        yield TextField::new('environment');
        yield TextField::new('domainName');
        yield ChoiceField::new('role')->setChoices(array_combine(
            array_map(static fn (DomainApplicationRole $role): string => $role->value, DomainApplicationRole::cases()),
            DomainApplicationRole::cases(),
        ));
        yield ChoiceField::new('status')->setChoices(array_combine(
            array_map(static fn (DomainDeclarationStatus $status): string => $status->value, DomainDeclarationStatus::cases()),
            DomainDeclarationStatus::cases(),
        ))->hideOnForm();
        yield TextField::new('objectUuid')->hideOnForm();
        yield TextField::new('objectSlug')->hideOnForm();
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
    }
}
