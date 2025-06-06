<?php

namespace App\Command;

use App\Classes\Data\FastTaskData;
use App\Classes\Data\InternTaskStatusData;
use App\Classes\Data\RepeatingIntervalData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\Availability;
use App\Entity\Company;
use App\Entity\FastTask;
use App\Entity\ManagerChecklist;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use App\Service\MailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RepeatingTasksCreatorCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:create:repeating_task')
      ->setDescription('Proverava da li postoji zadatak koji se ponavlja i kreira ga.')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $danas = new DateTimeImmutable();

    $tasksIntern = $this->em->getRepository(ManagerChecklist::class)->findBy(['datumPonavljanja' => $danas->setTime(0,0), 'repeating' => 1]);
    $tasksIds = [];

    foreach ($tasksIntern as $task) {

      if ($task->getStatus() != InternTaskStatusData::KONVERTOVANO) {
        $tasksIds[] = $task->getId();
        $this->em->detach($task);
        $newTask = new ManagerChecklist();
        $newTask->setTask($task->getTask());
        $newTask->setCreatedBy($task->getCreatedBy());
        $newTask->setUser($task->getUser());
        $newTask->setProject($task->getProject());
        $newTask->setCategory($task->getCategory());
        $newTask->setCompany($task->getCompany());
        $newTask->setPriority($task->getPriority());

        $newTask->setDatumKreiranja($task->getDatumPonavljanja());

        if (!is_null($task->getTime())) {
          $time = $task->getTime();
          $datum = $newTask->getDatumKreiranja();
          $newTime = $datum->setTime($time->format('H'), $time->format('i'));
          $newTask->setTime($newTime);
        }


        if ($task->getRepeatingInterval() == RepeatingIntervalData::TACAN_DATUM) {
          $newTask->setDatumPonavljanja(null);
          $newTask->setRepeating(null);
          $newTask->setRepeatingInterval(null);
        } else {
          if ($task->getRepeatingInterval() == RepeatingIntervalData::DAY) {
            $newTask->setDatumPonavljanja($task->getDatumPonavljanja()->modify('+1 day'));
            $newTask->setRepeatingInterval($task->getRepeatingInterval());
            $newTask->setRepeating($task->getRepeating());
          }
          if ($task->getRepeatingInterval() == RepeatingIntervalData::WEEK) {
            $newTask->setDatumPonavljanja($task->getDatumPonavljanja()->modify('+1 week'));
            $newTask->setRepeatingInterval($task->getRepeatingInterval());
            $newTask->setRepeating($task->getRepeating());
          }
          if ($task->getRepeatingInterval() == RepeatingIntervalData::MONTH) {
            $newTask->setDatumPonavljanja($task->getDatumPonavljanja()->modify('+1 month'));
            $newTask->setRepeatingInterval($task->getRepeatingInterval());
            $newTask->setRepeating($task->getRepeating());
          }
          if ($task->getRepeatingInterval() == RepeatingIntervalData::YEAR) {
            $newTask->setDatumPonavljanja($task->getDatumPonavljanja()->modify('+1 year'));
            $newTask->setRepeatingInterval($task->getRepeatingInterval());
            $newTask->setRepeating($task->getRepeating());
          }
        }
        $this->em->getRepository(ManagerChecklist::class)->save($newTask);

      }
    }

    $this->em->getRepository(ManagerChecklist::class)->removeRepeating($tasksIds);


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}