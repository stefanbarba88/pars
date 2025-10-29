<?php

namespace App\Command;


use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\Company;
use App\Entity\Project;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProjectCheckCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:check:project:verify')
      ->setDescription('Proverava status projekata za koje je potrebna dozvola!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $projects = $this->em->getRepository(Project::class)->findBy(['isSuspended' => false, 'isApproved' => true]);

    foreach ($projects as $project) {
      $project->setIsCreated(false);
      $this->em->getRepository(Project::class)->save($project);
    }

    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}