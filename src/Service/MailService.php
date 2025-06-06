<?php

namespace App\Service;

use App\Classes\CompanyInfo;
use App\Classes\Data\UserRolesData;
use App\Entity\Calendar;
use App\Entity\Client;
use App\Entity\Comment;
use App\Entity\Email;
use App\Entity\ManagerChecklist;
use App\Entity\Signature;
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

  private function sendMailWithAttachments(string $to, string $subject, string $from, string $sender, string $template, array $args, array $attachments): void {
    $message = (new TemplatedEmail())
      ->from(new Address($from, $sender))
      ->to($to)
      ->subject($subject)
      ->htmlTemplate($template)
      ->context($args);

    // Add attachments
    foreach ($attachments as $attachment) {
      $message->attachFromPath($attachment);
    }

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

  public function checklistTaskReminder(ManagerChecklist $checklist): void {

    $args = [];

    $subject = 'Podsetnik na interni zadatak od ' . $checklist->getCreatedBy()->getFullName();

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/reminder_interni_task.html.twig';
    $args['user'] = $checklist->getUser()->getFullName();
    $args['checklist'] = $checklist;
    $args['link'] = $this->router->generate('app_checklist_view', ['id' => $checklist->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    $to = $checklist->getUser()->getEmail();

    $this->sendMail($to, $subject, $from, $sender, $template, $args);

  }

  public function checklistCommentTask(ManagerChecklist $checklist, Comment $comment): void {

    $args = [];

    $subject = 'Komentar od ' . $comment->getUser()->getFullName();

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/interni_task_comment.html.twig';
    $args['user'] = $comment->getUser()->getFullName();
    $args['checklist'] = $checklist;
    $args['comment'] = $comment;
    $args['link'] = $this->router->generate('app_checklist_view', ['id' => $checklist->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

    if ($comment->getUser() == $checklist->getCreatedBy()) {
      $to = $checklist->getUser()->getEmail();
    } else {
      $to = $checklist->getCreatedBy()->getEmail();
    }

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

  public function signatureApprove(Signature $signature): void {

    $args = [];

    $subject = 'Potvrda zahteva ' . $signature->getEmployee()->getFullName() . ' za rad na projektu ' . $signature->getRelation()->getTitle();

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/signature_approve.html.twig';
    $args['user'] = $signature->getEmployee()->getFullName();
    $args['signature'] = $signature;


    $this->sendMail($signature->getEmployee()->getEmail(), $subject, $from, $sender, $template, $args);

  }

  public function signature(Signature $signature, $to): void {

    $args = [];

    $subject = 'Prijem zahteva od ' . $signature->getEmployee()->getFullName() . ' za rad na projektu ' . $signature->getRelation()->getTitle();

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/signature.html.twig';
    $args['user'] = $signature->getEmployee()->getFullName();
    $args['signature'] = $signature;

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

  public function reportsGenerator($mail, $client, $prethodniMesecDatum, $datum, $attachments, $projects): void {

    $args = [];

    if (is_null($client)) {
      $subject = 'Izveštaji za period ' . $prethodniMesecDatum->format('d.m.Y') . ' - ' . $datum->format('d.m.Y');
    } else {
      $subject = 'Izveštaji za klijenta ' . $client->getTitle() . ' za period ' . $prethodniMesecDatum->format('d.m.Y') . ' - ' . $datum->format('d.m.Y');

    }

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/download_files.html.twig';
    $args['client'] = $client;
    $args['date'] = $datum;
    $args['prethodniMesecDatum'] = $prethodniMesecDatum;
    $args['projects'] = $projects;
    $to = $mail;

    $this->sendMailWithAttachments($to, $subject, $from, $sender, $template, $args, $attachments);

  }

  public function reportsDailyExing($mail, $client, $datum, $attachments, $projects): void {

    $args = [];

    $subject = 'Dnevni spisak za Exing gradilišta za datum ' . $datum->format('d.m.Y.');

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/exing_files.html.twig';
    $args['client'] = $client;
    $args['datum'] = $datum;
    $args['projects'] = $projects;
    $to = $mail;

    $this->sendMailWithAttachments($to, $subject, $from, $sender, $template, $args, $attachments);

  }
  public function reportsDailyUrban($mail, $project, $datum, $attachments, $projects): void {

    $args = [];

    $subject = 'Dnevni spisak za '. $project->getTitle() .' za datum ' . $datum->format('d.m.Y.');

    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/exing_files.html.twig';
    $args['project'] = $project;
    $args['datum'] = $datum;
    $args['projects'] = $projects;
    $to = $mail;

    $this->sendMailWithAttachments($to, $subject, $from, $sender, $template, $args, $attachments);

  }

    public function reportsDailyPlato($mail, $project, $datum, $attachments, $projects, $ukupno): void {

        $args = [];

        $subject = 'Dnevni izveštaj broja zaposlenih prisutnih na gradilištu / Daily Report of the Number of Employees Present on the Construction Site';

        $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
        $sender = CompanyInfo::ORGANIZATION_TITLE;
        $template = 'email/plato_files.html.twig';
        $args['project'] = $project;
        $args['datum'] = $datum;
        $args['projects'] = $projects;
        $args['ukupno'] = $ukupno;
        $to = $mail;

        $this->sendMailWithAttachments($to, $subject, $from, $sender, $template, $args, $attachments);

    }

  public function reportsGeneratorExpo($mail, $client, $prethodniMesecDatum, $datum, $attachments, $projects): void {

    $args = [];


    $subject = 'Izveštaji EXPO projekte za period ' . $prethodniMesecDatum->format('d.m.Y') . ' - ' . $datum->format('d.m.Y');


    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/download_files_expo.html.twig';
    $args['client'] = $client;
    $args['date'] = $datum;
    $args['prethodniMesecDatum'] = $prethodniMesecDatum;
    $args['projects'] = $projects;
    $to = $mail;

    $this->sendMailWithAttachments($to, $subject, $from, $sender, $template, $args, $attachments);

  }

  public function reportsUserGenerator($mail, $user, $prviDan, $poslednjiDan, $attachments): void {

    $args = [];

    $subject = 'Periodični izveštaj za ' . $user->getFullName() . ' za period ' . $prviDan->format('d.m.Y') . ' - ' . $poslednjiDan->format('d.m.Y');



    $from = CompanyInfo::SUPPORT_MAIL_ADDRESS;
    $sender = CompanyInfo::ORGANIZATION_TITLE;
    $template = 'email/report_user.html.twig';
    $args['user'] = $user;
    $args['prviDan'] = $prviDan;
    $args['poslednjiDan'] = $poslednjiDan;
    $to = $mail;

    $this->sendMailWithAttachments($to, $subject, $from, $sender, $template, $args, $attachments);

  }

}
