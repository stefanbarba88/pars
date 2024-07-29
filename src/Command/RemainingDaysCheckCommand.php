<?php

namespace App\Command;


use App\Classes\AppConfig;
use App\Entity\User;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RemainingDaysCheckCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:employee:check')
      ->setDescription('Proverava isticanje ugovora zaposlenog!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $admins = $this->em->getRepository(User::class)->findBy(['isSuspended' => false, 'company' => AppConfig::KADROVSKA]);

    $ugovori = $this->em->getRepository(User::class)->getUsersCheckEmail();

    if (!empty($ugovori['soon']) || !empty($ugovori['today']) || !empty($ugovori['expired'])) {

      foreach ($admins as $admin) {
        $this->mail->kadrovskaEmployeeCheck($admin, $ugovori);
      }
    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}