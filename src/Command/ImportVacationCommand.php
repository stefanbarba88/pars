<?php

namespace App\Command;

use App\Classes\AppConfig;
use App\Classes\Avatar;
use App\Classes\Data\AvailabilityData;
use App\Classes\Data\CalendarData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Classes\Data\UserRolesData;
use App\Entity\Availability;
use App\Entity\City;
use App\Entity\Company;
use App\Entity\Titula;
use App\Entity\User;
use App\Entity\Vacation;
use App\Entity\ZaposleniPozicija;
use App\Service\MailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImportVacationCommand extends Command {
  private $em;
  private $mail;
  private $params;



  public function __construct(EntityManagerInterface $em, MailService $mail, ParameterBagInterface $params) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
    $this->params = $params;
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

    $company = $this->em->getRepository(Company::class)->find(14);

//    $putanja = $this->params->get('kernel.project_dir'). '/var/rvr.xml';
//
//    $lagerData = simplexml_load_file($putanja);
//
//
//    foreach ($lagerData as $koris) {
//
//      $user = new User();
//      $user->setCompany($this->em->getRepository(Company::class)->find(26));
//      $user->setPlainPassword(AppConfig::DEFAULT_PASS);
//
//      $user->setUserType(UserRolesData::ROLE_EMPLOYEE);
//      $user->setTrack(0);
//
//      $user->setIme($koris->ime);
//      $user->setPrezime($koris->prezime);
//      $user->setPol((int)$koris->pol);
//      $user->setIsKadrovska(true);
//
//      $gradString = trim($koris->grad);
//
//// Razdvajanje stringa na delove pre i posle zareza
//      $deloviGrada = explode(',', $gradString);
//
//// Prvi deo (pre zareza)
//      $gradPreZareza = isset($deloviGrada[0]) ? trim($deloviGrada[0]) : null;
//// Drugi deo (posle zareza)
//      $gradPosleZareza = isset($deloviGrada[1]) ? trim($deloviGrada[1]) : null;
//
//// Prvo pokušaj da nađeš grad pre zareza
//      $grad = $this->em->getRepository(City::class)->findOneBy(['title' => $gradPreZareza]);
//
//// Ako je rezultat null, pokušaj sa gradom posle zareza
//      if ($grad === null && $gradPosleZareza) {
//        $grad = $this->em->getRepository(City::class)->findOneBy(['title' => $gradPosleZareza]);
//      }
//
//
//      $user->setGrad($grad);
//      $user->setAdresa($koris->adresa);
//      $user->setJmbg($koris->jmbg);
//
//      $user->setVrstaZaposlenja((int)$koris->vrsta_zaposlenja);
//      $user->setPozicija($this->em->getRepository(ZaposleniPozicija::class)->findOneBy(['title' => mb_strtoupper(trim($koris->pozicija))]));
//
//      //      $user->setNivoObrazovanja((int)$koris->nivo_obrazovanja);
//      //      $user->setZvanje($this->em->getRepository(Titula::class)->findOneBy(['title' => mb_strtoupper(trim($koris->zvanje))]));
//
//      $user->setNivoObrazovanja(null);
//
//      $user->setZvanje(null);
//
//      $user->setMestoRada(trim($koris->mesto_rada));
//
//      $start = DateTimeImmutable::createFromFormat('d.m.Y.', $koris->start);
//      $stop = DateTimeImmutable::createFromFormat('d.m.Y.', $koris->start);
//      if ($start) {
//        $user->setPocetakUgovora($start);
//      }
//      if ($stop) {
//        $user->setPocetakUgovora($stop);
//      }
//
//      $file = Avatar::getAvatar($this->params->get('kernel.project_dir') . $user->getAvatarUploadPath(), $user);
//      $this->em->getRepository(User::class)->register($user, $file, $this->params->get('kernel.project_dir'));
//
//    }


    //zvanje
//    $jobs = [
//      "trgovac",
//      "pravno birotehnićki smer",
//      "ekonomista za finansijsko računovodstvo",
//      "ekonomski tehničar",
//      "građevinski tehničar",
//      "mašinski tehničar",
//      "hemiski tehničar",
//      "frizer",
//      "gimnazija",
//      "tehničar prodaje",
//      "modni krojač",
//      "ugostiteljski tehničar",
//      "usmereno obrazovanje",
//      "tekstilni tehničar",
//      "maturant gimnazije",
//      "prehrambeni tehničar",
//      "osnovno obrazovanje",
//      "komercijalni tehničar",
//      "finansijski tehničar",
//      "trgovina i ugostiteljstvo",
//      "tekstilac",
//      "stomatološki tehničar",
//      "poslastičar",
//      "diplomirani inženjer menadžmenta",
//      "trgovinski tehničar",
//      "viša ekonomska",
//      "profesor tehničkog obrazovanja",
//      "grafički tehničar",
//      "dip.inženjer poljoprivrede",
//      "hemisko-tehnološki tehničar",
//      "ekonom.za poreze i carine",
//      "diplomirani ekonomista",
//      "manupulant u poljoprivredi",
//      "Hemijsko tehnološki tehničar"
//    ];
//
//
//    foreach ($jobs as $job) {
//
//      $pos = new Titula();
//      $pos->setTitle(mb_strtoupper($job));
//      $pos = $this->em->getRepository(Titula::class)->save($pos);
//    }

    //Zaposleni pozicije
    $jobs = [
    "Administrativni operater",
    "Alatničar",
    "Direktor održavanja",
    "Direktor prodaje",
    "Finansijski direktor",
    "Generalni direktor",
    "Glavni majstor na mašini",
    "Higijeničar",
    "Kartonažer",
    "Kartonažer + magacioner u magacinu gotovog proizvoda",
    "Kartonažer + ppz",
    "Kartonažer na sortiranju otpada + PPZ",
    "Magacioner u magacinu gotovih proizvoda",
    "Magacioner u magacinu paleta",
    "Magacioner u magacinu repromaterijala",
    "Magacioner u magacinu rezervnih delova i pomoćnog materijala",
    "Majstor na mašini",
    "Manuelni radnik",
    "Planer proizvodnje",
    "Pomoćnik glavnog majstora I kategorije",
    "Pomoćnik glavnog majstora II kategorije",
    "Pomoćnik glavnog majstora na mašini - II kategorije",
    "Pomoćnik kartonažera",
    "Pomoćnik kartonažera + higijeničar",
    "Pomoćnik magacionera u magacinu gotovih proizvoda",
    "Pomoćnik magacionera u magacinu repromaterijala",
    "Pomoćnik tehničkog direktora",
    "Pomoćnik viljuškariste",
    "Poslovni sekretar",
    "Radnik na održavanju paleta",
    "Radnik na OMS/Viljuškarista",
    "Referent komercijale",
    "Referent nabavke",
    "Referent prodaje",
    "Referent računovodstva",
    "Referent za fakturisanje",
    "Referent za HR",
    "Referent za standardizaciju",
    "Savetnik generalnog direktora",
    "Šef magacina gotovih proizvoda",
    "Šef računovodstva",
    "Šef smene I kategorija",
    "Šef smene II kategorija",
    "Stručni saradnik u računovodstvu",
    "Supervizor održavanja",
    "Tehničar laboratorije",
    "Tehničar održavanja objekta - Domar",
    "Tehničar planiranog i neplaniranog održavanja",
    "Tehničar planiranog održavanja",
    "Tehnički direktor",
    "Viljuškarista",
    "Vodeći glavni majstor na mašini",
    "Vodeći glavni majstor na mašini + ppz",
    "Vozač teretnog vozila"
    ];
//
    foreach ($jobs as $job) {

      $pos = new ZaposleniPozicija();
      $pos->setTitle(mb_strtoupper($job));
      $pos->setCompany($company);
      $pos = $this->em->getRepository(ZaposleniPozicija::class)->save($pos);
    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
//  protected function execute(InputInterface $input, OutputInterface $output): int {
//    $io = new SymfonyStyle($input, $output);
//
//    $start = microtime(true);
//
//    $danas = new DateTimeImmutable();
//    $presek = new DateTimeImmutable('first day of July this year');
//    $presek2 = new DateTimeImmutable('first day of January this year');
//
//
//    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);
//
//    foreach ($companies as $company) {
//
////      $users = $this->em->getRepository(User::class)->getZaposleniCommand($company);
//
//
////      foreach ($users as $user) {
////        $vacation = new Vacation();
////        $vacation->setCompany($user->getCompany());
////        $vacation->setUser($user);
////        $this->em->getRepository(Vacation::class)->save($vacation);
////      }
//
//
//
//      $dostupnosti = $this->em->getRepository(Availability::class)->findBy(['company' => $company, 'type' => AvailabilityData::NEDOSTUPAN]);
//      if (!empty($dostupnosti)) {
//        foreach ($dostupnosti as $dostupnost) {
//
//          $vacation = $dostupnost->getUser()->getVacation();
//          if ($dostupnost->getDatum() < $presek && $dostupnost->getDatum() > $presek2 ) {
//            if ($dostupnost->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
//              $kolektivni = $vacation->getVacationk1();
//              $used1 = $vacation->getUsed1();
//              $vacation->setVacationk1($kolektivni + 1);
//              $vacation->setUsed1($used1 + 1);
//            } else {
//              if (is_null($dostupnost->getZahtev()) && $dostupnost->getTypeDay() != TipNeradnihDanaData::NEDELJA) {
//                $merenje = $vacation->getStopwatch1();
//                $used1 = $vacation->getUsed1();
//                $vacation->setStopwatch1($merenje + 1);
//                $vacation->setUsed1($used1 + 1);
//              }
//              if ($dostupnost->getZahtev() == CalendarData::SLOBODAN_DAN) {
//                $dan = $vacation->getVacationd1();
//                $used1 = $vacation->getUsed1();
//                $vacation->setVacationd1($dan + 1);
//                $vacation->setUsed1($used1 + 1);
//              }
//              if ($dostupnost->getZahtev() == CalendarData::ODMOR) {
//                $odmor = $vacation->getVacation1();
//                $used1 = $vacation->getUsed1();
//                $vacation->setVacation1($odmor + 1);
//                $vacation->setUsed1($used1 + 1);
//              }
//              if ($dostupnost->getZahtev() == CalendarData::BOLOVANJE) {
//                $ostalo = $vacation->getOther1();
//                $used1 = $vacation->getUsed1();
//                $vacation->setOther1($ostalo + 1);
//                $vacation->setUsed1($used1 + 1);
//              }
//              if ($dostupnost->getZahtev() == CalendarData::SLAVA) {
//                $slava = $vacation->getSlava();
//                $used1 = $vacation->getUsed1();
//                $vacation->setSlava($slava + 1);
//                $vacation->setUsed1($used1 + 1);
//              }
//            }
//          }
//
//          if ($dostupnost->getDatum() > $presek ) {
//            if ($dostupnost->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
//              $kolektivni = $vacation->getVacationk2();
//              $used2 = $vacation->getUsed2();
//              $vacation->setVacationk2($kolektivni + 1);
//              $vacation->setUsed2($used2 + 1);
//            } else {
//              if (is_null($dostupnost->getZahtev()) && $dostupnost->getTypeDay() != TipNeradnihDanaData::NEDELJA) {
//                $merenje = $vacation->getStopwatch2();
//                $used2 = $vacation->getUsed2();
//                $vacation->setStopwatch2($merenje + 1);
//                $vacation->setUsed2($used2 + 1);
//              }
//              if ($dostupnost->getZahtev() == CalendarData::SLOBODAN_DAN) {
//                $dan = $vacation->getVacationd2();
//                $used2 = $vacation->getUsed2();
//                $vacation->setVacationd2($dan + 1);
//                $vacation->setUsed2($used2 + 1);
//              }
//              if ($dostupnost->getZahtev() == CalendarData::ODMOR) {
//                $odmor = $vacation->getVacation2();
//                $used2 = $vacation->getUsed2();
//                $vacation->setVacation2($odmor + 1);
//                $vacation->setUsed2($used2 + 1);
//              }
//              if ($dostupnost->getZahtev() == CalendarData::BOLOVANJE) {
//                $ostalo = $vacation->getOther2();
//                $used2 = $vacation->getUsed2();
//                $vacation->setOther2($ostalo + 1);
//                $vacation->setUsed2($used2 + 1);
//              }
//              if ($dostupnost->getZahtev() == CalendarData::SLAVA) {
//                $slava = $vacation->getSlava();
//                $used2 = $vacation->getUsed2();
//                $vacation->setSlava($slava + 1);
//                $vacation->setUsed2($used2 + 1);
//              }
//            }
//          }
//
//          $this->em->getRepository(Vacation::class)->save($vacation);
//        }
//      }
//
//    }
//
//
//    $end = microtime(true);
//
//
//    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";
//
//
//    return 1;
//  }
}