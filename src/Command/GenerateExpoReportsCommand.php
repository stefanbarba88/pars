<?php

namespace App\Command;

use App\Classes\Data\FastTaskData;
use App\Entity\Activity;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Company;
use App\Entity\FastTask;
use App\Entity\Project;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\User;
use App\Service\MailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GenerateExpoReportsCommand extends Command {
  private $em;
  private $mail;
  private $parameterBag;

  public function __construct(EntityManagerInterface $em, MailService $mail, ParameterBagInterface $parameterBag) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
    $this->parameterBag = $parameterBag;
  }


  protected function configure() {
    $this
      ->setName('app:reports:expo')
      ->setDescription('Svakog ponedeljka kreira reporte i salje na mail!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    //expo projekti
    $expo = [166, 190];
    $client = null;
    $danas = new DateTimeImmutable();
    $datum = $danas->modify('-1 day');

// Proverava da li je danas ponedeljak
    if ($danas->format('N') == 5) {
      $prethodniMesecDatum = $danas->modify('last monday');

      $excelPath = $this->parameterBag->get('kernel.project_dir') . '/var/expo';
      $attachments = [];

      $projects = $this->em->getRepository(Project::class)->getReportsGeneratorExpo($expo, $prethodniMesecDatum, $datum, $excelPath, $danas);

      if (is_dir($excelPath)) {
        $files = glob($excelPath . '/*.xls'); // Get all .xlsx files
        foreach ($files as $file) {
          $attachments[] = $file;
        }
      }
  //    $this->mail->reportsGenerator('nemanjapars@gmail.com', $client, $prethodniMesecDatum, $datum, $attachments, $projects);
      $this->mail->reportsGeneratorExpo('stefanmaksimovic88@gmail.com', $client, $prethodniMesecDatum, $datum, $attachments, $projects);

      if (is_dir($excelPath)) {
        $files = glob($excelPath . '/*'); // Dohvata sve fajlove u folderu

        foreach ($files as $file) {
          if (is_file($file)) {
            unlink($file); // Briše fajl
          }
        }
      }

    }

    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}