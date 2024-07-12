<?php

namespace App\Command;

use App\Classes\Data\FastTaskData;
use App\Entity\Activity;
use App\Entity\Company;
use App\Entity\FastTask;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\User;
use App\Service\MailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FinishedTasksCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:finish:task')
      ->setDescription('Na 5 minuta proverava da li ima zavrsenih zadataka!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $danas = new DateTimeImmutable();

    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);

    foreach ($companies as $company) {
  //za sve firme
      $timetable = $this->em->getRepository(Task::class)->getTasksByDateForEmail($danas, $company);

      if (!empty($timetable)) {
        foreach ($timetable as $taskId) {
          $task = $this->em->getRepository(Task::class)->find($taskId);
          $logs = $this->em->getRepository(StopwatchTime::class)->getEndTime($task);
          $datum = $task->getDatumKreiranja();
          $this->mail->endTask($task, $datum, $logs, $company->getEmail());
        }
      }
    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}