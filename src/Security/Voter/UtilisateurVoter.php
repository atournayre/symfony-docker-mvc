<?php

namespace App\Security\Voter;

use App\Entity\Utilisateur;
use App\Enum\Role;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UtilisateurVoter extends Voter
{
    public function __construct(
        private readonly Security $security,
    )
    {
    }

    /**
     * @param string    $attribute
     * @param EntityDto $subject
     *
     * @return bool
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof EntityDto
            && $subject->getInstance() instanceof Utilisateur;
    }

    /**
     * @param string         $attribute
     * @param EntityDto      $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (!$subject->getInstance() instanceof UserInterface) {
            throw new LogicException('Subject is not an instance of User?');
        }

        if ($this->security->isGranted(Role::ROLE_ADMIN->name)) {
            return true;
        }

        return $subject->getInstance() === $user;
    }
}
