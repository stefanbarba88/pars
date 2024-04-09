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

class FaktureTasksCommand extends Command {
  private $em;

  public function __construct(EntityManagerInterface $em) {
    parent::__construct();
    $this->em = $em;
  }


  protected function configure() {
    $this
      ->setName('app:fakture:arhiva')
      ->setDescription('Svakog 1-og u mesecu broji koliko ima zadataka po projektu!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);

    $pocetak = new DateTimeImmutable('first day of January 2024');
    $kraj = new DateTimeImmutable('last day of January 2024');

    foreach ($companies as $company) {
      $projects = $this->em->getRepository(Project::class)->findBy(['isSuspended' => false, 'company' => $company]);
      foreach ($projects as $project) {
        $brojZadataka = $this->em->getRepository(Task::class)->countTasksInMonth($project, $pocetak, $kraj);
        if ($brojZadataka > 0) {
          $faktura = new ProjectFaktura();
          $faktura->setCompany($company);
          $faktura->setProject($project);
          $faktura->setStatus(0);
          $faktura->setNoTasks($brojZadataka);
          $faktura->setDatum($pocetak->setTime(0, 0));
          $this->em->getRepository(ProjectFaktura::class)->save($faktura);
        }
      }
    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}