<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

$dsn = 'smtp://83f7c75fc5daad:e6b2abadd62e7a@sandbox.smtp.mailtrap.io:2525';
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

$email = (new Email())
    ->from('noreply@minus.fr')
    ->to('ton-email-ici@example.com')  
    ->subject('Test email')
    ->text('Test envoi mail via Symfony Mailer');

try {
    $mailer->send($email);
    echo "Email envoyé avec succès\n";
} catch (\Exception $e) {
    echo "Erreur lors de l'envoi: " . $e->getMessage() . "\n";
}

