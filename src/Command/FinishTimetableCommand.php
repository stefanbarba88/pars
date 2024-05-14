<?php

namespace App\Command;

use App\Classes\Data\FastTaskData;
use App\Entity\Company;
use App\Entity\FastTask;
use App\Entity\User;
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
      ->setDescription('U 6:45h salje plan za taj dan!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $danas = new DateTimeImmutable();

    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);

    foreach ($companies as $company) {

      $plan = $this->em->getRepository(FastTask::class)->getTimeTableFinishCommand($danas, $company);

      if (!empty($plan)) {
        $fastTask = $plan[0];
        $fastTask->setStatus(FastTaskData::FINAL);
        $fastTask = $this->em->getRepository(FastTask::class)->save($fastTask);

        $timetable = $this->em->getRepository(FastTask::class)->getTimetableByFastTasks($fastTask);
        $datum = $fastTask->getDatum();
        $users = $this->em->getRepository(FastTask::class)->getUsersForEmail($fastTask, FastTaskData::FINAL);

        $subs = $this->em->getRepository(FastTask::class)->getSubsByFastTasks($fastTask);
        $usersSub = $this->em->getRepository(FastTask::class)->getUsersSubsForEmail($fastTask, FastTaskData::SAVED);

        if ($company->getId() == 1) {
          if (!empty($users)) {
            $users[] = $this->em->getRepository(User::class)->find(25);
          }
          if (!empty($usersSub)) {
            $usersSub[] = $this->em->getRepository(User::class)->find(25);
          }
        }

        $this->mail->plan($timetable, $users, $datum);
        $this->mail->subs($subs, $usersSub, $datum);
      }
    }

    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}