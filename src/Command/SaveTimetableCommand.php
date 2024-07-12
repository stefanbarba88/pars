<?php

namespace App\Command;

use App\Classes\Data\FastTaskData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\Company;
use App\Entity\FastTask;
use App\Entity\Plan;
use App\Entity\Task;
use App\Entity\User;
use App\Service\ImportService;
use App\Service\MailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SaveTimetableCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:save:timetable')
      ->setDescription('Cuva timetable u odredjeno vreme!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);
    $danas = new DateTimeImmutable();
    $sledeciDan = $danas->modify('+1 day')->setTime(7,0);
    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);

    foreach ($companies as $company) {
      $plan = $this->em->getRepository(Plan::class)->findOneBy(['status' => FastTaskData::OPEN, 'company' => $company, 'datumKreiranja' => $sledeciDan], ['datumKreiranja' => 'ASC']);
      if (!is_null($plan)) {
        $trenutnoVreme = new DateTimeImmutable('now');
        $workTime = $company->getSettings()->getRadnoVreme();
        $vremeSatiMinuti = $workTime->format('H:i');
        $trenutnoVremeSatiMinuti = $trenutnoVreme->format('H:i');

        if ($vremeSatiMinuti === $trenutnoVremeSatiMinuti) {
          $plan->setStatus(FastTaskData::SAVED);
          $this->em->getRepository(Plan::class)->save($plan);
          $users = $this->em->getRepository(Plan::class)->getUsersForEmail($plan, FastTaskData::SAVED);
          $this->mail->plan($plan, $users);
        }
      }
//
//
//
//      if (!is_null($plan)) {
//
//        $superadmin = $this->em->getRepository(User::class)->findOneBy(['company' => $company, 'userType' => UserRolesData::ROLE_ADMIN, 'isSuspended' => false], ['id' => 'ASC']);
//        if (is_null($superadmin)) {
//          $superadmin = $this->em->getRepository(User::class)->findOneBy(['company' => $company, 'userType' => UserRolesData::ROLE_SUPER_ADMIN, 'isSuspended' => false], ['id' => 'ASC']);
//        }
//
//        $datum = $plan->getDatum();
//
//
//        $timetable = $this->em->getRepository(FastTask::class)->getTimetableByFastTasks($plan);
//        $subs = $this->em->getRepository(FastTask::class)->getSubsByFastTasks($plan);
//
//        $users = $this->em->getRepository(FastTask::class)->getUsersForEmail($plan, FastTaskData::SAVED);
//        $usersSub = $this->em->getRepository(FastTask::class)->getUsersSubsForEmail($plan, FastTaskData::SAVED);
//
//        $this->mail->plan($timetable, $users, $datum);
//        $this->mail->subs($subs, $usersSub, $datum);
//      }
    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}