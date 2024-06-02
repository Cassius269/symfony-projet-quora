<?php

namespace App\EventSubscriber;

use App\Event\UserUpdatedEvent;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
        $email = new Email();
        $email->from('Service Wonder <fahamygaston@gmail.com>')
            ->to('destinataire@hotmail.com')
            ->subject('Mise à jour du profile')
            ->text('Votre profile a été mis à jour');

        $this->mailer->send($email); //envoi du mail
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserUpdatedEvent::class => 'onUpdatingUser',
        ];
    }
}
