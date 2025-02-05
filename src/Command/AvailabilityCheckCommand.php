<?php

namespace App\Command;

use App\Classes\Data\FastTaskData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\Availability;
use App\Entity\Company;
use App\Entity\FastTask;
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

class AvailabilityCheckCommand extends Command {
  private $em;
  private $mail;

  public function __construct(EntityManagerInterface $em, MailService $mail) {
    parent::__construct();
    $this->em = $em;
    $this->mail = $mail;
  }


  protected function configure() {
    $this
      ->setName('app:check:availability')
      ->setDescription('Proverava dostupnost za predhodni dan!')
      ->setHelp('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $start = microtime(true);

    $danas = new DateTimeImmutable();

    $companies = $this->em->getRepository(Company::class)->findBy(['isSuspended' => false]);

    foreach ($companies as $company) {
      if($company->getSettings()->isCalendar()) {
        $users = $this->em->getRepository(User::class)->getZaposleniCommand($company);
        foreach ($users as $user) {
          $dostupnost = $this->em->getRepository(Availability::class)->findOneBy(['datum' => $danas->setTime(0,0), 'User' => $user]);
          if (is_null($dostupnost)) {
            $this->em->getRepository(StopwatchTime::class)->addDostupnost($user);
          } else {
            $this->em->getRepository(StopwatchTime::class)->addCheckDostupnost($user, $dostupnost);
          }
        }
      }
    }


    $end = microtime(true);


    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";


    return 1;
  }
}