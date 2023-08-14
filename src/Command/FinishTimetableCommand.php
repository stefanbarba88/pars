<?php

namespace App\Command;

use App\Classes\Data\FastTaskData;
use App\Entity\Activity;
use App\Entity\FastTask;
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

class FinishTimetableCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:finish:timetable')
      ->setDescription('U 6:45h salje plan za taj dan!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $plan = $this->em->getRepository(FastTask::class)->getTimeTableId(new DateTimeImmutable());

    if ($plan != 0) {
      $fastTask = $this->em->getRepository(FastTask::class)->find($plan);
      $fastTask->setStatus(FastTaskData::FINAL);
      $fastTask = $this->em->getRepository(FastTask::class)->save($fastTask);

      $args['timetable'] = $this->em->getRepository(FastTask::class)->getTimetableByFastTasks($fastTask);
      $args['datum']= $fastTask->getDatum();
      $args['users']= $this->em->getRepository(FastTask::class)->getUsersForEmail($fastTask, FastTaskData::FINAL);
      $this->mail->plan($args['timetable'], $args['users'], $args['datum']);
    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}