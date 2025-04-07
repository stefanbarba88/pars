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

class DailyReportCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:daily:report')
      ->setDescription('Pregled dana!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);
//    $company = $this->em->getRepository(Company::class)->find(2);

    foreach ($companies as $company) {

        $args['vozila'] = $this->em->getRepository(Car::class)->getCarRegistration($company);

        $args['rezervacije'] = [];
        $vozila = $this->em->getRepository(Car::class)->findBy(['company' => $company, 'isSuspended' => false]);
        foreach ($vozila as $car) {
          $rezervacija = $this->em->getRepository(CarReservation::class)->findOneBy(['car' => $car, 'kmStop' => null], ['id' => 'DESC']);
          if (!is_null($rezervacija)) {
            $args['rezervacije'][] = $rezervacija;
          }
        }

        $args['ugovori'] = $this->em->getRepository(User::class)->getUsersCheckEmailAll($company);

        $args['nezatvoreniTaskovi'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedCommand($company);
        $args['taskovi'] = $this->em->getRepository(Task::class)->countGetTasksCommand($company);
        $args['taskoviInt'] = count($this->em->getRepository(ManagerChecklist::class)->findBy(['status' => InternTaskStatusData::ZAPOCETO, 'company' => $company]));
        $args['taskoviVerify'] = count($this->em->getRepository(Task::class)->findBy(['status' => TaskStatusData::ZAVRSENO, 'company' => $company]));

        $args['kalendar'] = $this->em->getRepository(Calendar::class)->countCalendarRequestsCommand($company);
        $args['zapisi'] = $this->em->getRepository(StopwatchTime::class)->findAllToCheckCountCommand($company);

        $dostupni = $this->em->getRepository(Availability::class)->getAllDostupnostiDanasBasicCommand($company);
        $args['nedostupni'] = $dostupni['noNedostupni'];

        $this->mail->dailyReport($args, $company);

      }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}