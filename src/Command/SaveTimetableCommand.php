<?php

namespace App\Command;

use App\Classes\Data\FastTaskData;
use App\Entity\Activity;
use App\Entity\FastTask;
use App\Entity\User;
use App\Service\ImportService;
use App\Service\MailService;
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

    $plan = $this->em->getRepository(FastTask::class)->findOneBy(['status' => FastTaskData::OPEN],['datum' => 'ASC']);

    if(!is_null($plan)) {
      $plan->setStatus(FastTaskData::SAVED);
      $plan = $this->em->getRepository(FastTask::class)->save($plan);

      $timetable = $this->em->getRepository(FastTask::class)->getTimetableByFastTasks($plan);

      $datum = $plan->getDatum();
      $users= $this->em->getRepository(FastTask::class)->getUsersForEmail($plan, FastTaskData::OPEN);
      $this->mail->plan($timetable, $users, $datum);
    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}