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

class CountExingTasksCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:count:exing')
      ->setDescription('Svakog 19-og u mesecu broji koliko ima izlazaka za klijenta Exing!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $client = $this->em->getRepository(Client::class)->find(5);
    $category = $this->em->getRepository(Category::class)->find(5);
    $company = $this->em->getRepository(Company::class)->find(1);

    $prethodniMesecDatum = new DateTimeImmutable('last day of last month');
    $prethodniMesecDatum = $prethodniMesecDatum->setDate($prethodniMesecDatum->format('Y'), $prethodniMesecDatum->format('m'), 26);

    $datum = new DateTimeImmutable();

    $projects = $this->em->getRepository(Project::class)->countClientTasks($company, $client, $category, $prethodniMesecDatum, $datum);


    $this->mail->tasksByClient($projects, $client, $prethodniMesecDatum, $datum, 'vladapars@gmail.com');
    $this->mail->tasksByClient($projects, $client, $prethodniMesecDatum, $datum, 'nemanjapars@gmail.com');
    $this->mail->tasksByClient($projects, $client, $prethodniMesecDatum, $datum, 'marceta.pars@gmail.com');

    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}