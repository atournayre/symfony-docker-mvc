<?php

namespace App\Controller\Admin;

use App\Configurateur\DashboardConfigurateur;
use App\Controller\Admin\Utilisateur\UtilisateurCrudController;
use App\Entity\Utilisateur;
use App\Enum\Role;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    const ACTION_RETOUR = 'retour';

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    )
    {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('@EasyAdmin/page/content.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Application');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Administration', 'fa fa-cog')
            ->setPermission(Role::ROLE_ADMIN->name);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', Utilisateur::class)
            ->setPermission(Role::ROLE_ADMIN->name);
    }

    public function configureCrud(): Crud
    {
        return DashboardConfigurateur::crud(parent::configureCrud());
    }

    public function configureActions(): Actions
    {
        return DashboardConfigurateur::actions(parent::configureActions());
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $menuItems = [
            MenuItem::linkToUrl('Paramètres', 'fa fa-cog', $this->urlDEditionDeProfil($user)),
        ];
        return DashboardConfigurateur::userMenu(parent::configureUserMenu($user), $user, $menuItems);
    }

    private function urlDEditionDeProfil(UserInterface $user): string
    {
        return $this->adminUrlGenerator
            ->setController(UtilisateurCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($user->getId())
            ->generateUrl();
    }
}
