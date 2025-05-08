<?php

namespace App\Command;

use App\Classes\Data\UserRolesData;
use App\Classes\Slugify;
use Twig\Environment as TwigEnvironment;
use App\Entity\Availability;
use App\Entity\Calendar;
use App\Entity\Holiday;
use App\Entity\ManagerChecklist;
use App\Entity\Overtime;
use App\Entity\User;
use App\Service\MailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class UserReportCommand extends Command {
  private $em;
  private $mail;
  private $parameterBag;
  private $knpSnappyPdf;
  private $twig;

  public function __construct(EntityManagerInterface $em, MailService $mail, ParameterBagInterface $parameterBag, Pdf $knpSnappyPdf, TwigEnvironment $twig) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
    $this->parameterBag = $parameterBag;
    $this->knpSnappyPdf = $knpSnappyPdf;
    $this->twig = $twig; // Dodajemo Twig servis
  }


  protected function configure() {
    $this
      ->setName('app:employee:report')
      ->setDescription('Kreira mesecne reporte za svakog zaposlenog!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);
//    if (!$this->lock()) {
//      $output->writeln('The command is already running in another process.');
//
//      return 0;
//    }

    $start = microtime(true);
    putenv('QT_QPA_PLATFORM=offscreen');
      // Pronalazi sve aktivne korisnike kompanije 1 koji nisu suspendovani
      $users = $this->em->getRepository(User::class)->findBy([
        'isSuspended' => false,
        'company' => 1,
        'userType' => UserRolesData::ROLE_EMPLOYEE
      ], ['prezime' => 'ASC']);

      // Proverava za svakog korisnika da li ima glasove
      foreach ($users as $usr) {

        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $reportsPath = $projectDir . '/public/assets/employee/';

        $currentYear = date('Y');
        $currentMonth = date('m', strtotime('-1 month'));

        $reportMonthPath = "$reportsPath$currentYear/$currentMonth";

        if (!is_dir($reportMonthPath)) {
          mkdir($reportMonthPath, 0777, true);
        }


        $args['user'] = $usr;
//        $args['image'] = $usr->getImage();
        $args['logo'] = 'assets/images/logo/logoXls.png';

        $datum = new DateTimeImmutable();

        $args['prviDan'] = $datum->modify('first day of last month')->setTime(0, 0);
        $args['poslednjiDan'] = $datum->modify('last day of last month')->setTime(23, 59, 59);


        $args['noRadnihDana'] = $this->em->getRepository(Holiday::class)->brojRadnihDanaMesec($datum);
        $args['noDays'] = $this->em->getRepository(Availability::class)->getDaysByUserMesec($usr, $datum);
        $args['overtime'] = $this->em->getRepository(Overtime::class)->getOvertimeByUserMesec($usr, $datum);
        $args['noRequests'] = $this->em->getRepository(Calendar::class)->getRequestByUserMesec($usr, $datum);

        $args['reports'] = $this->em->getRepository(User::class)->getReportMesec($usr, $datum);
        $args['intern'] = $this->em->getRepository(ManagerChecklist::class)->getInternTasksMesec($usr, $datum);




        $brojElemenata = isset($args['reports'][0]) ? count($args['reports'][0]) : 0;

        // Sabiranje vremena iz drugog podniza
        $ukupnoMinuta = 0;
        $brojVremeR = 0;

        foreach ($args['reports'][1] as $podniz) {
          if (isset($podniz['vremeR'])) {
            $brojVremeR++;
            list($sati, $minuti) = explode(':', $podniz['vremeR']);
            $ukupnoMinuta += (int)$sati * 60 + (int)$minuti;
          }
        }

        // Računanje proseka u minutima
        $prosekMinuta = $brojVremeR > 0 ? intdiv($ukupnoMinuta, $brojVremeR) : 0;

        // Konvertovanje minuta u sate i minute za ukupno vreme i prosek
        $ukupnoSati = intdiv($ukupnoMinuta, 60);
        $ukupnoOstatakMinuta = $ukupnoMinuta % 60;

        $prosekSati = intdiv($prosekMinuta, 60);
        $prosekOstatakMinuta = $prosekMinuta % 60;

        // Povratni rezultat
        $args['details'] = [
          'broj_elemenata' => $brojElemenata,
          'ukupno_vreme' => sprintf('%02d:%02d', $ukupnoSati, $ukupnoOstatakMinuta),
          'prosek_vreme' => sprintf('%02d:%02d', $prosekSati, $prosekOstatakMinuta),
        ];

//    dd($args);


//    return $this->render('report_employee/pdf.html.twig', $args);
//    return $this->render('report_employee/pdf_view.html.twig', $args);

        $html = $this->twig->render('report_employee/pdf.html.twig', $args);

        $pdfContent = $this->knpSnappyPdf->getOutputFromHtml($html);
        $ime = Slugify::slugify($usr->getFullName(), '_');
        $fileName = $reportMonthPath . '/' . $usr->getId(). '_report_' . $ime . '.pdf';
        file_put_contents($fileName, $pdfContent);


        $attachments[] = $fileName;

        $this->mail->reportsUserGenerator($usr->getEmail(), $usr, $args['prviDan'], $args['poslednjiDan'], $attachments);
//        $this->mail->reportsUserGenerator('stefanmaksimovic88@gmail.com', $usr, $args['prviDan'], $args['poslednjiDan'], $attachments);

      }





    $end = microtime(true);
    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";
    return 1;
  }
}