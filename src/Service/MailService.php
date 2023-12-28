<?php

namespace App\Service;

use App\Classes\CompanyInfo;
use App\Classes\Data\UserRolesData;
use App\Entity\Calendar;
use App\Entity\Client;
use App\Entity\Email;
use App\Entity\User;
use DateTimeImmutable;
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
    $from = CompanyInfo::ORGANIZATION_MAIL_ADDRESS;
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
    $from = CompanyInfo::ORGANIZATION_MAIL_ADDRESS;
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
  public function subs($subs, $users, $datum): void {

    $args = [];
    $subject = 'Izmene na stalnim gradilištima za ' .  $datum->format('d.m.Y');
    $from = CompanyInfo::ORGANIZATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/subs.html.twig';
    $args['subs'] = $subs;
    $args['danas'] = $datum;

    foreach ($users as $user) {
      $to = $user->getEmail();
      $args['user'] = $user;
      $this->sendMail($to, $subject, $from, $sender, $template, $args);
    }
  }

  public function endTask($task, $datum, $logs, $to): void {

    $args = [];
    $subject = 'Zatvoren zadatak ' .  $task->getTitle();
    $from = CompanyInfo::ORGANIZATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/task.html.twig';
    $args['task'] = $task;
    $args['danas'] = $datum;
    $args['logs'] = $logs;

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function calendar(Calendar $calendar, string $to): void {

    $args = [];

    $subject = 'Zahtev od ' . $calendar->getUser()->first()->getFullName();

    $from = CompanyInfo::ORGANIZATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/zahtev.html.twig';
    $args['user'] = $calendar->getUser()->first()->getFullName();
    $args['calendar'] = $calendar;


    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function responseCalendar(Calendar $calendar): void {

    $args = [];

    $subject = 'Odgovor na zahtev ' . $calendar->getUser()->first()->getFullName();

    $from = CompanyInfo::ORGANIZATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/odgovor.html.twig';
    $args['user'] = $calendar->getUser()->first()->getFullName();
    $args['calendar'] = $calendar;

    $to = $calendar->getUser()->first()->getEmail();

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function tasksByClient(array $projects, Client $client, DateTimeImmutable $predhodniMesecDatum, DateTimeImmutable $date, string $to): void {

    $args = [];

    $subject = 'Broj izlazaka za  ' . $client->getTitle() . ' do ' . $date->format('d.m.Y');

    $from = CompanyInfo::ORGANIZATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/izlasci.html.twig';
    $args['client'] = $client;
    $args['projects'] = $projects;
    $args['date'] = $date;
    $args['predhodniMesecDatum'] = $predhodniMesecDatum;


    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function edit(User $user): void {
    $args = [];
    $to = $user->getEmail();
    $subject = 'Izmena profila na ' . CompanyInfo::ORGANIZATION_TITLE;
    $from = CompanyInfo::ORGANIZATION_MAIL_ADDRESS;
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
    $from = CompanyInfo::ORGANIZATION_MAIL_ADDRESS;
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
    $from = CompanyInfo::ORGANIZATION_MAIL_ADDRESS;
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
    $from = CompanyInfo::ORGANIZATION_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/reset_password.html.twig';

    $args['resetToken'] = $resetToken;
    $args['name'] = $user->getFullName();
    $args['support'] = CompanyInfo::SUPPORT_MAIL_ADDRESS;


    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

}
