<?php

namespace App\Command;

use App\Entity\Activity;
use App\Entity\User;
use App\Service\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends Command {
  private $em;

  public function __construct(EntityManagerInterface $em) {
    parent::__construct();
    $this->em = $em;
  }


  protected function configure() {
    $this
      ->setName('app:import:lager')
      ->setDescription('Import lager')
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

    $act = new Activity();

    $act->setIsSuspended(false);
    $act->setTitle('test');
    $this->em->getRepository(Activity::class)->save($act);
    $end = microtime(true);
    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";
    return 1;
  }
}