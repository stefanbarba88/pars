<?php

namespace App\Service;

use App\Classes\CompanyInfo;
use App\Classes\Data\UserRolesData;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailService {

  public function __construct(private readonly MailerInterface $mailer, private readonly UrlGeneratorInterface $router) {
  }

  public function sendMail(string $to, string $subject, string $from, string $sender, string $template, array $args): void {

    $message = (new TemplatedEmail())
      ->from(new Address($from, $sender))
      ->to($to)
      ->subject($subject)
      ->htmlTemplate($template)
      ->context($args);

    $this->mailer->send($message);
  }

  public function registration(User $user): void {
    $args = [];
    $to = $user->getEmail();
    $subject = 'Registracija na ' . CompanyInfo::ORGANIZATION_TITLE;
    $from = CompanyInfo::REGISTRATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/registration.html.twig';

    $args['link'] = $this->router->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
    $args['mail'] = $user->getEmail();
    $args['password'] = $user->getPlainPassword();
    $args['name'] = $user->getFullName();
    $args['role'] = UserRolesData::userRoleTitle($user);

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

}
