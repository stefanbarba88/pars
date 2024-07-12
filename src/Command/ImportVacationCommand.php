<?php

namespace App\Command;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\CalendarData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Entity\Availability;
use App\Entity\Company;
use App\Entity\User;
use App\Entity\Vacation;
use App\Service\MailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportVacationCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:import:vacation')
      ->setDescription('Import neradni dani!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $danas = new DateTimeImmutable();
    $presek = new DateTimeImmutable('first day of July this year');
    $presek2 = new DateTimeImmutable('first day of January this year');


    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);

    foreach ($companies as $company) {

//      $users = $this->em->getRepository(User::class)->getZaposleniCommand($company);


//      foreach ($users as $user) {
//        $vacation = new Vacation();
//        $vacation->setCompany($user->getCompany());
//        $vacation->setUser($user);
//        $this->em->getRepository(Vacation::class)->save($vacation);
//      }



      $dostupnosti = $this->em->getRepository(Availability::class)->findBy(['company' => $company, 'type' => AvailabilityData::NEDOSTUPAN]);
        if (!empty($dostupnosti)) {
          foreach ($dostupnosti as $dostupnost) {

            $vacation = $dostupnost->getUser()->getVacation();
              if ($dostupnost->getDatum() < $presek && $dostupnost->getDatum() > $presek2 ) {
                if ($dostupnost->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
                  $kolektivni = $vacation->getVacationk1();
                  $used1 = $vacation->getUsed1();
                  $vacation->setVacationk1($kolektivni + 1);
                  $vacation->setUsed1($used1 + 1);
                } else {
                  if (is_null($dostupnost->getZahtev()) && $dostupnost->getTypeDay() != TipNeradnihDanaData::NEDELJA) {
                    $merenje = $vacation->getStopwatch1();
                    $used1 = $vacation->getUsed1();
                    $vacation->setStopwatch1($merenje + 1);
                    $vacation->setUsed1($used1 + 1);
                  }
                  if ($dostupnost->getZahtev() == CalendarData::SLOBODAN_DAN) {
                    $dan = $vacation->getVacationd1();
                    $used1 = $vacation->getUsed1();
                    $vacation->setVacationd1($dan + 1);
                    $vacation->setUsed1($used1 + 1);
                  }
                  if ($dostupnost->getZahtev() == CalendarData::ODMOR) {
                    $odmor = $vacation->getVacation1();
                    $used1 = $vacation->getUsed1();
                    $vacation->setVacation1($odmor + 1);
                    $vacation->setUsed1($used1 + 1);
                  }
                  if ($dostupnost->getZahtev() == CalendarData::BOLOVANJE) {
                    $ostalo = $vacation->getOther1();
                    $used1 = $vacation->getUsed1();
                    $vacation->setOther1($ostalo + 1);
                    $vacation->setUsed1($used1 + 1);
                  }
                  if ($dostupnost->getZahtev() == CalendarData::SLAVA) {
                    $slava = $vacation->getSlava();
                    $used1 = $vacation->getUsed1();
                    $vacation->setSlava($slava + 1);
                    $vacation->setUsed1($used1 + 1);
                  }
                }
              }

              if ($dostupnost->getDatum() > $presek ) {
                if ($dostupnost->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
                  $kolektivni = $vacation->getVacationk2();
                  $used2 = $vacation->getUsed2();
                  $vacation->setVacationk2($kolektivni + 1);
                  $vacation->setUsed2($used2 + 1);
                } else {
                  if (is_null($dostupnost->getZahtev()) && $dostupnost->getTypeDay() != TipNeradnihDanaData::NEDELJA) {
                    $merenje = $vacation->getStopwatch2();
                    $used2 = $vacation->getUsed2();
                    $vacation->setStopwatch2($merenje + 1);
                    $vacation->setUsed2($used2 + 1);
                  }
                  if ($dostupnost->getZahtev() == CalendarData::SLOBODAN_DAN) {
                    $dan = $vacation->getVacationd2();
                    $used2 = $vacation->getUsed2();
                    $vacation->setVacationd2($dan + 1);
                    $vacation->setUsed2($used2 + 1);
                  }
                  if ($dostupnost->getZahtev() == CalendarData::ODMOR) {
                    $odmor = $vacation->getVacation2();
                    $used2 = $vacation->getUsed2();
                    $vacation->setVacation2($odmor + 1);
                    $vacation->setUsed2($used2 + 1);
                  }
                  if ($dostupnost->getZahtev() == CalendarData::BOLOVANJE) {
                    $ostalo = $vacation->getOther2();
                    $used2 = $vacation->getUsed2();
                    $vacation->setOther2($ostalo + 1);
                    $vacation->setUsed2($used2 + 1);
                  }
                  if ($dostupnost->getZahtev() == CalendarData::SLAVA) {
                    $slava = $vacation->getSlava();
                    $used2 = $vacation->getUsed2();
                    $vacation->setSlava($slava + 1);
                    $vacation->setUsed2($used2 + 1);
                  }
                }
              }

              $this->em->getRepository(Vacation::class)->save($vacation);
          }
        }

    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}