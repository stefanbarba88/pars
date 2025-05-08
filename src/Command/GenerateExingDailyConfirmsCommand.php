<?php

namespace App\Command;

use App\Service\DailyReportService;
use App\Service\MailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use setasign\Fpdi\TcpdfFpdi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GenerateExingDailyConfirmsCommand extends Command {
  private $em;
  private $mail;
  private $dailyReportService;


  public function __construct(EntityManagerInterface $em, MailService $mail, DailyReportService $dailyReportService) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
    $this->dailyReportService = $dailyReportService;

  }


  protected function configure() {
    $this
      ->setName('app:exing:confirm')
      ->setDescription('Svakog dana kreira dozvole za exing, plato, urban i salje na mail!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);


    $this->dailyReportService->generateAndSendExingReports();
//    $this->dailyReportService->generateAndSendUrbanReports();
    $this->dailyReportService->generatePlatoReports();

//    $client = $this->em->getRepository(Client::class)->find(5);
//
//    $exingPath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/exing';
//    $attachments = [];
//
//    $projects = $this->em->getRepository(Project::class)->findBy(['isSuspended' => false]);
//
//    $danas = new DateTimeImmutable();
//
//    $projectList = [];
//    $usersList = [];
//
//    foreach ($projects as $project) {
//      if ($project->getClient()->first()->getId() == $client->getId()) {
//          $users = $this->em->getRepository(Task::class)->getUsersByDateExing($danas, $project);
//          if (!empty($users['lista'])) {
//
//              $projectList[] = $project->getTitle();
//              $pdf = new TcpdfFpdi();
//
//    // Dodavanje nove stranice
//              $pdf->AddPage();
//
//    // Putanja do PDF šablona
//              $templatePath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/pdf_template.pdf';
//
//    // Učitajte šablon
//              $pageCount = $pdf->setSourceFile($templatePath); // Broj stranica u šablonu
//              $templateId = $pdf->importPage(1); // Importuj prvu stranicu iz šablona
//
//    // Koristite šablon na aktuelnoj stranici
//              $pdf->useTemplate($templateId);
//              $pdf->SetFont('dejavusans', '', 13);
//
//              $meseci = [
//                1 => 'januar', 2 => 'februar', 3 => 'mart', 4 => 'april',
//                5 => 'maj', 6 => 'jun', 7 => 'jul', 8 => 'avgust',
//                9 => 'septembar', 10 => 'oktobar', 11 => 'novembar', 12 => 'decembar'
//              ];
//
//              $datum = [
//                'dan' => (new \DateTime())->format('d.'),
//                'mesec' => $meseci[(int)(new \DateTime())->format('m')],
//                'godina' => (new \DateTime())->format('Y.'),
//              ];
//
//              // Datum
//              $pdf->SetXY(16, 88); // X, Y koordinata gde ide datum
//              $pdf->Write(0, ($datum['mesec']));
//
//              $pdf->SetXY(62, 88); // X, Y koordinata gde ide datum
//              $pdf->Write(0, ($datum['dan']));
//
//              $pdf->SetXY(128, 88); // X, Y koordinata gde ide datum
//              $pdf->Write(0, ($datum['godina']));
//
//              $pdf->SetXY(62, 47);
//              $pdf->Write(0, (mb_strtoupper($project->getTitle())));
//
//              // Firma
//              $pdf->SetXY(62, 60);
//              $pdf->Write(0, 'PARS DOO BEOGRAD');
//
//              // Matični broj
//              $pdf->SetXY(62, 70);
//              $pdf->Write(0, '21360031');
//
//              $startY = 115;
//              foreach ($users['lista'] as $index => $user) {
//                $usersList[] = $user;
//
//                $pdf->SetXY(33, $startY + ($index * 13));
//                $pdf->Write(0, $user->getIme() . ' ' . $user->getPrezime());
//
//                $pdf->SetXY(158, $startY + ($index * 13));
//                $pdf->Write(0, $user->getLk());
//              }
//
//
//              $pdfPath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/exing/dnevni_spisak_' . $project->getId() . '.pdf';
//
//    // Sačuvajte PDF na zadatoj putanji
//              $pdf->Output($pdfPath, 'F');
//    //      $pdf->Output('dnevni_spisak.pdf', 'I');
//            }
//          $sign = new SignEmail();
//          $sign->setProject($project->getId());
//          $sign->setUsers($users['listaSign']);
//          $this->em->getRepository(SignEmail::class)->save($sign);
//      }
//    }
//
//    if (is_dir($exingPath)) {
//      $files = glob($exingPath . '/*.pdf'); // Get all .xlsx files
//      foreach ($files as $file) {
//        $attachments[] = $file;
//      }
//      foreach ($usersList as $usr) {
//        $atch = $this->em->getRepository(Pdf::class)->findOneBy(['user' => $usr, 'title' => 'Dokumenti']);
//        if (!is_null($atch)) {
//          $attachments[] = $this->parameterBag->get('kernel.project_dir') . '/public' . $atch->getPath();
//        }
//      }
//    }
//
////    $this->mail->reportsDailyExing('stefanmaksimovic88@gmail.com', $client, $danas, $attachments, $projectList);
//      if (!empty($usersList)) {
//          $this->mail->reportsDailyExing('marceta.pars@gmail.com', $client, $danas, $attachments, $projectList);
//          $this->mail->reportsDailyExing('zoran.stankovic@exing.co.rs', $client, $danas, $attachments, $projectList);
//          $this->mail->reportsDailyExing('radisa.baltic@exing.co.rs', $client, $danas, $attachments, $projectList);
//          $this->mail->reportsDailyExing('petar.petrovic@exing.co.rs', $client, $danas, $attachments, $projectList);
//          $this->mail->reportsDailyExing('sanja.petronijevic@exing.co.rs', $client, $danas, $attachments, $projectList);
//      }
//
//    if (is_dir($exingPath)) {
//      $files = glob($exingPath . '/*'); // Dohvata sve fajlove u folderu
//
//      foreach ($files as $file) {
//        if (is_file($file)) {
//          unlink($file); // Briše fajl
//        }
//      }
//    }
//
//
//      $urbanPath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/urban';
//      $attachments = [];
//
//      $project = $this->em->getRepository(Project::class)->find(214);
//
//      $danas = new DateTimeImmutable();
//
//      $projectList = [];
//      $usersList = [];
//
//
//      $users = $this->em->getRepository(Task::class)->getUsersByDateExing($danas, $project);
//      if (!empty($users['lista'])) {
//
//          $projectList[] = $project->getTitle();
//          $pdf = new TcpdfFpdi();
//
//                  // Dodavanje nove stranice
//          $pdf->AddPage();
//
//                  // Putanja do PDF šablona
//          $templatePath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/pdf_template.pdf';
//
//                  // Učitajte šablon
//          $pageCount = $pdf->setSourceFile($templatePath); // Broj stranica u šablonu
//          $templateId = $pdf->importPage(1); // Importuj prvu stranicu iz šablona
//
//                  // Koristite šablon na aktuelnoj stranici
//          $pdf->useTemplate($templateId);
//          $pdf->SetFont('dejavusans', '', 13);
//
//          $meseci = [
//              1 => 'januar', 2 => 'februar', 3 => 'mart', 4 => 'april',
//              5 => 'maj', 6 => 'jun', 7 => 'jul', 8 => 'avgust',
//              9 => 'septembar', 10 => 'oktobar', 11 => 'novembar', 12 => 'decembar'
//          ];
//
//          $datum = [
//              'dan' => (new \DateTime())->format('d.'),
//              'mesec' => $meseci[(int)(new \DateTime())->format('m')],
//              'godina' => (new \DateTime())->format('Y.'),
//          ];
//
//                  // Datum
//          $pdf->SetXY(16, 88); // X, Y koordinata gde ide datum
//                  $pdf->Write(0, ($datum['mesec']));
//
//                  $pdf->SetXY(62, 88); // X, Y koordinata gde ide datum
//                  $pdf->Write(0, ($datum['dan']));
//
//                  $pdf->SetXY(128, 88); // X, Y koordinata gde ide datum
//                  $pdf->Write(0, ($datum['godina']));
//
//                  $pdf->SetXY(62, 47);
//                  $pdf->Write(0, (mb_strtoupper($project->getTitle())));
//
//                  // Firma
//                  $pdf->SetXY(62, 60);
//                  $pdf->Write(0, 'PARS DOO BEOGRAD');
//
//                  // Matični broj
//                  $pdf->SetXY(62, 70);
//                  $pdf->Write(0, '21360031');
//
//                  $startY = 115;
//                  foreach ($users['lista'] as $index => $user) {
//                      $usersList[] = $user;
//
//                      $pdf->SetXY(33, $startY + ($index * 13));
//                      $pdf->Write(0, $user->getIme() . ' ' . $user->getPrezime());
//
//                      $pdf->SetXY(158, $startY + ($index * 13));
//                      $pdf->Write(0, $user->getLk());
//                  }
//
//
//                  $pdfPath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/urban/dnevni_spisak_' . $project->getId() . '.pdf';
//
//                  // Sačuvajte PDF na zadatoj putanji
//                  $pdf->Output($pdfPath, 'F');
//                  //      $pdf->Output('dnevni_spisak.pdf', 'I');
//              }
//      $sign = new SignEmail();
//      $sign->setProject($project->getId());
//      $sign->setUsers($users['listaSign']);
//      $this->em->getRepository(SignEmail::class)->save($sign);
//
//
//
//      if (is_dir($urbanPath)) {
//          $files = glob($urbanPath . '/*.pdf'); // Get all .xlsx files
//          foreach ($files as $file) {
//              $attachments[] = $file;
//          }
//          foreach ($usersList as $usr) {
//              $atch = $this->em->getRepository(Pdf::class)->findOneBy(['user' => $usr, 'title' => 'Dokumenti']);
//              if (!is_null($atch)) {
//                  $attachments[] = $this->parameterBag->get('kernel.project_dir') . '/public' . $atch->getPath();
//              }
//          }
//      }
//
//      $this->mail->reportsDailyUrban('stefanmaksimovic88@gmail.com', $project, $danas, $attachments, $projectList);
//      if (!empty($usersList)) {
//          $this->mail->reportsDailyExing('marceta.pars@gmail.com', $client, $danas, $attachments, $projectList);
//      }
//
//      if (is_dir($urbanPath)) {
//          $files = glob($urbanPath . '/*'); // Dohvata sve fajlove u folderu
//
//          foreach ($files as $file) {
//              if (is_file($file)) {
//                  unlink($file); // Briše fajl
//              }
//          }
//      }

    $end = microtime(true);
    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}