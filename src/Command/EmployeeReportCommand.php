<?php

namespace App\Command;

use App\Classes\Data\UserRolesData;
use App\Classes\Slugify;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
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

class EmployeeReportCommand extends Command {
  private $em;
  private $mail;
  private $parameterBag;
  private $knpSnappyPdf;
  private $twig;

  private $slugify;

  public function __construct(EntityManagerInterface $em, MailService $mail, ParameterBagInterface $parameterBag, Pdf $knpSnappyPdf, TwigEnvironment $twig, Slugify $slugify) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
    $this->parameterBag = $parameterBag;
    $this->knpSnappyPdf = $knpSnappyPdf;
    $this->slugify = $slugify;
    $this->twig = $twig; // Dodajemo Twig servis
  }


  protected function configure() {
    $this
      ->setName('app:employee:monthly')
      ->setDescription('Kreira mesecni report za Nemanju!')
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


      $projectDir = $this->parameterBag->get('kernel.project_dir');
      $reportsPath = $projectDir . '/public/assets/user/';

      $currentYear = date('Y');
      $currentMonth = date('m', strtotime('-1 month'));

      $reportMonthPath = "$reportsPath$currentYear/$currentMonth";

      if (!is_dir($reportMonthPath)) {
          mkdir($reportMonthPath, 0777, true);
      }

      $args['logo'] = 'assets/images/logo/logoXls.png';

      $datum = new DateTimeImmutable();

      $excelFilepath = $reportMonthPath . '/izvestaj_' . $currentMonth .'_'. $currentYear .'.xls';
      header('Content-Type: application/openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . time() . '_' . date('m') . '.xls"');

      $args['prviDan'] = $datum->modify('first day of last month')->setTime(0, 0);
      $args['poslednjiDan'] = $datum->modify('last day of last month')->setTime(23, 59, 59);

//      $args['prviDan'] = (new DateTimeImmutable('2025-05-01'))->setTime(0, 0);
//      $args['poslednjiDan'] = (new DateTimeImmutable('2025-05-31'))->setTime(23, 59);

      foreach ($users as $usr) {
          $args['report'][] = $this->em->getRepository(Overtime::class)->getOvertimeByUserMesecNemanja($usr, $args['prviDan'], $args['poslednjiDan']);
      }

      $args['neradni'] = $this->em->getRepository(Holiday::class)->neradniDaniNemanja($args['prviDan'], $args['poslednjiDan']);



      $args['novi'] = $this->em->getRepository(User::class)->getNoviZaposleni($args['prviDan'], $args['poslednjiDan']);



      $spreadsheet = new Spreadsheet();

      $sheet = $spreadsheet->getActiveSheet();

      $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
      $sheet->getPageSetup()->setFitToWidth(1);
      $sheet->getPageSetup()->setFitToHeight(0);
      $sheet->getPageMargins()->setTop(1);
      $sheet->getPageMargins()->setRight(0.75);
      $sheet->getPageMargins()->setLeft(0.75);
      $sheet->getPageMargins()->setBottom(1);

      $styleBorder = [
          'borders' => [
              'allBorders' => [
                  'borderStyle' => Border::BORDER_THIN,
              ],
          ],
      ];



              $spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
              $sheet->getColumnDimension('A')->setAutoSize(true);
              $sheet->getColumnDimension('B')->setAutoSize(true);
              $sheet->getColumnDimension('C')->setAutoSize(true);
              $sheet->getColumnDimension('D')->setAutoSize(true);
              $sheet->getColumnDimension('E')->setAutoSize(true);
              $sheet->getColumnDimension('F')->setAutoSize(true);
              $sheet->getColumnDimension('G')->setAutoSize(true);
              $sheet->getColumnDimension('H')->setAutoSize(true);
              $sheet->getColumnDimension('I')->setAutoSize(true);



              $sheet->mergeCells('A1:E1');
              $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
              $sheet->setCellValue('A1', 'PREKOVREMENI SATI ' . $args['prviDan']->format('m.Y'));
              $style = $sheet->getStyle('A1:E1');
              $font = $style->getFont();
              $font->setSize(18); // Postavite veličinu fonta na 14
              $font->setBold(true); // Postavite font kao boldiran


              $sheet->setCellValue('A2', 'Zaposleni');
              $sheet->setCellValue('B2', 'Sati');
              $sheet->setCellValue('C2', 'Dani');
              $sheet->setCellValue('D2', 'Ukupno sati');
              $sheet->setCellValue('E2', 'Ukupno dani');
              $sheet->setCellValue('H2', 'Napomena sati');
              $sheet->setCellValue('I2', 'Napomena dani');


              $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

              $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('B2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

              $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('C2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

              $sheet->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('D2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

              $sheet->getStyle('E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('E2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

              $sheet->getStyle('H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('H2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

              $sheet->getStyle('I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('I2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);



              $font = $sheet->getStyle('A')->getFont();
              $font->setSize(14); // Postavite veličinu fonta na 14
              $font = $sheet->getStyle('B')->getFont();
              $font->setSize(14); // Postavite veličinu fonta na 14
              $font = $sheet->getStyle('C')->getFont();
              $font->setSize(14); // Postavite veličinu fonta na 14
              $font = $sheet->getStyle('D')->getFont();
              $font->setSize(16); // Postavite veličinu fonta na 14
              $font = $sheet->getStyle('E')->getFont();
              $font->setSize(16); // Postavite veličinu fonta na 14
              $font = $sheet->getStyle('H')->getFont();
              $font->setSize(14); // Postavite veličinu fonta na 14
              $font = $sheet->getStyle('I')->getFont();
              $font->setSize(14); // Postavite veličinu fonta na 14

              $start = 3;

              foreach ($args['report'] as $item) {

                  if (!empty($item['overtime'])) {

                      $niz = implode(' + ', $item['overtime'][1]);

                      $sheet->setCellValue('B' . $start, $niz);

                      $sheet->getStyle('B' . $start)->getAlignment()->setWrapText(true);
                      $sheet->getStyle('B' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                      $sheet->getStyle('B' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                      $sheet->setCellValue('D' . $start, $item['overtime'][0]);

                      $sheet->getStyle('D' . $start)->getAlignment()->setWrapText(true);
                      $sheet->getStyle('D' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                      $sheet->getStyle('D' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                      $font = $sheet->getStyle('D')->getFont();
                      $font->setSize(16); // Postavite veličinu fonta na 14
                      $font->setBold(true); // Postavite font kao boldiran

                      $rezultat = '';
                      $ukupno = count($item['overtime'][2]);

                      foreach ($item['overtime'][2] as $i => $par) {
                          foreach ($par as $vreme => $naziv) {
                              $rezultat .= "$vreme - $naziv";
                              if ($i < $ukupno - 1) {
                                  $rezultat .= "\n";
                              }
                          }
                      }


                      $sheet->setCellValue('H' . $start, $rezultat);

                      $sheet->getStyle('H' . $start)->getAlignment()->setWrapText(true);
                      $sheet->getStyle('H' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                      $sheet->getStyle('H' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                  }
                  if ($item['days'][3] > 0) {

                      $broj = count($item['days'][0]);
                      $niz = implode(' + ', array_fill(0, $broj, '1'));


                      $sheet->setCellValue('E' . $start, $item['days'][3]);

                      $sheet->getStyle('E' . $start)->getAlignment()->setWrapText(true);
                      $sheet->getStyle('E' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                      $sheet->getStyle('E' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                      $font = $sheet->getStyle('E')->getFont();
                      $font->setSize(16); // Postavite veličinu fonta na 14
                      $font->setBold(true); // Postavite font kao boldiran


                      $sheet->setCellValue('C' . $start, $niz);

                      $sheet->getStyle('C' . $start)->getAlignment()->setWrapText(true);
                      $sheet->getStyle('C' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                      $sheet->getStyle('C' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                      $rezultat = implode("\n", $item['days'][0]);

                      $sheet->setCellValue('I' . $start, $rezultat);

                      $sheet->getStyle('I' . $start)->getAlignment()->setWrapText(true);
                      $sheet->getStyle('I' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                      $sheet->getStyle('I' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                  }

                  if (!empty($item['overtime']) || $item['days'][3] > 0) {
                      $sheet->setCellValue('A' . $start, $item['user']);
                      $sheet->getStyle('A' . $start)->getAlignment()->setWrapText(true);
                      $sheet->getStyle('A' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                      $sheet->getStyle('A' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                      $start++;
                  }
              }

      $sheet->getStyle("A2:E" . $start-1)->applyFromArray($styleBorder);

      $sheet->getStyle("H2:I" . $start-1)->applyFromArray($styleBorder);

              $start1 = $start + 4;


              if (!empty($args['neradni'])) {

                  $colIndex = 1;
                  foreach ($args['neradni'] as $key => $item) {

                      $colLetter = Coordinate::stringFromColumnIndex($colIndex);

                      $sheet->setCellValue($colLetter . $start1, $key);
                      $sheet->getStyle($colLetter . $start1)->getAlignment()->setWrapText(true);
                      $sheet->getStyle($colLetter . $start1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                      $sheet->getStyle($colLetter . $start1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                      $colIndex++;
                  }
                  $sheet->mergeCells('A' . $start1 - 1 . ':' . $colLetter . $start1 - 1);
                  $sheet->getStyle('A' . $start1 - 1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                  $sheet->getStyle('A' . $start1 - 1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                  $sheet->setCellValue('A' . $start1 - 1, 'RAD NA NERADNI DAN');
                  $style = $sheet->getStyle('A' . $start1 - 1 . ':' . $colLetter . $start1);
                  $font = $style->getFont();
                  $font->setSize(14); // Postavite veličinu fonta na 14
                  $font->setBold(true); // Postavite font kao boldiran

                  $sheet->getStyle('A' . $start1 - 1 . ':' . $colLetter . $start1)->applyFromArray($styleBorder);
                  $sheet->getStyle('A' . $start1 - 1 . ':' . $colLetter . $start1)->getFill()->setFillType(Fill::FILL_SOLID);
                  $sheet->getStyle('A' . $start1 - 1 . ':' . $colLetter . $start1)->getFill()->getStartColor()->setRGB('CCCCCC');


                  $colIndex = 1;
                  foreach ($args['neradni'] as $value) {
                      $start2 = $start1 + 1;
                      foreach ($value as $item) {

                          $colLetter = Coordinate::stringFromColumnIndex($colIndex);

                          $sheet->setCellValue($colLetter . $start2, $item);
                          $sheet->getStyle($colLetter . $start2)->getAlignment()->setWrapText(true);
                          $sheet->getStyle($colLetter . $start2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                          $sheet->getStyle($colLetter . $start2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                          $sheet->getStyle($colLetter . $start2)->applyFromArray($styleBorder);
                          $start2++;
                      }
                      $colIndex++;
                  }

                  $colIndex = $colIndex + 2;
                  $start3 = $start + 3;

                  if (!empty($args['novi'])) {

                      $colLetter = 'H';
                      $colLetter1 = 'I';

                      $sheet->mergeCells($colLetter . $start3 . ':' . $colLetter1 . $start3);
                      $sheet->getStyle($colLetter . $start3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                      $sheet->getStyle($colLetter . $start3)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                      $sheet->setCellValue($colLetter . $start3, 'ZAPOSLILI SE U ' . $args['prviDan']->format('m.Y'));
                      $style = $sheet->getStyle($colLetter . $start3);
                      $font = $style->getFont();
                      $font->setSize(14); // Postavite veličinu fonta na 14
                      $font->setBold(true); // Postavite font kao boldiran
                      $sheet->getStyle($colLetter . $start3 . ':' . $colLetter1 . $start3)->applyFromArray($styleBorder);
                      $sheet->getStyle($colLetter . $start3)->getFill()->setFillType(Fill::FILL_SOLID);
                      $sheet->getStyle($colLetter . $start3)->getFill()->getStartColor()->setRGB('CCCCCC');

                      $sheet->getStyle($colLetter . $start3 + 1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                      $sheet->getStyle($colLetter . $start3 + 1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                      $sheet->setCellValue($colLetter . $start3 + 1, 'Datum');
                      $style = $sheet->getStyle($colLetter . $start3 + 1);
                      $font = $style->getFont();
                      $font->setSize(14); // Postavite veličinu fonta na 14
                      $font->setBold(true); // Postavite font kao boldiran
                      $sheet->getStyle($colLetter . $start3 + 1)->applyFromArray($styleBorder);
                      $sheet->getStyle($colLetter . $start3 + 1)->getFill()->setFillType(Fill::FILL_SOLID);
                      $sheet->getStyle($colLetter . $start3 + 1)->getFill()->getStartColor()->setRGB('CCCCCC');

                      $sheet->getStyle($colLetter1 . $start3 + 1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                      $sheet->getStyle($colLetter1 . $start3 + 1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                      $sheet->setCellValue($colLetter1 . $start3 + 1, 'Zaposleni');
                      $style = $sheet->getStyle($colLetter1 . $start3 + 1);
                      $font = $style->getFont();
                      $font->setSize(14); // Postavite veličinu fonta na 14
                      $font->setBold(true); // Postavite font kao boldiran
                      $sheet->getStyle($colLetter1 . $start3 + 1)->applyFromArray($styleBorder);
                      $sheet->getStyle($colLetter1 . $start3 + 1)->getFill()->setFillType(Fill::FILL_SOLID);
                      $sheet->getStyle($colLetter1 . $start3 + 1)->getFill()->getStartColor()->setRGB('CCCCCC');


                      $start4 = $start3 + 2;

                      foreach ($args['novi'] as $user) {

                          $sheet->setCellValue($colLetter . $start4, $user->getCreated()->format('d.m.Y.'));
                          $sheet->getStyle($colLetter . $start4)->getAlignment()->setWrapText(true);
                          $sheet->getStyle($colLetter . $start4)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                          $sheet->getStyle($colLetter . $start4)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                          $sheet->getStyle($colLetter . $start4)->applyFromArray($styleBorder);

                          $sheet->setCellValue($colLetter1 . $start4, $user->getFullName());
                          $sheet->getStyle($colLetter1 . $start4)->getAlignment()->setWrapText(true);
                          $sheet->getStyle($colLetter1 . $start4)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                          $sheet->getStyle($colLetter1 . $start4)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                          $sheet->getStyle($colLetter1 . $start4)->applyFromArray($styleBorder);

                          $start4++;
                      }

                  }

              }

              $sheet->getStyle('A1:E2')->getFill()->setFillType(Fill::FILL_SOLID);
              $sheet->getStyle('A1:E2')->getFill()->getStartColor()->setRGB('CCCCCC');

      $sheet->getStyle('H2:I2')->getFill()->setFillType(Fill::FILL_SOLID);
      $sheet->getStyle('H2:I2')->getFill()->getStartColor()->setRGB('CCCCCC');

              // Postavite font za opseg od A1 do M2
              $style = $sheet->getStyle('A2:I2');
              $font = $style->getFont();
              $font->setSize(14); // Postavite veličinu fonta na 14
              $font->setBold(true); // Postavite font kao boldiran



      $sheet->setTitle("Izvestaj");

      // Create your Office 2007 Excel (XLSX Format)
      $writer = new Xls($spreadsheet);

      // In this case, we want to write the file in the public directory
//    $publicDirectory = $this->getParameter('kernel.project_dir') . '/var/excel';
//    // e.g /var/www/project/public/my_first_excel_symfony4.xlsx
//    $excelFilepath =  $publicDirectory . '/'.$user->getFullName() . '_'. $datum .'.xls';

      // Create the file
      try {
          $writer->save($excelFilepath);
      } catch (Exception $e) {
          dd( 'Caught exception: ',  $e->getMessage(), "\n");
      }




//        $html = $this->twig->render('report_employee/pdf.html.twig', $args);
//
//        $pdfContent = $this->knpSnappyPdf->getOutputFromHtml($html);
//        $ime = Slugify::slugify($usr->getFullName(), '_');
//        $fileName = $reportMonthPath . '/' . $usr->getId(). '_report_' . $ime . '.pdf';
//        file_put_contents($fileName, $pdfContent);
//
//
//        $attachments[] = $fileName;
//
//        $this->mail->reportsUserGenerator($usr->getEmail(), $usr, $args['prviDan'], $args['poslednjiDan'], $attachments);
////        $this->mail->reportsUserGenerator('stefanmaksimovic88@gmail.com', $usr, $args['prviDan'], $args['poslednjiDan'], $attachments);

//      }





    $end = microtime(true);
    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";
    return 1;
  }
}