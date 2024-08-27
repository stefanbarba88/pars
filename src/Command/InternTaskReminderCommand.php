<?php

namespace App\Command;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\CalendarData;
use App\Classes\Data\InternTaskStatusData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Entity\Availability;
use App\Entity\Company;
use App\Entity\ManagerChecklist;
use App\Entity\User;
use App\Entity\Vacation;
use App\Service\MailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InternTaskReminderCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:check:intern_tasks')
      ->setDescription('Opominje za interni task!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);
    $start = microtime(true);
    $tasks = $this->em->getRepository(ManagerChecklist::class)->getChecklistForCommand();

    foreach ($tasks as $task) {
      $this->mail->checklistTaskReminder($task);
    }

    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}