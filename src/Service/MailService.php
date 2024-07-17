<?php

namespace App\Service;

use App\Classes\CompanyInfo;
use App\Classes\Data\UserRolesData;
use App\Entity\Calendar;
use App\Entity\Client;
use App\Entity\Email;
use App\Entity\ManagerChecklist;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\VerifyActivity;
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
    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
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

  public function checklistTask(ManagerChecklist $checklist): void {

    $args = [];

    $subject = 'Interni zadatak od ' . $checklist->getCreatedBy()->getFullName();

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/interni_task.html.twig';
    $args['user'] = $checklist->getUser()->getFullName();
    $args['checklist'] = $checklist;
    $args['link'] = $this->router->generate('app_checklist_view', ['id' => $checklist->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    $to = $checklist->getUser()->getEmail();

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function checklistEditTask(ManagerChecklist $checklist): void {

    $args = [];

    $subject = 'Interni zadatak izmena od ' . $checklist->getCreatedBy()->getFullName();

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/interni_task_edit.html.twig';
    $args['user'] = $checklist->getUser()->getFullName();
    $args['checklist'] = $checklist;
    $args['link'] = $this->router->generate('app_checklist_view', ['id' => $checklist->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    $to = $checklist->getUser()->getEmail();

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function checklistStatusTask(ManagerChecklist $checklist): void {

    $args = [];
    $subject = 'Promena statusa za interni zadatak #' . $checklist->getId() . ' od ' . $checklist->getUser()->getFullName();

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/interni_task_status.html.twig';
    $args['user'] = $checklist->getUser()->getFullName();
    $args['checklist'] = $checklist;
    $args['link'] = $this->router->generate('app_checklist_view', ['id' => $checklist->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    $to = $checklist->getUser()->getCompany()->getEmail();
    $too = $checklist->getCreatedBy()->getEmail();

    $this->sendMail($to, $subject, $from, $sender, $template, $args);
    $this->sendMail($too, $subject, $from, $sender, $template, $args);

  }

  public function checklistConvertTask(ManagerChecklist $checklist, Task $task): void {

    $args = [];
    $subject = 'Interni zadatak #' . $checklist->getId() . ' je konvertovan';

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/interni_task_convert.html.twig';
    $args['user'] = $checklist->getUser()->getFullName();
    $args['checklist'] = $checklist;
    $args['task'] = $task;
    $args['link'] = $this->router->generate('app_task_view_user', ['id' => $task->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    $to = $checklist->getUser()->getEmail();

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function test(string $email): void {
    $args = [];
    $to = $email;
    $subject = 'Registracija na ' . CompanyInfo::ORGANIZATION_TITLE;
    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/registration.html.twig';

    $args['link'] = $this->router->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
    $args['mail'] = $email;
    $args['password'] = $email;
    $args['name'] = $email;
    $args['role'] = $email;
    $args['support'] = CompanyInfo::SUPPORT_MAIL_ADDRESS;

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function carStatus($rezervacije, $company): void {

    $args = [];
    $danas = new DateTimeImmutable();
    $subject = 'Status vozila na dan ' .  $danas->format('d.m.Y');
    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/car_status.html.twig';
    $args['rezervacije'] = $rezervacije;
    $args['danas'] = $danas;
    $args['company'] = $company;

    $this->sendMail($company->getEmail(), $subject, $from, $sender, $template, $args);

  }
  public function carCheck($cars, $company): void {

    $args = [];
    $danas = new DateTimeImmutable();
    $subject = 'Isticanje registracije kod vozila ' .  $danas->format('d.m.Y');
    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/car_check.html.twig';
    $args['cars'] = $cars;
    $args['danas'] = $danas;
    $args['company'] = $company;

    $this->sendMail($company->getEmail(), $subject, $from, $sender, $template, $args);

  }

  public function plan($plan, $users, $datum): void {

    $args = [];
    $subject = 'Plan rada za ' .  $datum->format('d.m.Y');
    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
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
    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
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
    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
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

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/zahtev.html.twig';
    $args['user'] = $calendar->getUser()->first()->getFullName();
    $args['calendar'] = $calendar;


    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

//  public function checklistTask(ManagerChecklist $checklist): void {
//
//    $args = [];
//
//    $subject = 'Interni zadatak od ' . $checklist->getCreatedBy()->getFullName();
//
//    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
//    $sender = CompanyInfo::ORGANIZATION_TITLE;
//    $template = 'email/interni_task.html.twig';
//    $args['user'] = $checklist->getUser()->getFullName();
//    $args['checklist'] = $checklist;
//    $to = $checklist->getUser()->getEmail();
//
//
//    $this->sendMail($to, $subject, $from, $sender, $template, $args);
//
//  }

  public function responseCalendar(Calendar $calendar): void {

    $args = [];

    $subject = 'Odgovor na zahtev ' . $calendar->getUser()->first()->getFullName();

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/odgovor.html.twig';
    $args['user'] = $calendar->getUser()->first()->getFullName();
    $args['calendar'] = $calendar;

    $to = $calendar->getUser()->first()->getEmail();

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function tasksByClient(array $projects, Client $client, DateTimeImmutable $predhodniMesecDatum, DateTimeImmutable $date, string $to): void {

    $args = [];

    $subject = 'Broj izlazaka za ' . $client->getTitle() . ' do ' . $date->format('d.m.Y');

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
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
    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
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
    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
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
    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
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
    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/reset_password.html.twig';

    $args['resetToken'] = $resetToken;
    $args['name'] = $user->getFullName();
    $args['support'] = CompanyInfo::SUPPORT_MAIL_ADDRESS;


    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function responseActivityVerify(VerifyActivity $verifyActivity): void {

    $args = [];

    $subject = 'Odgovor na potvrdu prijema podatka za ' . $verifyActivity->getZaduzeni()->getFullName();

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/odgovor_prijem.html.twig';
    $args['user'] = $verifyActivity->getZaduzeni()->getFullName();
    $args['verify'] = $verifyActivity;

    $to = $verifyActivity->getZaduzeni()->getEmail();

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }
  public function activityVerify(VerifyActivity $verifyActivity, string $mail): void {

    $args = [];

    $subject = 'Zahtev za potvrda prijema podataka od ' . $verifyActivity->getZaduzeni()->getFullName();

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/potvrda_prijema.html.twig';
    $args['user'] = $verifyActivity->getZaduzeni()->getFullName();
    $args['verify'] = $verifyActivity;

    $to = $mail;

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

}
