<?php

namespace App\EventSubscriber;

use App\Event\NewUserEvent;
use App\Event\UserUpdatedEvent;
use App\Event\UserResetPasswordEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mime\Address;

class UserSubscriber implements EventSubscriberInterface
{
    public ?MailerInterface $mailer = null;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function onUpdatingUser(UserUpdatedEvent $event): void
    {
        // création du mail
        $email = new TemplatedEmail();
        $email->from('Service Wonder <fahamygaston@gmail.com>')
            ->to('destinataire@hotmail.com')
            ->subject('Mise à jour du profile.')
            ->htmlTemplate('@templates_emails/updateProfile.html.twig');

        $this->mailer->send($email); //envoi du mail
    }

    public function onResetPassword(UserResetPasswordEvent $event)
    {
        $user = $event->getUser();
        $emailUser = $user->getEmail();

        $email = new TemplatedEmail();
        $email->from('Département RH <fahamygaston@gmail.com>')
            ->to(new Address($emailUser, $user->getFirstname()))
            ->subject('Modification de mot de passe')
            ->htmlTemplate('@templates_emails/resetPassword.html.twig')
            ->context([
                //'user' => $user
                'token' => $event->getToken()
            ]);

        $this->mailer->send($email);
    }

    public function onNewUserSubscription(NewUserEvent $event)
    {
        $user = $event->getUser();
        $emailUser = $user->getEmail();

        $email = new TemplatedEmail();
        $email->from('Département RH <fahamygaston@gmail.com>')
            ->to(new Address($emailUser, $user->getFirstname()))
            ->subject('Bienvenu')
            ->htmlTemplate('@templates_emails/welcomeNewUser.html.twig')
            ->context([
                //'user' => $user
            ]);

        $this->mailer->send($email);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserUpdatedEvent::class => 'onUpdatingUser',
            UserResetPasswordEvent::class => 'onResetPassword',
            NewUserEvent::class => 'onNewUserSubscription'
        ];
    }
}
