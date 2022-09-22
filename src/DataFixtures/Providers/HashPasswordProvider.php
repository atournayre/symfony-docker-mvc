<?php

namespace App\DataFixtures\Providers;

use App\Entity\Utilisateur;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class HashPasswordProvider
{
    public function __construct(
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
    )
    {
    }

    public function hashPassword(string $plainPassword): string
    {
        return $this->passwordHasherFactory
            ->getPasswordHasher(new Utilisateur())
            ->hash($plainPassword);
    }
}
