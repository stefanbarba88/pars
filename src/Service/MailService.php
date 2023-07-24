<?php

namespace App\Service;

use App\Classes\CompanyInfo;
use App\Classes\Data\UserRolesData;
use App\Entity\Email;
use App\Entity\User;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailService {

  public function __construct(private readonly MailerInterface $mailer, private readonly UrlGeneratorInterface $router, private readonly ManagerRegistry $em, private Environment $twig) {
  }

  public function sendMail(string $to, string $subject, string $from, string $sender, string $template, array $args): void {

    $message = (new TemplatedEmail())
      ->from(new Address($from, $sender))
      ->to($to)
      ->subject($subject)
      ->htmlTemplate($template)
      ->context($args);

    $this->mailer->send($message);

    $backtrace = debug_backtrace();

    $line = $backtrace[0]['line'];
    $function = $backtrace[1]['function'];
    $file = $backtrace[0]['file'];

    $body = $this->twig->render($template, $args);

    $mail = Email::create($to, $subject, $body, $file, $function, $line);
    $this->em->getRepository(Email::class)->save($mail);

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
    $args['support'] = CompanyInfo::SUPPORT_MAIL_ADDRESS;

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function plan($plan, $users, $datum): void {

    $args = [];
    $subject = 'Plan rada za ' .  $datum->format('d.m.Y');
    $from = CompanyInfo::REGISTRATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/plan.html.twig';
    $args['timetable'] = $plan;
    $args['danas'] = $datum;

    foreach ($users as $user) {
      $to = $user->getEmail();
      $args['user'] = $user;
      $this->sendMail($to, $subject, $from, $sender, $template, $args);
    }
  }

  public function edit(User $user): void {
    $args = [];
    $to = $user->getEmail();
    $subject = 'Izmena profila na ' . CompanyInfo::ORGANIZATION_TITLE;
    $from = CompanyInfo::REGISTRATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/edit_account.html.twig';

    $args['link'] = $this->router->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
    $args['mail'] = $user->getEmail();
    $args['password'] = $user->getPlainPassword();
    $args['name'] = $user->getFullName();
    $args['role'] = UserRolesData::userRoleTitle($user);
    $args['support'] = CompanyInfo::SUPPORT_MAIL_ADDRESS;

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function deactivate(User $user): void {
    $args = [];
    $to = $user->getEmail();
    $subject = 'Deaktivacija naloga na ' . CompanyInfo::ORGANIZATION_TITLE;
    $from = CompanyInfo::REGISTRATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/deactivate.html.twig';

    $args['name'] = $user->getFullName();
    $args['support'] = CompanyInfo::SUPPORT_MAIL_ADDRESS;

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function activate(User $user): void {
    $args = [];
    $to = $user->getEmail();
    $subject = 'Aktivacija naloga na ' . CompanyInfo::ORGANIZATION_TITLE;
    $from = CompanyInfo::REGISTRATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/activate.html.twig';

    $args['link'] = $this->router->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
    $args['name'] = $user->getFullName();
    $args['support'] = CompanyInfo::SUPPORT_MAIL_ADDRESS;

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function resetPassword(User $user, $resetToken): void {
    $args = [];
    $to = $user->getEmail();
    $subject = 'Reset lozinke na ' . CompanyInfo::ORGANIZATION_TITLE;
    $from = CompanyInfo::REGISTRATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/reset_password.html.twig';

    $args['resetToken'] = $resetToken;
    $args['name'] = $user->getFullName();
    $args['support'] = CompanyInfo::SUPPORT_MAIL_ADDRESS;


    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

}
