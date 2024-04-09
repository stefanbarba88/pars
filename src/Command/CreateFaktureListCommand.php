<?php

namespace App\Command;

use App\Classes\Data\FastTaskData;
use App\Entity\Activity;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Company;
use App\Entity\FastTask;
use App\Entity\Project;
use App\Entity\ProjectFaktura;
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

class CreateFaktureListCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:create:fakture')
      ->setDescription('Svakog 1-og u mesecu kreira listu projekata koji su imali zadatke')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);

    $pocetak = new DateTimeImmutable('first day of last month');

    foreach ($companies as $company) {
      $projects = $this->em->getRepository(Project::class)->getAllProjectsByNoTasks($company);

      foreach ($projects as $project) {
        $faktura = new ProjectFaktura();
        $faktura->setCompany($company);
        $faktura->setProject($project);
        $faktura->setStatus(0);
        $faktura->setNoTasks($project->getNoTasks());
        $faktura->setDatum($pocetak->setTime(0,0));
        $this->em->getRepository(ProjectFaktura::class)->save($faktura);
      }
    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}