<?php

namespace App\Command;


use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\Company;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CarsStatusCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:car:status')
      ->setDescription('Proverava status vozila svakog dana!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);
    
    foreach ($companies as $company) {
      if ($company->getSettings()->isCar()) {
        $rezervacije = [];
        $cars = $this->em->getRepository(Car::class)->findBy(['company' => $company, 'isSuspended' => false]);
        foreach ($cars as $car) {
          $rezervacija = $this->em->getRepository(CarReservation::class)->findOneBy(['car' => $car, 'kmStop' => null], ['id' => 'DESC']);
          if (!is_null($rezervacija)) {
            $rezervacije[] = $rezervacija;
          }
        }

        if (!empty($rezervacije)) {
          $this->mail->carStatus($rezervacije, $company);
        }
      }
    }

    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}