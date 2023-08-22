<?php

namespace App\Command;

use App\Classes\Data\FastTaskData;
use App\Entity\Activity;
use App\Entity\FastTask;
use App\Entity\Task;
use App\Entity\User;
use App\Service\ImportService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EditTimetableCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:edit:timetable')
      ->setDescription('U 20h salje ako je bilo promene posle 14:30 u odredjeno vreme!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $plan = $this->em->getRepository(FastTask::class)->findOneBy(['status' => FastTaskData::EDIT],['datum' => 'ASC']);
    if (!is_null($plan)) {

      $datum = $plan->getDatum();
      $timetable = $this->em->getRepository(FastTask::class)->getTimetableByFastTasks($plan);
      $subs = $this->em->getRepository(FastTask::class)->getSubsByFastTasks($plan);

      $users = $this->em->getRepository(FastTask::class)->getUsersForEmail($plan, FastTaskData::SAVED);
      $usersSub = $this->em->getRepository(FastTask::class)->getUsersSubsForEmail($plan, FastTaskData::SAVED);

      $this->mail->plan($timetable, $users, $datum);
      $this->mail->subs($subs, $usersSub, $datum);

    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}