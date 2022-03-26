<?php

namespace App\Controller\Service\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class ReinitialisationDeMotDePasseService
{
    /**
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        private ParameterBagInterface $parameterBag,
    )
    {
    }

    /**
     * @param mixed              $user
     * @param ResetPasswordToken $resetToken
     *
     * @return TemplatedEmail
     */
    public function __invoke(mixed $user, ResetPasswordToken $resetToken): TemplatedEmail
    {
        $fromAddress = $this->parameterBag->get('email_adresse');
        $fromName = $this->parameterBag->get('email_nom');

        return (new TemplatedEmail())
            ->from(new Address($fromAddress, $fromName))
            ->to($user->getEmail())
            ->subject('Votre demande de réinitialisation de mot de passe')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);
    }
}