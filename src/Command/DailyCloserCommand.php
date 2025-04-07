<?php

namespace App\Command;


use App\Classes\Data\InternTaskStatusData;
use App\Classes\Data\TaskStatusData;
use App\Entity\Availability;
use App\Entity\Calendar;
use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\Company;
use App\Entity\ManagerChecklist;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\User;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DailyCloserCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:daily:closer')
      ->setDescription('Verifikuje zapise, taskove, kalendar!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);

    foreach ($companies as $company) {
        //proverava da li ima neki zahtev da nije prihvacen do roka i automatski ga prihvata
        $calendars = $this->em->getRepository(Calendar::class)->getCalendarConfirmCommand($company);
        if (!empty($calendars)) {
          foreach ($calendars as $calendar) {
            $this->em->getRepository(Calendar::class)->allowCalendar($calendar);
          }
        }

      $zapisi = $this->em->getRepository(StopwatchTime::class)->findAllToCheckCommand($company);
        if (!empty($zapisi)) {
          foreach ($zapisi as $zapis) {
            $zapis->setChecked(1);
            $this->em->getRepository(StopwatchTime::class)->save($zapis);
          }
        }
    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}