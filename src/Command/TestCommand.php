<?php

namespace App\Command;

use App\Entity\Activity;
use App\Entity\Survey;
use App\Entity\User;
use App\Entity\Vote;
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
      ->setName('app:test')
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

      // Pronalazi sve aktivne korisnike kompanije 1 koji nisu suspendovani
      $users = $this->em->getRepository(User::class)->findBy([
        'isSuspended' => false,
        'company' => 1,
      ]);

      $nemaVote = [];

      // Proverava za svakog korisnika da li ima glasove
      foreach ($users as $user) {
        $votes = $this->em->getRepository(Vote::class)->findBy([
          'user' => $user,
        ]);

        if (empty($votes)) {
          $nemaVote[] = [
            'ime' => $user->getFullName(),
          ];
        }
      }

      // Pronalazi aktivnu anketu
      $survey = $this->em->getRepository(Survey::class)->findOneBy([
        'isActive' => true,
      ], [
        'id' => 'DESC',
      ]);

      // Pronalazi glasove vezane za anketu
      $votes = $survey->getVote()->toArray();

      $result = [
        'total_votes' => count($votes),
        'value1' => [
          '0' => ['count' => 0, 'users' => []],
          '1' => ['count' => 0, 'users' => []],
          '2' => ['count' => 0, 'users' => []],
        ],
        'value2' => [
          '0' => ['count' => 0, 'users' => []],
          '1' => ['count' => 0, 'users' => []],
        ],
      ];

      // Obrada glasova
      foreach ($votes as $vote) {
        $user = $vote->getUser();

        if ($vote->getValue1() !== null) {
          $value1 = $vote->getValue1();
          $result['value1'][$value1]['count']++;
          $result['value1'][$value1]['users'][] = $user->getFullName();
        }

        if ($vote->getValue2() !== null) {
          $value2 = $vote->getValue2();
          $result['value2'][$value2]['count']++;
          $result['value2'][$value2]['users'][] = $user->getFullName();
        }
      }

      dd ([
        'nemaVote' => $nemaVote,
        'result' => $result,
      ]);


    $end = microtime(true);
    echo "The code took " . date('i:s', $end - $start) . " minutes to complete. \n";
    return 1;
  }
}