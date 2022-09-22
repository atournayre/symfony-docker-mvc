<?php

namespace App\Configurateur;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardConfigurateur
{
    const ACTION_RETOUR = 'retour';

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    )
    {
    }

    public static function crud(Crud $crud): Crud
    {
        return $crud
            ->renderContentMaximized()
            ->showEntityActionsInlined()
            ->showEntityActionsInlined(false)
            ->setDateFormat('dd/MM/yyyy')
            ->addFormTheme('bootstrap_5_layout.html.twig')
            ;
    }

    public static function actions(Actions $actions): Actions
    {
        $actionRetour = Action::new(self::ACTION_RETOUR, 'Retour', 'fa fa-angle-left')
            ->setCssClass('btn btn-lg btn-secondary text-secondary')
            ->linkToCrudAction(Action::INDEX)
        ;

        return $actions
            ->add(Crud::PAGE_NEW, $actionRetour)
            ->add(Crud::PAGE_EDIT, $actionRetour)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('far fa-save')->addCssClass('btn-lg')->setLabel('Enregistrer');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setIcon('fas fa-save')->addCssClass('btn-lg')->setLabel('Enregistrer puis créer');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel(false)->addCssClass('btn btn-lg btn-link');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fas fa-trash')->setLabel(false)->addCssClass('btn btn-lg btn-link');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa fa-save')->addCssClass('btn-lg')->setLabel('Enregistrer');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action->addCssClass('btn-lg')->setLabel('Enregistrer et continuer');
            })
            ->reorder(Crud::PAGE_NEW, [self::ACTION_RETOUR, Action::SAVE_AND_RETURN, Action::SAVE_AND_ADD_ANOTHER])
            ->reorder(Crud::PAGE_EDIT, [self::ACTION_RETOUR, Action::SAVE_AND_RETURN, Action::SAVE_AND_CONTINUE])
            ;
    }

    public static function userMenu(UserMenu $userMenu, UserInterface $user, array $menuItems): UserMenu
    {
        return $userMenu
            ->setAvatarUrl($user->getAvatarUrl())
            ->displayUserAvatar()
            ->addMenuItems($menuItems)
            ;
    }
}