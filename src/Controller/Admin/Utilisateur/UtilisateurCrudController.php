<?php

namespace App\Controller\Admin\Utilisateur;

use App\Controller\Admin\DashboardController;
use App\Entity\Utilisateur;
use App\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AvatarField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\Security;

class UtilisateurCrudController extends AbstractCrudController
{
    const ACTION_CONNEXION_EN_TANT_QUE = 'connexionEnTantQue';

    public function __construct(
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly Security                       $security,
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('avatar')
            ->setBasePath('avatars/uploads')
            ->setUploadDir('public/avatars/uploads')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setHelp('Pour un affichage optimal, choisissez une photo carrée.')
            ->onlyOnForms();
        yield AvatarField::new('avatar')
            ->setTextAlign('center')
            ->setHeight(50)
            ->formatValue(fn($value, Utilisateur $user) => $user?->getAvatarUrl())
            ->hideOnForm();
        yield TextField::new('prenom', 'Prénom')
            ->hideOnIndex();
        yield TextField::new('nom', 'Nom')
            ->hideOnIndex();
        yield TextField::new('nomComplet', 'Nom complet')
            ->formatValue(fn($value, Utilisateur $utilisateur) => $utilisateur->getNomComplet())
            ->hideOnForm();
        yield EmailField::new('email');
        yield TextField::new('motDePasse', 'Mot de passe')
            ->onlyOnForms();
    }

    public function createEntity(string $entityFqcn)
    {
        /** @var Utilisateur $entity */
        $entity = parent::createEntity($entityFqcn);
        $this->mettreAJourLeMotDePasse($entity, 'motdepasse');
        return $entity;
    }

    public function edit(AdminContext $context): KeyValueStore|RedirectResponse|Response
    {
        if ($this->lEntiteEstDifferentDeLUtilisateurConnecte($context)) {
            return parent::edit($context);
        }

        $context
            ->getCrud()
            ->setCustomPageTitle(Crud::PAGE_EDIT, 'Modifier mon profil');
        return parent::edit($context);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des utilisateurs')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier un utilisateur')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un utilisateur')
            ->setDefaultSort([
                                 'prenom' => 'ASC',
                                 'nom'    => 'ASC',
                             ]);
    }

    public function index(AdminContext $context): KeyValueStore|RedirectResponse|Response
    {
        if (!$this->security->isGranted(Role::ROLE_ADMIN->name)) {
            return $this->redirectToRoute('admin');
        }

        return parent::index($context);
    }

    public function configureActions(Actions $actions): Actions
    {
        $connexionEnTantQue = Action::new(self::ACTION_CONNEXION_EN_TANT_QUE)
            ->setIcon('fa fa-user-secret')
            ->addCssClass('btn btn-lg btn-link')
            ->setLabel(false)
            ->linkToUrl(
                fn(?Utilisateur $utilisateur) => is_null($utilisateur)
                    ? ''
                    : $this->genererLUrlPourSeConnecterEnTantQue($utilisateur->getEmail())
            );

        $actions = parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel(false)->addCssClass('btn btn-lg btn-link');
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nouvel utilisateur');
            })
            ->add(Crud::PAGE_INDEX, $connexionEnTantQue)
            ->setPermission('connexionEnTantQue', Role::ROLE_SUPER_ADMIN->name)
            ->reorder(Crud::PAGE_INDEX, [Action::EDIT, self::ACTION_CONNEXION_EN_TANT_QUE]);

        if (!$this->security->isGranted(Role::ROLE_ADMIN->name)) {
            $actions
                ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
                ->remove(Crud::PAGE_EDIT, DashboardController::ACTION_RETOUR);
        }

        return $actions;
    }

    private function genererLUrlPourSeConnecterEnTantQue(string $email): string
    {
        return $this->container
            ->get(AdminUrlGenerator::class)
            ->setController(TacheCrudController::class)
            ->setAction(Action::INDEX)
            ->set('_switch_user', $email)
            ->generateUrl();
    }

    private function lEntiteEstDifferentDeLUtilisateurConnecte(AdminContext $context): bool
    {
        return $context->getEntity()->getInstance() !== $context->getUser();
    }

    private function mettreAJourLeMotDePasse(Utilisateur $utilisateur, string $motDePasse): void
    {
        $password = $this->passwordHasherFactory
            ->getPasswordHasher($utilisateur)
            ->hash($motDePasse);

        $utilisateur
            ->setPassword($password);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Utilisateur            $entityInstance
     *
     * @return void
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance->leMotDePasseAEteModifie()) {
            $this->mettreAJourLeMotDePasse($entityInstance, $entityInstance->getMotDePasse());
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
