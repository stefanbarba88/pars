<?php

namespace App\Command;

use App\Classes\Data\FastTaskData;
use App\Entity\Company;
use App\Entity\FastTask;
use App\Entity\Plan;
use App\Entity\Task;
use App\Service\MailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
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
      ->setDescription('U 00:01h kreira zadatke za taj dan na osnovu plana!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $danas = new DateTimeImmutable();

    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);

    foreach ($companies as $company) {

      $plan = $this->em->getRepository(Plan::class)->getTimeTableFinishCommand($danas, $company);

      if (!empty($plan)) {
        $fastTask = $plan[0];
        $this->em->getRepository(Task::class)->createTasksFromPlan($fastTask);
      }
    }

    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}