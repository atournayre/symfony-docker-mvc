<?php

namespace App\Field;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use ReflectionClass;
use ReflectionException;

class BootstrapFieldConfigurator implements FieldConfiguratorInterface
{
    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        try {
            return (new ReflectionClass($field->getFieldFqcn()))
                ->implementsInterface(FieldInterface::class);
        } catch (ReflectionException $e) {
            return false;
        }
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $field->setFormTypeOption('row_attr', [
            'class' => 'mb-3',
        ]);
    }
}