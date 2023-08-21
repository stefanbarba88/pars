<?php

namespace App\Repository;

use App\Classes\Data\FastTaskData;
use App\Entity\Activity;
use App\Entity\Car;
use App\Entity\FastTask;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\Tool;
use App\Entity\ToolReservation;
use App\Entity\User;
use App\Service\MailService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @extends ServiceEntityRepository<FastTask>
 *
 * @method FastTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method FastTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method FastTask[]    findAll()
 * @method FastTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FastTaskRepository extends ServiceEntityRepository {
  private $mail;
  public function __construct(ManagerRegistry $registry, MailService $mail) {
    parent::__construct($registry, FastTask::class);
    $this->mail = $mail;
  }

  public function findCarToReserve(User $user): ?Car {

    $sutra = new DateTimeImmutable('tomorrow');
    $danas = new DateTimeImmutable();

    $startDate = $danas->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $danas->format('Y-m-d 23:59:59'); // Kraj dana

    $danasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($danas);
    $sutrasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($sutra);


    $lista = [];
    foreach ($danasnjiTaskovi as $dnsTask) {
      if (!empty($dnsTask['driver']) && $dnsTask['driver'][0] == $user ){
        if ($dnsTask['status'] != 2) {
          $lista[] = [
            'datum' => $dnsTask['task']->getTime(),
            'car' => $dnsTask['task']->getCar(),
          ];
        }
      }
    }

    if (empty($lista)) {
      foreach ($sutrasnjiTaskovi as $dnsTask) {
        if (!empty($dnsTask['driver']) && $dnsTask['driver'][0] == $user ){
          if ($dnsTask['status'] != 2) {
            $lista[] = [
              'datum' => $dnsTask['task']->getTime(),
              'car' => $dnsTask['task']->getCar(),
            ];
          }
        }
      }
      $qb = $this->createQueryBuilder('f');
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->eq('f.status', ':status2'),
          $qb->expr()->eq('f.status', ':status3')
        ))
        ->setParameter('status2', FastTaskData::SAVED)
        ->setParameter('status3', FastTaskData::EDIT)
        ->setMaxResults(1); // Postavljamo da vrati samo jedan rezultat

      $query = $qb->getQuery();
      $fastTask = $query->getOneOrNullResult();


      if(!is_null($fastTask)) {
        if (!is_null($fastTask->getDriver1())) {
          if ($fastTask->getDriver1() == $user->getId()) {
            if (!is_null($fastTask->getTime1())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime1());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getCar1())) {
              $vozilo = $fastTask->getCar1();
            } else {
              $vozilo = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'car' => $vozilo,
            ];
          }
        }
        if (!is_null($fastTask->getDriver2())) {
          if ($fastTask->getDriver2() == $user->getId()) {
            if (!is_null($fastTask->getTime2())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime2());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getCar2())) {
              $vozilo = $fastTask->getCar2();
            } else {
              $vozilo = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'car' => $vozilo,
            ];
          }
        }
        if (!is_null($fastTask->getDriver3())) {
          if ($fastTask->getDriver3() == $user->getId()) {
            if (!is_null($fastTask->getTime3())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime3());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getCar3())) {
              $vozilo = $fastTask->getCar3();
            } else {
              $vozilo = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'car' => $vozilo,
            ];
          }
        }
        if (!is_null($fastTask->getDriver4())) {
          if ($fastTask->getDriver4() == $user->getId()) {
            if (!is_null($fastTask->getTime4())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime4());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getCar4())) {
              $vozilo = $fastTask->getCar4();
            } else {
              $vozilo = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'car' => $vozilo,
            ];
          }
        }
        if (!is_null($fastTask->getDriver5())) {
          if ($fastTask->getDriver5() == $user->getId()) {
            if (!is_null($fastTask->getTime5())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime5());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getCar5())) {
              $vozilo = $fastTask->getCar5();
            } else {
              $vozilo = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'car' => $vozilo,
            ];
          }
        }
        if (!is_null($fastTask->getDriver6())) {
          if ($fastTask->getDriver6() == $user->getId()) {
            if (!is_null($fastTask->getTime6())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime6());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getCar6())) {
              $vozilo = $fastTask->getCar6();
            } else {
              $vozilo = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'car' => $vozilo,
            ];
          }
        }
        if (!is_null($fastTask->getDriver7())) {
          if ($fastTask->getDriver7() == $user->getId()) {
            if (!is_null($fastTask->getTime7())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime7());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getCar7())) {
              $vozilo = $fastTask->getCar7();
            } else {
              $vozilo = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'car' => $vozilo,
            ];
          }
        }
        if (!is_null($fastTask->getDriver8())) {
          if ($fastTask->getDriver8() == $user->getId()) {
            if (!is_null($fastTask->getTime8())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime8());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getCar8())) {
              $vozilo = $fastTask->getCar8();
            } else {
              $vozilo = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'car' => $vozilo,
            ];
          }
        }
        if (!is_null($fastTask->getDriver9())) {
          if ($fastTask->getDriver9() == $user->getId()) {
            if (!is_null($fastTask->getTime9())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime9());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getCar9())) {
              $vozilo = $fastTask->getCar9();
            } else {
              $vozilo = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'car' => $vozilo,
            ];
          }
        }
        if (!is_null($fastTask->getDriver10())) {
          if ($fastTask->getDriver10() == $user->getId()) {
            if (!is_null($fastTask->getTime10())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime10());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getCar10())) {
              $vozilo = $fastTask->getCar10();
            } else {
              $vozilo = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'car' => $vozilo,
            ];
          }
        }

      }
    }

    usort($lista, function($a, $b) {
      return $a['datum'] <=> $b['datum'];
    });

    foreach ($lista as $list) {
      if (!is_null($list['car'])) {
        return $this->getEntityManager()->getRepository(Car::class)->find($list['car']);
      }
    }

    return null;

  }

  public function findToolsToReserve(User $user): array {

    $sutra = new DateTimeImmutable('tomorrow');
    $danas = new DateTimeImmutable();

    $startDate = $danas->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $danas->format('Y-m-d 23:59:59'); // Kraj dana

    $danasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($danas);
    $sutrasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($sutra);

    $lista = [];

    foreach ($danasnjiTaskovi as $dnsTask) {
      if ($dnsTask['status'] != 2) {
        if (!$dnsTask['task']->getOprema()->isEmpty()) {
          if ($dnsTask['task']->getPriorityUserLog() == $user->getId()) {
            foreach ($dnsTask['task']->getOprema() as $opr) {
              $lista[] = [
                'datum' => $dnsTask['task']->getTime(),
                'user' => $dnsTask['task']->getPriorityUserLog(),
                'tool' => $opr->getId(),
              ];
            }
          }
        }
      }
    }

    if (empty($lista)) {
      foreach ($sutrasnjiTaskovi as $dnsTask) {
        if ($dnsTask['status'] != 2) {
          if (!$dnsTask['task']->getOprema()->isEmpty()) {
            if ($dnsTask['task']->getPriorityUserLog() == $user->getId()) {
              foreach ($dnsTask['task']->getOprema() as $opr) {
                $lista[] = [
                  'datum' => $dnsTask['task']->getTime(),
                  'user' => $dnsTask['task']->getPriorityUserLog(),
                  'tool' => $opr->getId(),
                ];
              }
            }
          }
        }
      }

      $qb = $this->createQueryBuilder('f');
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->eq('f.status', ':status2'),
          $qb->expr()->eq('f.status', ':status3')
        ))
        ->setParameter('status2', FastTaskData::SAVED)
        ->setParameter('status3', FastTaskData::EDIT)
        ->setMaxResults(1); // Postavljamo da vrati samo jedan rezultat

      $query = $qb->getQuery();
      $fastTask = $query->getOneOrNullResult();


      if (!is_null($fastTask)) {
        if (!is_null($fastTask->getOprema1())) {
          if ($user->getId() == $fastTask->getGeo11()) {
            foreach ($fastTask->getOprema1() as $opr) {

              if (!is_null($fastTask->getTime1())) {
                $vreme = $fastTask->getDatum()->modify($fastTask->getTime1());
              } else {
                $vreme = $fastTask->getDatum();
              }

              $lista[] = [
                'datum' => $vreme,
                'user' => $fastTask->getGeo11(),
                'tool' => $opr,
              ];
            }
          }
        }
        if (!is_null($fastTask->getOprema2())) {
          if ($user->getId() == $fastTask->getGeo12()) {
            foreach ($fastTask->getOprema2() as $opr) {

              if (!is_null($fastTask->getTime2())) {
                $vreme = $fastTask->getDatum()->modify($fastTask->getTime2());
              } else {
                $vreme = $fastTask->getDatum();
              }

              $lista[] = [
                'datum' => $vreme,
                'user' => $fastTask->getGeo12(),
                'tool' => $opr,
              ];
            }
          }
        }
        if (!is_null($fastTask->getOprema3())) {
          if ($user->getId() == $fastTask->getGeo13()) {
            foreach ($fastTask->getOprema3() as $opr) {

              if (!is_null($fastTask->getTime3())) {
                $vreme = $fastTask->getDatum()->modify($fastTask->getTime3());
              } else {
                $vreme = $fastTask->getDatum();
              }

              $lista[] = [
                'datum' => $vreme,
                'user' => $fastTask->getGeo13(),
                'tool' => $opr,
              ];
            }
          }
        }
        if (!is_null($fastTask->getOprema4())) {
          if ($user->getId() == $fastTask->getGeo14()) {
            foreach ($fastTask->getOprema4() as $opr) {

              if (!is_null($fastTask->getTime4())) {
                $vreme = $fastTask->getDatum()->modify($fastTask->getTime4());
              } else {
                $vreme = $fastTask->getDatum();
              }

              $lista[] = [
                'datum' => $vreme,
                'user' => $fastTask->getGeo14(),
                'tool' => $opr,
              ];
            }
          }
        }
        if (!is_null($fastTask->getOprema5())) {
          if ($user->getId() == $fastTask->getGeo15()) {
            foreach ($fastTask->getOprema5() as $opr) {

              if (!is_null($fastTask->getTime5())) {
                $vreme = $fastTask->getDatum()->modify($fastTask->getTime5());
              } else {
                $vreme = $fastTask->getDatum();
              }

              $lista[] = [
                'datum' => $vreme,
                'user' => $fastTask->getGeo15(),
                'tool' => $opr,
              ];
            }
          }
        }
        if (!is_null($fastTask->getOprema6())) {
          if ($user->getId() == $fastTask->getGeo16()) {
            foreach ($fastTask->getOprema6() as $opr) {

              if (!is_null($fastTask->getTime6())) {
                $vreme = $fastTask->getDatum()->modify($fastTask->getTime6());
              } else {
                $vreme = $fastTask->getDatum();
              }

              $lista[] = [
                'datum' => $vreme,
                'user' => $fastTask->getGeo16(),
                'tool' => $opr,
              ];
            }
          }
        }
        if (!is_null($fastTask->getOprema7())) {
          if ($user->getId() == $fastTask->getGeo17()) {
            foreach ($fastTask->getOprema7() as $opr) {

              if (!is_null($fastTask->getTime7())) {
                $vreme = $fastTask->getDatum()->modify($fastTask->getTime7());
              } else {
                $vreme = $fastTask->getDatum();
              }

              $lista[] = [
                'datum' => $vreme,
                'user' => $fastTask->getGeo17(),
                'tool' => $opr,
              ];
            }
          }
        }
        if (!is_null($fastTask->getOprema8())) {
          if ($user->getId() == $fastTask->getGeo18()) {
            foreach ($fastTask->getOprema8() as $opr) {

              if (!is_null($fastTask->getTime8())) {
                $vreme = $fastTask->getDatum()->modify($fastTask->getTime8());
              } else {
                $vreme = $fastTask->getDatum();
              }

              $lista[] = [
                'datum' => $vreme,
                'user' => $fastTask->getGeo18(),
                'tool' => $opr,
              ];
            }
          }
        }
        if (!is_null($fastTask->getOprema9())) {
          if ($user->getId() == $fastTask->getGeo19()) {
            foreach ($fastTask->getOprema9() as $opr) {

              if (!is_null($fastTask->getTime9())) {
                $vreme = $fastTask->getDatum()->modify($fastTask->getTime9());
              } else {
                $vreme = $fastTask->getDatum();
              }

              $lista[] = [
                'datum' => $vreme,
                'user' => $fastTask->getGeo19(),
                'tool' => $opr,
              ];
            }
          }
        }
        if (!is_null($fastTask->getOprema10())) {
          if ($user->getId() == $fastTask->getGeo110()) {
            foreach ($fastTask->getOprema10() as $opr) {

              if (!is_null($fastTask->getTime10())) {
                $vreme = $fastTask->getDatum()->modify($fastTask->getTime10());
              } else {
                $vreme = $fastTask->getDatum();
              }

              $lista[] = [
                'datum' => $vreme,
                'user' => $fastTask->getGeo110(),
                'tool' => $opr,
              ];
            }
          }
        }
      }
    }

    usort($lista, function ($a, $b) {
      return $a['datum'] <=> $b['datum'];
    });


    $noviNiz = [];

    foreach ($lista as $element) {
      $datum = $element['datum']->getTimestamp();
      if (!isset($noviNiz[$datum])) {
        $noviNiz[$datum] = [];
      }
      $noviNiz[$datum][] = [
        "user" => $element['user'],
        "tool" => $element['tool'],
      ];
    }

    $listaOpreme = [];
    if (!empty($noviNiz)) {
      foreach (reset($noviNiz) as $list) {
        $tool = $this->getEntityManager()->getRepository(Tool::class)->find($list['tool']);
        $listaOpreme[] = [
          'tool' => $tool,
          'lastReservation' => $this->getEntityManager()->getRepository(ToolReservation::class)->findOneBy(['tool' => $tool], ['id' => 'desc'])
        ];
      }
    }
    return $listaOpreme;
  }

  public function whereCarShouldGo(Car $car): ?User {

    $sutra = new DateTimeImmutable('tomorrow');
    $danas = new DateTimeImmutable();

    $startDate = $danas->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $danas->format('Y-m-d 23:59:59'); // Kraj dana

    $danasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($danas);
    $sutrasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($sutra);

    $lista = [];
    foreach ($danasnjiTaskovi as $dnsTask) {
      if (!empty($dnsTask['car']) && $dnsTask['car'][0] == $car ){
        if ($dnsTask['status'] != 2) {
          $lista[] = [
            'datum' => $dnsTask['task']->getTime(),
            'driver' => $dnsTask['task']->getDriver(),
          ];
        }
      }
    }

    if (empty($lista)) {
      foreach ($sutrasnjiTaskovi as $dnsTask) {
        if (!empty($dnsTask['car']) && $dnsTask['car'][0] == $car ){
          if ($dnsTask['status'] != 2) {
            $lista[] = [
              'datum' => $dnsTask['task']->getTime(),
              'driver' => $dnsTask['task']->getDriver(),
            ];
          }
        }
      }
      $qb = $this->createQueryBuilder('f');
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->eq('f.status', ':status2'),
          $qb->expr()->eq('f.status', ':status3')
        ))
        ->setParameter('status2', FastTaskData::SAVED)
        ->setParameter('status3', FastTaskData::EDIT)
        ->setMaxResults(1); // Postavljamo da vrati samo jedan rezultat

      $query = $qb->getQuery();
      $fastTask = $query->getOneOrNullResult();


      if(!is_null($fastTask)) {
        if (!is_null($fastTask->getCar1())) {
          if ($fastTask->getCar1() == $car->getId()) {
            if (!is_null($fastTask->getTime1())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime1());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getDriver1())) {
              $vozac = $fastTask->getDriver1();
            } else {
              $vozac = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'driver' => $vozac,
            ];
          }
        }
        if (!is_null($fastTask->getCar2())) {
          if ($fastTask->getCar2() == $car->getId()) {
            if (!is_null($fastTask->getTime2())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime2());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getDriver2())) {
              $vozac = $fastTask->getDriver2();
            } else {
              $vozac = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'driver' => $vozac,
            ];
          }
        }
        if (!is_null($fastTask->getCar3())) {
          if ($fastTask->getCar3() == $car->getId()) {
            if (!is_null($fastTask->getTime3())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime3());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getDriver3())) {
              $vozac = $fastTask->getDriver3();
            } else {
              $vozac = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'driver' => $vozac,
            ];
          }
        }
        if (!is_null($fastTask->getCar4())) {
          if ($fastTask->getCar4() == $car->getId()) {
            if (!is_null($fastTask->getTime4())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime4());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getDriver4())) {
              $vozac = $fastTask->getDriver4();
            } else {
              $vozac = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'driver' => $vozac,
            ];
          }
        }
        if (!is_null($fastTask->getCar5())) {
          if ($fastTask->getCar5() == $car->getId()) {
            if (!is_null($fastTask->getTime5())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime5());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getDriver5())) {
              $vozac = $fastTask->getDriver5();
            } else {
              $vozac = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'driver' => $vozac,
            ];
          }
        }
        if (!is_null($fastTask->getCar6())) {
          if ($fastTask->getCar6() == $car->getId()) {
            if (!is_null($fastTask->getTime6())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime6());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getDriver6())) {
              $vozac = $fastTask->getDriver6();
            } else {
              $vozac = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'driver' => $vozac,
            ];
          }
        }
        if (!is_null($fastTask->getCar7())) {
          if ($fastTask->getCar7() == $car->getId()) {
            if (!is_null($fastTask->getTime7())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime7());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getDriver7())) {
              $vozac = $fastTask->getDriver7();
            } else {
              $vozac = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'driver' => $vozac,
            ];
          }
        }
        if (!is_null($fastTask->getCar8())) {
          if ($fastTask->getCar8() == $car->getId()) {
            if (!is_null($fastTask->getTime8())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime8());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getDriver8())) {
              $vozac = $fastTask->getDriver8();
            } else {
              $vozac = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'driver' => $vozac,
            ];
          }
        }
        if (!is_null($fastTask->getCar9())) {
          if ($fastTask->getCar9() == $car->getId()) {
            if (!is_null($fastTask->getTime9())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime9());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getDriver9())) {
              $vozac = $fastTask->getDriver9();
            } else {
              $vozac = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'driver' => $vozac,
            ];
          }
        }
        if (!is_null($fastTask->getCar10())) {
          if ($fastTask->getCar10() == $car->getId()) {
            if (!is_null($fastTask->getTime10())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime10());
            } else {
              $vreme = $fastTask->getDatum();
            }
            if (!is_null($fastTask->getDriver10())) {
              $vozac = $fastTask->getDriver10();
            } else {
              $vozac = null;
            }
            $lista[] = [
              'datum' => $vreme,
              'driver' => $vozac,
            ];
          }
        }
      }
    }

    usort($lista, function($a, $b) {
      return $a['datum'] <=> $b['datum'];
    });

    foreach ($lista as $list) {
      if (!is_null($list['driver'])) {
        return $this->getEntityManager()->getRepository(User::class)->find($list['driver']);
      }
    }

    return null;

  }
  public function whereToolShouldGo(Tool $tool): ?User {

    $sutra = new DateTimeImmutable('tomorrow');
    $danas = new DateTimeImmutable();

    $startDate = $danas->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $danas->format('Y-m-d 23:59:59'); // Kraj dana

    $danasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($danas);
    $sutrasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($sutra);

    $lista = [];

    foreach ($danasnjiTaskovi as $dnsTask) {
      if ($dnsTask['status'] != 2) {
        if (!$dnsTask['task']->getOprema()->isEmpty()) {
          foreach ($dnsTask['task']->getOprema() as $opr) {
            if ($opr == $tool) {
              $lista[] = [
                'datum' => $dnsTask['task']->getTime(),
                'user' => $dnsTask['task']->getPriorityUserLog(),
                'tool' => $tool->getId(),
              ];
            }
          }
        }
      }
    }

    if (empty($lista)) {
      foreach ($sutrasnjiTaskovi as $dnsTask) {
        if ($dnsTask['status'] != 2) {
          if (!$dnsTask['task']->getOprema()->isEmpty()) {
            foreach ($dnsTask['task']->getOprema() as $opr) {
              if ($opr == $tool) {
                $lista[] = [
                  'datum' => $dnsTask['task']->getTime(),
                  'user' => $dnsTask['task']->getPriorityUserLog(),
                  'tool' => $tool->getId(),
                ];
              }
            }
          }
        }
      }

      $qb = $this->createQueryBuilder('f');
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->eq('f.status', ':status2'),
          $qb->expr()->eq('f.status', ':status3')
        ))
        ->setParameter('status2', FastTaskData::SAVED)
        ->setParameter('status3', FastTaskData::EDIT)
        ->setMaxResults(1); // Postavljamo da vrati samo jedan rezultat

      $query = $qb->getQuery();
      $fastTask = $query->getOneOrNullResult();


      if (!is_null($fastTask)) {
        if (!is_null($fastTask->getOprema1())) {

          if (in_array($tool->getId(),$fastTask->getOprema1() )) {
            if (!is_null($fastTask->getTime1())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime1());
            } else {
              $vreme = $fastTask->getDatum();
            }

            $lista[] = [
              'datum' => $vreme,
              'user' => $fastTask->getGeo11(),
              'tool' => $tool->getId(),

            ];
          }
        }
        if (!is_null($fastTask->getOprema2())) {

          if (in_array($tool->getId(),$fastTask->getOprema2() )) {
            if (!is_null($fastTask->getTime2())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime2());
            } else {
              $vreme = $fastTask->getDatum();
            }

            $lista[] = [
              'datum' => $vreme,
              'user' => $fastTask->getGeo12(),
              'tool' => $tool->getId(),

            ];
          }
        }
        if (!is_null($fastTask->getOprema3())) {

          if (in_array($tool->getId(),$fastTask->getOprema3() )) {
            if (!is_null($fastTask->getTime3())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime3());
            } else {
              $vreme = $fastTask->getDatum();
            }

            $lista[] = [
              'datum' => $vreme,
              'user' => $fastTask->getGeo13(),
              'tool' => $tool->getId(),

            ];
          }
        }
        if (!is_null($fastTask->getOprema4())) {

          if (in_array($tool->getId(),$fastTask->getOprema4() )) {
            if (!is_null($fastTask->getTime4())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime4());
            } else {
              $vreme = $fastTask->getDatum();
            }

            $lista[] = [
              'datum' => $vreme,
              'user' => $fastTask->getGeo14(),
              'tool' => $tool->getId(),

            ];
          }
        }
        if (!is_null($fastTask->getOprema5())) {

          if (in_array($tool->getId(),$fastTask->getOprema5() )) {
            if (!is_null($fastTask->getTime5())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime5());
            } else {
              $vreme = $fastTask->getDatum();
            }

            $lista[] = [
              'datum' => $vreme,
              'user' => $fastTask->getGeo15(),
              'tool' => $tool->getId(),

            ];
          }
        }
        if (!is_null($fastTask->getOprema6())) {

          if (in_array($tool->getId(),$fastTask->getOprema6() )) {
            if (!is_null($fastTask->getTime6())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime6());
            } else {
              $vreme = $fastTask->getDatum();
            }

            $lista[] = [
              'datum' => $vreme,
              'user' => $fastTask->getGeo16(),
              'tool' => $tool->getId(),

            ];
          }
        }
        if (!is_null($fastTask->getOprema7())) {

          if (in_array($tool->getId(),$fastTask->getOprema7() )) {
            if (!is_null($fastTask->getTime7())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime7());
            } else {
              $vreme = $fastTask->getDatum();
            }

            $lista[] = [
              'datum' => $vreme,
              'user' => $fastTask->getGeo17(),
              'tool' => $tool->getId(),

            ];
          }
        }
        if (!is_null($fastTask->getOprema8())) {

          if (in_array($tool->getId(),$fastTask->getOprema8() )) {
            if (!is_null($fastTask->getTime8())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime8());
            } else {
              $vreme = $fastTask->getDatum();
            }

            $lista[] = [
              'datum' => $vreme,
              'user' => $fastTask->getGeo18(),
              'tool' => $tool->getId(),

            ];
          }
        }
        if (!is_null($fastTask->getOprema9())) {

          if (in_array($tool->getId(),$fastTask->getOprema9() )) {
            if (!is_null($fastTask->getTime9())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime9());
            } else {
              $vreme = $fastTask->getDatum();
            }

            $lista[] = [
              'datum' => $vreme,
              'user' => $fastTask->getGeo19(),
              'tool' => $tool->getId(),

            ];
          }
        }
        if (!is_null($fastTask->getOprema10())) {

          if (in_array($tool->getId(),$fastTask->getOprema10() )) {
            if (!is_null($fastTask->getTime10())) {
              $vreme = $fastTask->getDatum()->modify($fastTask->getTime10());
            } else {
              $vreme = $fastTask->getDatum();
            }

            $lista[] = [
              'datum' => $vreme,
              'user' => $fastTask->getGeo110(),
              'tool' => $tool->getId(),

            ];
          }
        }
      }
    }

    usort($lista, function ($a, $b) {
      return $a['datum'] <=> $b['datum'];
    });

    foreach ($lista as $list) {
      if (!is_null($list['user'])) {
        return $this->getEntityManager()->getRepository(User::class)->find($list['user']);
      }
    }

    return null;
  }

  public function getTimetableByFastTasks(FastTask $task): array {

    $tasks = [];


    $activity1 = [];
    if(!empty($task->getActivity1())) {
      foreach ($task->getActivity1() as $act) {
        $activity1[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
      }
    }

    $activity2 = [];
    if(!empty($task->getActivity2())) {
      foreach ($task->getActivity2() as $act) {
        $activity2[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
      }
    }

    $activity3 = [];
    if(!empty($task->getActivity3())) {
      foreach ($task->getActivity3() as $act) {
        $activity3[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
      }
    }

    $activity4 = [];
    if(!empty($task->getActivity4())) {
      foreach ($task->getActivity4() as $act) {
        $activity4[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
      }
    }

    $activity5 = [];
    if(!empty($task->getActivity5())) {
      foreach ($task->getActivity5() as $act) {
        $activity5[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
      }
    }

    $activity6 = [];
    if(!empty($task->getActivity6())) {
      foreach ($task->getActivity6() as $act) {
        $activity6[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
      }
    }

    $activity7 = [];
    if(!empty($task->getActivity7())) {
      foreach ($task->getActivity7() as $act) {
        $activity7[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
      }
    }

    $activity8 = [];
    if(!empty($task->getActivity8())) {
      foreach ($task->getActivity8() as $act) {
        $activity8[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
      }
    }

    $activity9 = [];
    if(!empty($task->getActivity9())) {
      foreach ($task->getActivity9() as $act) {
        $activity9[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
      }
    }

    $activity10 = [];
    if(!empty($task->getActivity10())) {
      foreach ($task->getActivity10() as $act) {
        $activity10[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
      }
    }

    $oprema1 = [];
    if(!empty($task->getOprema1())) {
      foreach ($task->getOprema1() as $opr) {
        $oprema1[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
      }
    }

    $oprema2 = [];
    if(!empty($task->getOprema2())) {
      foreach ($task->getOprema2() as $opr) {
        $oprema2[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
      }
    }
    $oprema3 = [];
    if(!empty($task->getOprema3())) {
      foreach ($task->getOprema3() as $opr) {
        $oprema3[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
      }
    }
    $oprema4 = [];
    if(!empty($task->getOprema4())) {
      foreach ($task->getOprema4() as $opr) {
        $oprema4[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
      }
    }
    $oprema5 = [];
    if(!empty($task->getOprema5())) {
      foreach ($task->getOprema5() as $opr) {
        $oprema5[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
      }
    }
    $oprema6 = [];
    if(!empty($task->getOprema6())) {
      foreach ($task->getOprema6() as $opr) {
        $oprema6[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
      }
    }
    $oprema7 = [];
    if(!empty($task->getOprema7())) {
      foreach ($task->getOprema7() as $opr) {
        $oprema7[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
      }
    }
    $oprema8 = [];
    if(!empty($task->getOprema8())) {
      foreach ($task->getOprema8() as $opr) {
        $oprema8[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
      }
    }
    $oprema9 = [];
    if(!empty($task->getOprema9())) {
      foreach ($task->getOprema9() as $opr) {
        $oprema9[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
      }
    }
    $oprema10 = [];
    if(!empty($task->getOprema10())) {
      foreach ($task->getOprema10() as $opr) {
        $oprema10[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
      }
    }


    if (!is_null($task->getCar1())) {
      $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar1()]);
    } else {
      $car1 = null;
    }
    if (!is_null($task->getCar2())) {
      $car2 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar2()]);
    } else {
      $car2 = null;
    }
    if (!is_null($task->getCar3())) {
      $car3 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar3()]);
    } else {
      $car3 = null;
    }
    if (!is_null($task->getCar4())) {
      $car4 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar4()]);
    } else {
      $car4 = null;
    }
    if (!is_null($task->getCar5())) {
      $car5 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar5()]);
    } else {
      $car5 = null;
    }
    if (!is_null($task->getCar6())) {
      $car6 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar6()]);
    } else {
      $car6 = null;
    }
    if (!is_null($task->getCar7())) {
      $car7 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar7()]);
    } else {
      $car7 = null;
    }
    if (!is_null($task->getCar8())) {
      $car8 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar8()]);
    } else {
      $car8 = null;
    }
    if (!is_null($task->getCar9())) {
      $car9 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar9()]);
    } else {
      $car9 = null;
    }
    if (!is_null($task->getCar10())) {
      $car10 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar10()]);
    } else {
      $car10 = null;
    }

    $tasks = [
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject1()]),
        'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo11()]),
        'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo21()]),
        'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo31()]),
        'aktivnosti' => $activity1,
        'oprema' => $oprema1,
        'napomena' => $task->getDescription1(),
        'vozilo' => $car1,
        'vreme' => $task->getTime1(),
        'status' => $task->getStatus1(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject2()]),
        'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo12()]),
        'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo22()]),
        'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo32()]),
        'aktivnosti' => $activity2,
        'oprema' => $oprema2,
        'napomena' => $task->getDescription2(),
        'vozilo' => $car2,
        'vreme' => $task->getTime2(),
        'status' => $task->getStatus2(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject3()]),
        'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo13()]),
        'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo23()]),
        'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo33()]),
        'aktivnosti' => $activity3,
        'oprema' => $oprema3,
        'napomena' => $task->getDescription3(),
        'vozilo' => $car3,
        'vreme' => $task->getTime3(),
        'status' => $task->getStatus3(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject4()]),
        'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo14()]),
        'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo24()]),
        'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo34()]),
        'aktivnosti' => $activity4,
        'oprema' => $oprema4,
        'napomena' => $task->getDescription4(),
        'vozilo' => $car4,
        'vreme' => $task->getTime4(),
        'status' => $task->getStatus4(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject5()]),
        'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo15()]),
        'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo25()]),
        'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo35()]),
        'aktivnosti' => $activity5,
        'oprema' => $oprema5,
        'napomena' => $task->getDescription5(),
        'vozilo' => $car5,
        'vreme' => $task->getTime5(),
        'status' => $task->getStatus5(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject6()]),
        'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo16()]),
        'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo26()]),
        'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo36()]),
        'aktivnosti' => $activity6,
        'oprema' => $oprema6,
        'napomena' => $task->getDescription6(),
        'vozilo' => $car6,
        'vreme' => $task->getTime6(),
        'status' => $task->getStatus6(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject7()]),
        'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo17()]),
        'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo27()]),
        'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo37()]),
        'aktivnosti' => $activity7,
        'oprema' => $oprema7,
        'napomena' => $task->getDescription7(),
        'vozilo' => $car7,
        'vreme' => $task->getTime7(),
        'status' => $task->getStatus7(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject8()]),
        'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo18()]),
        'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo28()]),
        'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo38()]),
        'aktivnosti' => $activity8,
        'oprema' => $oprema8,
        'napomena' => $task->getDescription8(),
        'vozilo' => $car8,
        'vreme' => $task->getTime8(),
        'status' => $task->getStatus8(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject9()]),
        'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo19()]),
        'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo29()]),
        'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo39()]),
        'aktivnosti' => $activity9,
        'oprema' => $oprema9,
        'napomena' => $task->getDescription9(),
        'vozilo' => $car9,
        'vreme' => $task->getTime9(),
        'status' => $task->getStatus9(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject10()]),
        'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo110()]),
        'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo210()]),
        'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo310()]),
        'aktivnosti' => $activity10,
        'oprema' => $oprema10,
        'napomena' => $task->getDescription10(),
        'vozilo' => $car10,
        'vreme' => $task->getTime10(),
        'status' => $task->getStatus10(),
      ]
    ];
//    dd($tasks);
//    usort($tasks, function($a, $b) {
//      return $a['vreme'] <=> $b['vreme'];
//    });

    return $tasks;
  }

  public function getUsersForEmail(FastTask $task, int $status): array {

    $users = [];
    if ($status == FastTaskData::EDIT) {
      if ($task->getStatus1() == FastTaskData::EDIT) {
        $users[] = $task->getGeo11();
        $users[] = $task->getGeo21();
        $users[] = $task->getGeo31();
      }

      if ($task->getStatus2() == FastTaskData::EDIT) {
        $users[] = $task->getGeo12();
        $users[] = $task->getGeo22();
        $users[] = $task->getGeo32();
      }

      if ($task->getStatus3() == FastTaskData::EDIT) {
        $users[] = $task->getGeo13();
        $users[] = $task->getGeo23();
        $users[] = $task->getGeo33();
      }

      if ($task->getStatus4() == FastTaskData::EDIT) {
        $users[] = $task->getGeo14();
        $users[] = $task->getGeo24();
        $users[] = $task->getGeo34();
      }

      if ($task->getStatus5() == FastTaskData::EDIT) {
        $users[] = $task->getGeo15();
        $users[] = $task->getGeo25();
        $users[] = $task->getGeo35();
      }

      if ($task->getStatus6() == FastTaskData::EDIT) {
        $users[] = $task->getGeo16();
        $users[] = $task->getGeo26();
        $users[] = $task->getGeo36();
      }

      if ($task->getStatus7() == FastTaskData::EDIT) {
        $users[] = $task->getGeo17();
        $users[] = $task->getGeo27();
        $users[] = $task->getGeo37();
      }

      if ($task->getStatus8() == FastTaskData::EDIT) {
        $users[] = $task->getGeo18();
        $users[] = $task->getGeo28();
        $users[] = $task->getGeo38();
      }

      if ($task->getStatus9() == FastTaskData::EDIT) {
        $users[] = $task->getGeo19();
        $users[] = $task->getGeo29();
        $users[] = $task->getGeo39();
      }

      if ($task->getStatus10() == FastTaskData::EDIT) {
        $users[] = $task->getGeo110();
        $users[] = $task->getGeo210();
        $users[] = $task->getGeo310();
      }


    } else {
      $users[] = $task->getGeo11();
      $users[] = $task->getGeo21();
      $users[] = $task->getGeo31();
      $users[] = $task->getGeo12();
      $users[] = $task->getGeo22();
      $users[] = $task->getGeo32();
      $users[] = $task->getGeo13();
      $users[] = $task->getGeo23();
      $users[] = $task->getGeo33();
      $users[] = $task->getGeo14();
      $users[] = $task->getGeo24();
      $users[] = $task->getGeo34();
      $users[] = $task->getGeo15();
      $users[] = $task->getGeo25();
      $users[] = $task->getGeo35();
      $users[] = $task->getGeo16();
      $users[] = $task->getGeo26();
      $users[] = $task->getGeo36();
      $users[] = $task->getGeo17();
      $users[] = $task->getGeo27();
      $users[] = $task->getGeo37();
      $users[] = $task->getGeo18();
      $users[] = $task->getGeo28();
      $users[] = $task->getGeo38();
      $users[] = $task->getGeo19();
      $users[] = $task->getGeo29();
      $users[] = $task->getGeo39();
      $users[] = $task->getGeo110();
      $users[] = $task->getGeo210();
      $users[] = $task->getGeo310();
    }
    $users = array_filter(array_unique($users));

    $usersList = [];
    if (!empty($users)) {
      foreach ($users as $usr) {
        $usersList[] = $this->getEntityManager()->getRepository(User::class)->find($usr);
      }
    }

    return $usersList;
  }

  public function getTimeTable(DateTimeImmutable $date): array {

//    $today = new DateTimeImmutable(); // Trenutni datum i vrijeme
    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('f');
    $qb
      ->where($qb->expr()->between('f.datum', ':start', ':end'))
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate);

    $query = $qb->getQuery();
    $fastTasks = $query->getResult();
    $tasks = [];
    if (!empty ($fastTasks)) {
      foreach ($fastTasks as $task) {

        $activity1 = [];
        if(!empty($task->getActivity1())) {
          foreach ($task->getActivity1() as $act) {
            $activity1[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
          }
        }

        $activity2 = [];
        if(!empty($task->getActivity2())) {
          foreach ($task->getActivity2() as $act) {
            $activity2[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
          }
        }

        $activity3 = [];
        if(!empty($task->getActivity3())) {
          foreach ($task->getActivity3() as $act) {
            $activity3[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
          }
        }

        $activity4 = [];
        if(!empty($task->getActivity4())) {
          foreach ($task->getActivity4() as $act) {
            $activity4[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
          }
        }

        $activity5 = [];
        if(!empty($task->getActivity5())) {
          foreach ($task->getActivity5() as $act) {
            $activity5[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
          }
        }

        $activity6 = [];
        if(!empty($task->getActivity6())) {
          foreach ($task->getActivity6() as $act) {
            $activity6[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
          }
        }

        $activity7 = [];
        if(!empty($task->getActivity7())) {
          foreach ($task->getActivity7() as $act) {
            $activity7[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
          }
        }

        $activity8 = [];
        if(!empty($task->getActivity8())) {
          foreach ($task->getActivity8() as $act) {
            $activity8[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
          }
        }

        $activity9 = [];
        if(!empty($task->getActivity9())) {
          foreach ($task->getActivity9() as $act) {
            $activity9[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
          }
        }

        $activity10 = [];
        if(!empty($task->getActivity10())) {
          foreach ($task->getActivity10() as $act) {
            $activity10[] = $this->getEntityManager()->getRepository(Activity::class)->find($act);
          }
        }

        $oprema1 = [];
        if(!empty($task->getOprema1())) {
          foreach ($task->getOprema1() as $opr) {
            $oprema1[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
          }
        }

        $oprema2 = [];
        if(!empty($task->getOprema2())) {
          foreach ($task->getOprema2() as $opr) {
            $oprema2[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
          }
        }
        $oprema3 = [];
        if(!empty($task->getOprema3())) {
          foreach ($task->getOprema3() as $opr) {
            $oprema3[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
          }
        }
        $oprema4 = [];
        if(!empty($task->getOprema4())) {
          foreach ($task->getOprema4() as $opr) {
            $oprema4[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
          }
        }
        $oprema5 = [];
        if(!empty($task->getOprema5())) {
          foreach ($task->getOprema5() as $opr) {
            $oprema5[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
          }
        }
        $oprema6 = [];
        if(!empty($task->getOprema6())) {
          foreach ($task->getOprema6() as $opr) {
            $oprema6[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
          }
        }
        $oprema7 = [];
        if(!empty($task->getOprema7())) {
          foreach ($task->getOprema7() as $opr) {
            $oprema7[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
          }
        }
        $oprema8 = [];
        if(!empty($task->getOprema8())) {
          foreach ($task->getOprema8() as $opr) {
            $oprema8[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
          }
        }
        $oprema9 = [];
        if(!empty($task->getOprema9())) {
          foreach ($task->getOprema9() as $opr) {
            $oprema9[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
          }
        }
        $oprema10 = [];
        if(!empty($task->getOprema10())) {
          foreach ($task->getOprema10() as $opr) {
            $oprema10[] = $this->getEntityManager()->getRepository(Tool::class)->find($opr);
          }
        }


        if (!is_null($task->getCar1())) {
          $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar1()]);
        } else {
          $car1 = null;
        }
        if (!is_null($task->getCar2())) {
          $car2 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar2()]);
        } else {
          $car2 = null;
        }
        if (!is_null($task->getCar3())) {
          $car3 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar3()]);
        } else {
          $car3 = null;
        }
        if (!is_null($task->getCar4())) {
          $car4 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar4()]);
        } else {
          $car4 = null;
        }
        if (!is_null($task->getCar5())) {
          $car5 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar5()]);
        } else {
          $car5 = null;
        }
        if (!is_null($task->getCar6())) {
          $car6 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar6()]);
        } else {
          $car6 = null;
        }
        if (!is_null($task->getCar7())) {
          $car7 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar7()]);
        } else {
          $car7 = null;
        }
        if (!is_null($task->getCar8())) {
          $car8 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar8()]);
        } else {
          $car8 = null;
        }
        if (!is_null($task->getCar9())) {
          $car9 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar9()]);
        } else {
          $car9 = null;
        }
        if (!is_null($task->getCar10())) {
          $car10 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id'=> $task->getCar10()]);
        } else {
          $car10 = null;
        }

        if (!is_null($task->getTime1())) {
          $time1 = $task->getTime1();
        } else {
          $time1 = null;
        }
        if (!is_null($task->getTime2())) {
          $time2 = $task->getTime2();
        } else {
          $time2 = null;
        }
        if (!is_null($task->getTime3())) {
          $time3 = $task->getTime3();
        } else {
          $time3 = null;
        }
        if (!is_null($task->getTime4())) {
          $time4 = $task->getTime4();
        } else {
          $time4 = null;
        }
        if (!is_null($task->getTime5())) {
          $time5 = $task->getTime5();
        } else {
          $time5 = null;
        }
        if (!is_null($task->getTime6())) {
          $time6 = $task->getTime6();
        } else {
          $time6 = null;
        }
        if (!is_null($task->getTime7())) {
          $time7 = $task->getTime7();
        } else {
          $time7 = null;
        }
        if (!is_null($task->getTime8())) {
          $time8 = $task->getTime8();
        } else {
          $time8 = null;
        }
        if (!is_null($task->getTime9())) {
          $time9 = $task->getTime9();
        } else {
          $time9 = null;
        }
        if (!is_null($task->getTime10())) {
          $time10 = $task->getTime10();
        } else {
          $time10 = null;
        }

        $tasks = [
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject1()]),
            'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo11()]),
            'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo21()]),
            'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo31()]),
            'aktivnosti' => $activity1,
            'oprema' => $oprema1,
            'napomena' => $task->getDescription1(),
            'vozilo' => $car1,
            'vreme' => $time1,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject2()]),
            'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo12()]),
            'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo22()]),
            'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo32()]),
            'aktivnosti' => $activity2,
            'oprema' => $oprema2,
            'napomena' => $task->getDescription2(),
            'vozilo' => $car2,
            'vreme' => $time2,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject3()]),
            'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo13()]),
            'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo23()]),
            'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo33()]),
            'aktivnosti' => $activity3,
            'oprema' => $oprema3,
            'napomena' => $task->getDescription3(),
            'vozilo' => $car3,
            'vreme' => $time3,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject4()]),
            'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo14()]),
            'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo24()]),
            'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo34()]),
            'aktivnosti' => $activity4,
            'oprema' => $oprema4,
            'napomena' => $task->getDescription4(),
            'vozilo' => $car4,
            'vreme' => $time4,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject5()]),
            'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo15()]),
            'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo25()]),
            'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo35()]),
            'aktivnosti' => $activity5,
            'oprema' => $oprema5,
            'napomena' => $task->getDescription5(),
            'vozilo' => $car5,
            'vreme' => $time5,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject6()]),
            'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo16()]),
            'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo26()]),
            'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo36()]),
            'aktivnosti' => $activity6,
            'oprema' => $oprema6,
            'napomena' => $task->getDescription6(),
            'vozilo' => $car6,
            'vreme' => $time6,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject7()]),
            'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo17()]),
            'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo27()]),
            'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo37()]),
            'aktivnosti' => $activity7,
            'oprema' => $oprema7,
            'napomena' => $task->getDescription7(),
            'vozilo' => $car7,
            'vreme' => $time7,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject8()]),
            'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo18()]),
            'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo28()]),
            'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo38()]),
            'aktivnosti' => $activity8,
            'oprema' => $oprema8,
            'napomena' => $task->getDescription8(),
            'vozilo' => $car8,
            'vreme' => $time8,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject9()]),
            'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo19()]),
            'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo29()]),
            'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo39()]),
            'aktivnosti' => $activity9,
            'oprema' => $oprema9,
            'napomena' => $task->getDescription9(),
            'vozilo' => $car9,
            'vreme' => $time9,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject10()]),
            'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo110()]),
            'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo210()]),
            'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo310()]),
            'aktivnosti' => $activity10,
            'oprema' => $oprema10,
            'napomena' => $task->getDescription10(),
            'vozilo' => $car10,
            'vreme' => $time10,
          ]
        ];
      }
    }

//    $otherTasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($date);



    usort($tasks, function($a, $b) {
      return $a['vreme'] <=> $b['vreme'];
    });

    return $tasks;
  }

  public function getTimeTableId(DateTimeImmutable $date): int {

    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('f');
    $qb
      ->where($qb->expr()->between('f.datum', ':start', ':end'))
      ->andWhere('f.status = :status1 OR f.status = :status2')
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate)
      ->setParameter('status1', FastTaskData::SAVED)
      ->setParameter('status2', FastTaskData::EDIT)
      ->setMaxResults(1);

    $query = $qb->getQuery();
    $fast = $query->getResult();

    if (!empty ($fast)) {
      return $fast[0]->getId();
    }
    return 0;

  }

  public function getTimeTableTomorrowId(DateTimeImmutable $date): int {

    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('f');
    $qb
      ->where($qb->expr()->between('f.datum', ':start', ':end'))
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate)
      ->setMaxResults(1);

    $query = $qb->getQuery();
    $fast = $query->getResult();

    if (!empty ($fast)) {
      return $fast[0]->getId();
    }
    return 0;

  }

  public function saveFastTask(FastTask $fastTask, array $data): FastTask {

    $datum = $data['task_quick_form_datum'];
    $format = "d.m.Y H:i:s";
    $dateTime = DateTimeImmutable::createFromFormat($format, $datum . '14:30:00');
    $currentTime = new DateTimeImmutable();
    $editTime = $dateTime->sub(new DateInterval('P1D'));


    if ($currentTime > $editTime) {
      $status = FastTaskData::EDIT;
    } else {
      $status = FastTaskData::OPEN;
    }

    $stanja = [];

    $fastTask->setDatum($dateTime);
    $noTasks = 0;
//dd($data);
    if (isset($data['task_quick_form1'])) {
      $task1 = $data['task_quick_form1'];
      if (!is_null($fastTask->getId())) {

        $proj1 = $task1['projekat'];
        if ($proj1 == '---') {
          $proj1 = null;
        }
        if (isset($task1['geo'][0])) {
          $geo11 = $task1['geo'][0];
          if ($geo11 == '---') {
            $geo11 = null;
          }
        } else {
          $geo11 = null;
        }

        if (isset($task1['geo'][1])) {
          $geo21 = $task1['geo'][1];
          if ($geo21 == '---') {
            $geo21 = null;
          }
        } else {
          $geo21 = null;
        }
        if (isset($task1['geo'][2])) {
          $geo31 = $task1['geo'][2];
          if ($geo31 == '---') {
            $geo31 = null;
          }
        } else {
          $geo31 = null;
        }

        if (isset($task1['vozilo'])) {
          $vozilo1 = $task1['vozilo'];
          if ($vozilo1 == '---') {
            $vozilo1 = null;
          }
        } else {
          $vozilo1 = null;
        }
        if (isset($task1['vozac'])) {
          $vozac1 = $task1['vozac'];
          if ($vozac1 == '---') {
            $vozac1 = null;
          }
        } else {
          $vozac1 = null;
        }

        if (isset($task1['napomena'])) {
          $napomena1 = trim($task1['napomena']);
          if (empty($napomena1)) {
            $napomena1 = null;
          }
        } else {
          $napomena1 = null;
        }


        if (is_null($proj1)) {
          if (!is_null($fastTask->getTask1())) {
            if ($fastTask->getTask1() > 0) {
              $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
            }
          }
          $fastTask->setProject1(null);
          $fastTask->setGeo11(null);
          $fastTask->setGeo21(null);
          $fastTask->setGeo31(null);
          $fastTask->setActivity1(null);
          $fastTask->setOprema1(null);
          $fastTask->setDescription1(null);
          $fastTask->setTime1(null);
          $fastTask->setCar1(null);
          $fastTask->setDriver1(null);
          $fastTask->setTask1(null);
          $fastTask->setStatus1(null);
        } else {
          if ($fastTask->getProject1() != $proj1 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask1())) {
              if ($fastTask->getTask1() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
                $fastTask->setTask1(0);
              }
            }
            $fastTask->setStatus1(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo11() != $geo11 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask1())) {
              if ($fastTask->getTask1() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
                $fastTask->setTask1(0);
              }
            }
            $fastTask->setStatus1(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo21() != $geo21 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask1())) {
              if ($fastTask->getTask1() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
                $fastTask->setTask1(0);
              }
            }
            $fastTask->setStatus1(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo31() != $geo31 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask1())) {
              if ($fastTask->getTask1() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
                $fastTask->setTask1(0);
              }
            }
            $fastTask->setStatus1(FastTaskData::EDIT);
          }
          if (isset($task1['aktivnosti']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getActivity1() != $task1['aktivnosti']) {
              if (!is_null($fastTask->getTask1())) {
                if ($fastTask->getTask1() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
                  $fastTask->setTask1(0);
                }
              }
              $fastTask->setStatus1(FastTaskData::EDIT);
            }
          }
          if (isset($task1['oprema']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getOprema1() != $task1['oprema']) {
              if (!is_null($fastTask->getTask1())) {
                if ($fastTask->getTask1() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
                  $fastTask->setTask1(0);
                }
              }
              $fastTask->setStatus1(FastTaskData::EDIT);
            }
          }
          if ($fastTask->getDescription1() != $napomena1 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask1())) {
              if ($fastTask->getTask1() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
                $fastTask->setTask1(0);
              }
            }
            $fastTask->setStatus1(FastTaskData::EDIT);
          }
          if (isset($task1['vreme']) && $fastTask->getTime1() != $task1['vreme'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask1())) {
              if ($fastTask->getTask1() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
                $fastTask->setTask1(0);
              }
            }
            $fastTask->setStatus1(FastTaskData::EDIT);
          }
          if ($fastTask->getCar1() != $vozilo1 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask1())) {
              if ($fastTask->getTask1() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
                $fastTask->setTask1(0);
              }
            }
            $fastTask->setStatus1(FastTaskData::EDIT);
          }
          if ($fastTask->getDriver1() != $vozac1 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask1())) {
              if ($fastTask->getTask1() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
                $fastTask->setTask1(0);
              }
            }
            $fastTask->setStatus1(FastTaskData::EDIT);
          }
        }
      }
      if (($task1['projekat']) !== '---') {
        $noTasks++;
        $fastTask->setProject1($task1['projekat']);
        if (($task1['geo'][0]) !== '---') {
          $fastTask->setGeo11($task1['geo'][0]);
        } else {
          $fastTask->setGeo11(null);
        }
        if (($task1['geo'][1]) !== '---') {
          $fastTask->setGeo21($task1['geo'][1]);
        } else {
          $fastTask->setGeo21(null);
        }
        if (($task1['geo'][2]) !== '---') {
          $fastTask->setGeo31($task1['geo'][2]);
        } else {
          $fastTask->setGeo31(null);
        }

        if (isset($task1['aktivnosti'])) {
          $fastTask->setActivity1($task1['aktivnosti']);
        } else {
          $fastTask->setActivity1(null);
        }

        if (isset($task1['oprema'])) {
          $fastTask->setOprema1($task1['oprema']);
        } else {
          $fastTask->setOprema1(null);
        }

        if (!empty(trim($task1['napomena']))) {
          $fastTask->setDescription1(trim($task1['napomena']));
        } else {
          $fastTask->setDescription1(null);
        }

        if (!empty($task1['vreme'])) {
          $fastTask->setTime1($task1['vreme']);
        }

        $fastTask->setCar1(null);
        $fastTask->setDriver1(null);
        if (isset($task1['vozilo'])) {
          if ($task1['vozilo'] != '---') {
            $fastTask->setCar1($task1['vozilo']);
            $fastTask->setDriver1($fastTask->getGeo11());
            if (isset($task1['vozac'])) {
              if ($task1['vozac'] != '---') {
                $fastTask->setDriver1($task1['vozac']);
              }
            }
          }
        }

      } else {
        $fastTask->setGeo11(null);
        $fastTask->setGeo21(null);
        $fastTask->setGeo31(null);
        $fastTask->setActivity1(null);
        $fastTask->setOprema1(null);
        $fastTask->setDescription1(null);
        $fastTask->setTime1(null);
        $fastTask->setCar1(null);
        $fastTask->setDriver1(null);
        if (!is_null($fastTask->getTask1())) {
          $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
          $fastTask->setTask1(null);
        }
      }
    }
    if (isset($data['task_quick_form2'])) {
      $task2 = $data['task_quick_form2'];

      if (!is_null($fastTask->getId())) {
        $proj2 = $task2['projekat'];
        if ($proj2 == '---') {
          $proj2 = null;
        }
        if (isset($task2['geo'][0])) {
          $geo12 = $task2['geo'][0];
          if ($geo12 == '---') {
            $geo12 = null;
          }
        } else {
          $geo12 = null;
        }

        if (isset($task2['geo'][1])) {
          $geo22 = $task2['geo'][1];
          if ($geo22 == '---') {
            $geo22 = null;
          }
        } else {
          $geo22 = null;
        }
        if (isset($task2['geo'][2])) {
          $geo32 = $task2['geo'][2];
          if ($geo32 == '---') {
            $geo32 = null;
          }
        } else {
          $geo32 = null;
        }

        if (isset($task2['vozilo'])) {
          $vozilo2 = $task2['vozilo'];
          if ($vozilo2 == '---') {
            $vozilo2 = null;
          }
        } else {
          $vozilo2 = null;
        }
        if (isset($task2['vozac'])) {
          $vozac2 = $task2['vozac'];
          if ($vozac2 == '---') {
            $vozac2 = null;
          }
        } else {
          $vozac2 = null;
        }

        if (isset($task2['napomena'])) {
          $napomena2 = trim($task2['napomena']);
          if (empty($napomena2)) {
            $napomena2 = null;
          }
        } else {
          $napomena2 = null;
        }

        if (is_null($proj2)) {
          if (!is_null($fastTask->getTask2())) {
            if ($fastTask->getTask2() > 0) {
              $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
            }
          }
          $fastTask->setProject2(null);
          $fastTask->setGeo12(null);
          $fastTask->setGeo22(null);
          $fastTask->setGeo32(null);
          $fastTask->setActivity2(null);
          $fastTask->setOprema2(null);
          $fastTask->setDescription2(null);
          $fastTask->setTime2(null);
          $fastTask->setCar2(null);
          $fastTask->setDriver2(null);
          $fastTask->setTask2(null);
          $fastTask->setStatus2(null);
        } else {
          if ($fastTask->getProject2() != $proj2 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask2())) {
              if ($fastTask->getTask2() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
                $fastTask->setTask2(0);
              }
            }
            $fastTask->setStatus2(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo12() != $geo12 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask2())) {
              if ($fastTask->getTask2() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
                $fastTask->setTask2(0);
              }
            }
            $fastTask->setStatus2(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo22() != $geo22 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask2())) {
              $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
              $fastTask->setTask2(0);
            }
            $fastTask->setStatus2(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo32() != $geo32 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask2())) {
              if ($fastTask->getTask2() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
                $fastTask->setTask2(0);
              }
            }
            $fastTask->setStatus2(FastTaskData::EDIT);
          }
          if (isset($task2['aktivnosti']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getActivity2() != $task2['aktivnosti']) {
              if (!is_null($fastTask->getTask2())) {
                if ($fastTask->getTask2() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
                  $fastTask->setTask2(0);
                }
              }
              $fastTask->setStatus2(FastTaskData::EDIT);
            }
          }
          if (isset($task2['oprema']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getOprema2() != $task2['oprema']) {
              if (!is_null($fastTask->getTask2())) {
                if ($fastTask->getTask2() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
                  $fastTask->setTask2(0);
                }
              }
              $fastTask->setStatus2(FastTaskData::EDIT);
            }
          }
          if ($fastTask->getDescription2() != $napomena2 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask2())) {
              if ($fastTask->getTask2() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
                $fastTask->setTask2(0);
              }
            }
            $fastTask->setStatus2(FastTaskData::EDIT);
          }
          if (isset($task2['vreme']) && $fastTask->getTime2() != $task2['vreme'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask2())) {
              if ($fastTask->getTask2() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
                $fastTask->setTask2(0);
              }
            }
            $fastTask->setStatus2(FastTaskData::EDIT);
          }
          if ($fastTask->getCar2() != $vozilo2 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask2())) {
              if ($fastTask->getTask2() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
                $fastTask->setTask2(0);
              }
            }
            $fastTask->setStatus2(FastTaskData::EDIT);
          }
          if ($fastTask->getDriver2() != $vozac2 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask2())) {
              if ($fastTask->getTask2() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
                $fastTask->setTask2(0);
              }
            }
            $fastTask->setStatus2(FastTaskData::EDIT);
          }
        }
      }
      if (($task2['projekat']) !== '---') {
        $noTasks++;
        $fastTask->setProject2($task2['projekat']);
        if (($task2['geo'][0]) !== '---') {
          $fastTask->setGeo12($task2['geo'][0]);
        } else {
          $fastTask->setGeo12(null);
        }
        if (($task2['geo'][1]) !== '---') {
          $fastTask->setGeo22($task2['geo'][1]);
        }else {
          $fastTask->setGeo22(null);
        }
        if (($task2['geo'][2]) !== '---') {
          $fastTask->setGeo32($task2['geo'][2]);
        } else {
          $fastTask->setGeo32(null);
        }

        if (isset($task2['aktivnosti'])) {
          $fastTask->setActivity2($task2['aktivnosti']);
        } else {
          $fastTask->setActivity2(null);
        }

        if (isset($task2['oprema'])) {
          $fastTask->setOprema2($task2['oprema']);
        } else {
          $fastTask->setOprema2(null);
        }

        if (!empty(trim($task2['napomena']))) {
          $fastTask->setDescription2($task2['napomena']);
        } else {
          $fastTask->setDescription2(null);
        }

        if (!empty($task2['vreme'])) {
          $fastTask->setTime2($task2['vreme']);
        }

        $fastTask->setCar2(null);
        $fastTask->setDriver2(null);
        if (isset($task2['vozilo'])) {
          if ($task2['vozilo'] != '---') {
            $fastTask->setCar2($task2['vozilo']);
            $fastTask->setDriver2($fastTask->getGeo12());
            if (isset($task2['vozac'])) {
              if ($task2['vozac'] != '---') {
                $fastTask->setDriver2($task2['vozac']);
              }
            }
          }
        }

      } else {
        $fastTask->setGeo12(null);
        $fastTask->setGeo22(null);
        $fastTask->setGeo32(null);
        $fastTask->setActivity2(null);
        $fastTask->setOprema2(null);
        $fastTask->setDescription2(null);
        $fastTask->setTime2(null);
        $fastTask->setCar2(null);
        $fastTask->setDriver2(null);
        if(!is_null($fastTask->getTask2())) {
          $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
          $fastTask->setTask2(null);
        }
      }
    }
    if (isset($data['task_quick_form3'])) {
      $task3 = $data['task_quick_form3'];
      if (!is_null($fastTask->getId())) {
        $proj3 = $task3['projekat'];
        if ($proj3 == '---') {
          $proj3 = null;
        }
        if (isset($task3['geo'][0])) {
          $geo13 = $task3['geo'][0];
          if ($geo13 == '---') {
            $geo13 = null;
          }
        } else {
          $geo13 = null;
        }

        if (isset($task3['geo'][1])) {
          $geo23 = $task3['geo'][1];
          if ($geo23 == '---') {
            $geo23 = null;
          }
        } else {
          $geo23 = null;
        }
        if (isset($task3['geo'][2])) {
          $geo33 = $task3['geo'][2];
          if ($geo33 == '---') {
            $geo33 = null;
          }
        } else {
          $geo33 = null;
        }

        if (isset($task3['vozilo'])) {
          $vozilo3 = $task3['vozilo'];
          if ($vozilo3 == '---') {
            $vozilo3 = null;
          }
        } else {
          $vozilo3 = null;
        }
        if (isset($task3['vozac'])) {
          $vozac3 = $task3['vozac'];
          if ($vozac3 == '---') {
            $vozac3 = null;
          }
        } else {
          $vozac3 = null;
        }

        if (isset($task3['napomena'])) {
          $napomena3 = trim($task3['napomena']);
          if (empty($napomena3)) {
            $napomena3 = null;
          }
        } else {
          $napomena3 = null;
        }
        if (is_null($proj3)) {
          if (!is_null($fastTask->getTask3())) {
            if ($fastTask->getTask3() > 0) {
              $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
            }
          }
          $fastTask->setProject3(null);
          $fastTask->setGeo13(null);
          $fastTask->setGeo23(null);
          $fastTask->setGeo33(null);
          $fastTask->setActivity3(null);
          $fastTask->setOprema3(null);
          $fastTask->setDescription3(null);
          $fastTask->setTime3(null);
          $fastTask->setCar3(null);
          $fastTask->setDriver3(null);
          $fastTask->setTask3(null);
          $fastTask->setStatus3(null);
        } else {
          if ($fastTask->getProject3() != $proj3 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask3())) {
              if ($fastTask->getTask3() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
                $fastTask->setTask3(0);
              }
            }
            $fastTask->setStatus3(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo13() != $geo13 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask3())) {
              if ($fastTask->getTask3() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
                $fastTask->setTask3(0);
              }
            }
            $fastTask->setStatus3(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo23() != $geo23 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask3())) {
              if ($fastTask->getTask3() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
                $fastTask->setTask3(0);
              }
            }
            $fastTask->setStatus3(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo33() != $geo33 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask3())) {
              if ($fastTask->getTask3() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
                $fastTask->setTask3(0);
              }
            }
            $fastTask->setStatus3(FastTaskData::EDIT);
          }
          if (isset($task3['aktivnosti']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getActivity3() != $task3['aktivnosti']) {
              if (!is_null($fastTask->getTask3())) {
                if ($fastTask->getTask3() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
                  $fastTask->setTask3(0);
                }
              }
              $fastTask->setStatus3(FastTaskData::EDIT);
            }
          }
          if (isset($task3['oprema']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getOprema3() != $task3['oprema']) {
              if (!is_null($fastTask->getTask3())) {
                if ($fastTask->getTask3() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
                  $fastTask->setTask3(0);
                }
              }
              $fastTask->setStatus3(FastTaskData::EDIT);
            }
          }
          if ($fastTask->getDescription3() != $napomena3 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask3())) {
              if ($fastTask->getTask3() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
                $fastTask->setTask3(0);
              }
            }
            $fastTask->setStatus3(FastTaskData::EDIT);
          }
          if (isset($task3['vreme']) && $fastTask->getTime3() != $task3['vreme'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask3())) {
              if ($fastTask->getTask3() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
                $fastTask->setTask3(0);
              }
            }
            $fastTask->setStatus3(FastTaskData::EDIT);
          }
          if ($fastTask->getCar3() != $vozilo3 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask3())) {
              if ($fastTask->getTask3() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
                $fastTask->setTask3(0);
              }
            }
            $fastTask->setStatus3(FastTaskData::EDIT);
          }
          if ($fastTask->getDriver3() != $vozac3 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask3())) {
              if ($fastTask->getTask3() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
                $fastTask->setTask3(0);
              }
            }
            $fastTask->setStatus3(FastTaskData::EDIT);
          }
        }
      }
      if (($task3['projekat']) !== '---') {
        $noTasks++;
        $fastTask->setProject3($task3['projekat']);
        if (($task3['geo'][0]) !== '---') {
          $fastTask->setGeo13($task3['geo'][0]);
        } else {
          $fastTask->setGeo13(null);
        }
        if (($task3['geo'][1]) !== '---') {
          $fastTask->setGeo23($task3['geo'][1]);
        }else {
          $fastTask->setGeo23(null);
        }
        if (($task3['geo'][2]) !== '---') {
          $fastTask->setGeo33($task3['geo'][2]);
        } else {
          $fastTask->setGeo33(null);
        }

        if (isset($task3['aktivnosti'])) {
          $fastTask->setActivity3($task3['aktivnosti']);
        } else {
          $fastTask->setActivity3(null);
        }

        if (isset($task3['oprema'])) {
          $fastTask->setOprema3($task3['oprema']);
        } else {
          $fastTask->setOprema3(null);
        }

        if (!empty(trim($task3['napomena']))) {
          $fastTask->setDescription3($task3['napomena']);
        } else {
          $fastTask->setDescription3(null);
        }

        if (!empty($task3['vreme'])) {
          $fastTask->setTime3($task3['vreme']);
        }

        $fastTask->setCar3(null);
        $fastTask->setDriver3(null);
        if (isset($task3['vozilo'])) {
          if ($task3['vozilo'] != '---') {
            $fastTask->setCar3($task3['vozilo']);
            $fastTask->setDriver3($fastTask->getGeo13());
            if (isset($task3['vozac'])) {
              if ($task3['vozac'] != '---') {
                $fastTask->setDriver3($task3['vozac']);
              }
            }
          }
        }

      } else {
        $fastTask->setGeo13(null);
        $fastTask->setGeo23(null);
        $fastTask->setGeo33(null);
        $fastTask->setActivity3(null);
        $fastTask->setOprema3(null);
        $fastTask->setDescription3(null);
        $fastTask->setTime3(null);
        $fastTask->setCar3(null);
        $fastTask->setDriver3(null);
        if(!is_null($fastTask->getTask3())) {
          $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
          $fastTask->setTask3(null);
        }
      }
    }
    if (isset($data['task_quick_form4'])) {
      $task4 = $data['task_quick_form4'];
      if (!is_null($fastTask->getId())) {
        $proj4 = $task4['projekat'];
        if ($proj4 == '---') {
          $proj4 = null;
        }
        if (isset($task4['geo'][0])) {
          $geo14 = $task4['geo'][0];
          if ($geo14 == '---') {
            $geo14 = null;
          }
        } else {
          $geo14 = null;
        }

        if (isset($task4['geo'][1])) {
          $geo24 = $task4['geo'][1];
          if ($geo24 == '---') {
            $geo24 = null;
          }
        } else {
          $geo24 = null;
        }
        if (isset($task4['geo'][2])) {
          $geo34 = $task4['geo'][2];
          if ($geo34 == '---') {
            $geo34 = null;
          }
        } else {
          $geo34 = null;
        }

        if (isset($task4['vozilo'])) {
          $vozilo4 = $task4['vozilo'];
          if ($vozilo4 == '---') {
            $vozilo4 = null;
          }
        } else {
          $vozilo4 = null;
        }
        if (isset($task4['vozac'])) {
          $vozac4 = $task4['vozac'];
          if ($vozac4 == '---') {
            $vozac4 = null;
          }
        } else {
          $vozac4 = null;
        }

        if (isset($task4['napomena'])) {
          $napomena4 = trim($task4['napomena']);
          if (empty($napomena4)) {
            $napomena4 = null;
          }
        } else {
          $napomena4 = null;
        }
        if (is_null($proj4)) {
          if (!is_null($fastTask->getTask4())) {
            if ($fastTask->getTask4() > 0) {
              $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
            }
          }
          $fastTask->setProject4(null);
          $fastTask->setGeo14(null);
          $fastTask->setGeo24(null);
          $fastTask->setGeo34(null);
          $fastTask->setActivity4(null);
          $fastTask->setOprema4(null);
          $fastTask->setDescription4(null);
          $fastTask->setTime4(null);
          $fastTask->setCar4(null);
          $fastTask->setDriver4(null);
          $fastTask->setTask4(null);
          $fastTask->setStatus4(null);
        } else {
          if ($fastTask->getProject4() != $proj4 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask4())) {
              if ($fastTask->getTask4() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
                $fastTask->setTask4(0);
              }
            }
            $fastTask->setStatus4(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo14() != $geo14 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask4())) {
              if ($fastTask->getTask4() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
                $fastTask->setTask4(0);
              }
            }
            $fastTask->setStatus4(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo24() != $geo24 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask4())) {
              if ($fastTask->getTask4() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
                $fastTask->setTask4(0);
              }
            }
            $fastTask->setStatus4(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo34() != $geo34 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask4())) {
              if ($fastTask->getTask4() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
                $fastTask->setTask4(0);
              }
            }
            $fastTask->setStatus4(FastTaskData::EDIT);
          }
          if (isset($task4['aktivnosti']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getActivity4() != $task4['aktivnosti']) {
              if (!is_null($fastTask->getTask4())) {
                if ($fastTask->getTask4() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
                  $fastTask->setTask4(0);
                }
              }
              $fastTask->setStatus4(FastTaskData::EDIT);
            }
          }
          if (isset($task4['oprema']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getOprema4() != $task4['oprema']) {
              if (!is_null($fastTask->getTask4())) {
                if ($fastTask->getTask4() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
                  $fastTask->setTask4(0);
                }
              }
              $fastTask->setStatus4(FastTaskData::EDIT);
            }
          }
          if ($fastTask->getDescription4() != $napomena4 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask4())) {
              if ($fastTask->getTask4() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
                $fastTask->setTask4(0);
              }
            }
            $fastTask->setStatus4(FastTaskData::EDIT);
          }
          if (isset($task4['vreme']) && $fastTask->getTime4() != $task4['vreme'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask4())) {
              if ($fastTask->getTask4() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
                $fastTask->setTask4(0);
              }
            }
            $fastTask->setStatus4(FastTaskData::EDIT);
          }
          if ($fastTask->getCar4() != $vozilo4 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask4())) {
              if ($fastTask->getTask4() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
                $fastTask->setTask4(0);
              }
            }
            $fastTask->setStatus4(FastTaskData::EDIT);
          }
          if ($fastTask->getDriver4() != $vozac4 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask4())) {
              if ($fastTask->getTask4() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
                $fastTask->setTask4(0);
              }
            }
            $fastTask->setStatus4(FastTaskData::EDIT);
          }
        }
      }
      if (($task4['projekat']) !== '---') {
        $noTasks++;
        $fastTask->setProject4($task4['projekat']);
        if (($task4['geo'][0]) !== '---') {
          $fastTask->setGeo14($task4['geo'][0]);
        } else {
          $fastTask->setGeo14(null);
        }
        if (($task4['geo'][1]) !== '---') {
          $fastTask->setGeo24($task4['geo'][1]);
        }else {
          $fastTask->setGeo24(null);
        }
        if (($task4['geo'][2]) !== '---') {
          $fastTask->setGeo34($task4['geo'][2]);
        } else {
          $fastTask->setGeo34(null);
        }

        if (isset($task4['aktivnosti'])) {
          $fastTask->setActivity4($task4['aktivnosti']);
        } else {
          $fastTask->setActivity4(null);
        }

        if (isset($task4['oprema'])) {
          $fastTask->setOprema4($task4['oprema']);
        } else {
          $fastTask->setOprema4(null);
        }

        if (!empty(trim($task4['napomena']))) {
          $fastTask->setDescription4($task4['napomena']);
        } else {
          $fastTask->setDescription4(null);
        }

        if (!empty($task4['vreme'])) {
          $fastTask->setTime4($task4['vreme']);
        }

        $fastTask->setCar4(null);
        $fastTask->setDriver4(null);
        if (isset($task4['vozilo'])) {
          if ($task4['vozilo'] != '---') {
            $fastTask->setCar4($task4['vozilo']);
            $fastTask->setDriver4($fastTask->getGeo14());
            if (isset($task4['vozac'])) {
              if ($task4['vozac'] != '---') {
                $fastTask->setDriver4($task4['vozac']);
              }
            }
          }
        }

      } else {
        $fastTask->setGeo14(null);
        $fastTask->setGeo24(null);
        $fastTask->setGeo34(null);
        $fastTask->setActivity4(null);
        $fastTask->setOprema4(null);
        $fastTask->setDescription4(null);
        $fastTask->setTime4(null);
        $fastTask->setCar4(null);
        $fastTask->setDriver4(null);
        if(!is_null($fastTask->getTask4())) {
          $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
          $fastTask->setTask4(null);
        }
      }
    }
    if (isset($data['task_quick_form5'])) {
      $task5 = $data['task_quick_form5'];
      if (!is_null($fastTask->getId())) {
        $proj5 = $task5['projekat'];
        if ($proj5 == '---') {
          $proj5 = null;
        }
        if (isset($task5['geo'][0])) {
          $geo15 = $task5['geo'][0];
          if ($geo15 == '---') {
            $geo15 = null;
          }
        } else {
          $geo15 = null;
        }

        if (isset($task5['geo'][1])) {
          $geo25 = $task5['geo'][1];
          if ($geo25 == '---') {
            $geo25 = null;
          }
        } else {
          $geo25 = null;
        }
        if (isset($task5['geo'][2])) {
          $geo35 = $task5['geo'][2];
          if ($geo35 == '---') {
            $geo35 = null;
          }
        } else {
          $geo35 = null;
        }

        if (isset($task5['vozilo'])) {
          $vozilo5 = $task5['vozilo'];
          if ($vozilo5 == '---') {
            $vozilo5 = null;
          }
        } else {
          $vozilo5 = null;
        }
        if (isset($task5['vozac'])) {
          $vozac5 = $task5['vozac'];
          if ($vozac5 == '---') {
            $vozac5 = null;
          }
        } else {
          $vozac5 = null;
        }

        if (isset($task5['napomena'])) {
          $napomena5 = trim($task5['napomena']);
          if (empty($napomena5)) {
            $napomena5 = null;
          }
        } else {
          $napomena5 = null;
        }
        if (is_null($proj5)) {
          if (!is_null($fastTask->getTask5())) {
            if ($fastTask->getTask5() > 0) {
              $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
            }
          }
          $fastTask->setProject5(null);
          $fastTask->setGeo15(null);
          $fastTask->setGeo25(null);
          $fastTask->setGeo35(null);
          $fastTask->setActivity5(null);
          $fastTask->setOprema5(null);
          $fastTask->setDescription5(null);
          $fastTask->setTime5(null);
          $fastTask->setCar5(null);
          $fastTask->setDriver5(null);
          $fastTask->setTask5(null);
          $fastTask->setStatus5(null);
        } else {
          if ($fastTask->getProject5() != $proj5 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask5())) {
              if ($fastTask->getTask5() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
                $fastTask->setTask5(0);
              }
            }
            $fastTask->setStatus5(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo15() != $geo15 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask5())) {
              if ($fastTask->getTask5() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
                $fastTask->setTask5(0);
              }
            }
            $fastTask->setStatus5(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo25() != $geo25 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask5())) {
              if ($fastTask->getTask5() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
                $fastTask->setTask5(0);
              }
            }
            $fastTask->setStatus5(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo35() != $geo35 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask5())) {
              if ($fastTask->getTask5() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
                $fastTask->setTask5(0);
              }
            }
            $fastTask->setStatus5(FastTaskData::EDIT);
          }
          if (isset($task5['aktivnosti']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getActivity5() != $task5['aktivnosti']) {
              if (!is_null($fastTask->getTask5())) {
                if ($fastTask->getTask5() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
                  $fastTask->setTask5(0);
                }
              }
              $fastTask->setStatus5(FastTaskData::EDIT);
            }
          }
          if (isset($task5['oprema']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getOprema5() != $task5['oprema']) {
              if (!is_null($fastTask->getTask5())) {
                if ($fastTask->getTask5() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
                  $fastTask->setTask5(0);
                }
              }
              $fastTask->setStatus5(FastTaskData::EDIT);
            }
          }
          if ($fastTask->getDescription5() != $napomena5 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask5())) {
              if ($fastTask->getTask5() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
                $fastTask->setTask5(0);
              }
            }
            $fastTask->setStatus5(FastTaskData::EDIT);
          }
          if (isset($task5['vreme']) && $fastTask->getTime5() != $task5['vreme'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask5())) {
              if ($fastTask->getTask5() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
                $fastTask->setTask5(0);
              }
            }
            $fastTask->setStatus5(FastTaskData::EDIT);
          }
          if ($fastTask->getCar5() != $vozilo5 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask5())) {
              if ($fastTask->getTask5() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
                $fastTask->setTask5(0);
              }
            }
            $fastTask->setStatus5(FastTaskData::EDIT);
          }
          if ($fastTask->getDriver5() != $vozac5 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask5())) {
              if ($fastTask->getTask5() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
                $fastTask->setTask5(0);
              }
            }
            $fastTask->setStatus5(FastTaskData::EDIT);
          }
        }
      }
      if (($task5['projekat']) !== '---') {
        $noTasks++;
        $fastTask->setProject5($task5['projekat']);
        if (($task5['geo'][0]) !== '---') {
          $fastTask->setGeo15($task5['geo'][0]);
        } else {
          $fastTask->setGeo15(null);
        }
        if (($task5['geo'][1]) !== '---') {
          $fastTask->setGeo25($task5['geo'][1]);
        }else {
          $fastTask->setGeo25(null);
        }
        if (($task5['geo'][2]) !== '---') {
          $fastTask->setGeo35($task5['geo'][2]);
        } else {
          $fastTask->setGeo35(null);
        }

        if (isset($task5['aktivnosti'])) {
          $fastTask->setActivity5($task5['aktivnosti']);
        } else {
          $fastTask->setActivity5(null);
        }

        if (isset($task5['oprema'])) {
          $fastTask->setOprema5($task5['oprema']);
        } else {
          $fastTask->setOprema5(null);
        }

        if (!empty(trim($task5['napomena']))) {
          $fastTask->setDescription5($task5['napomena']);
        } else {
          $fastTask->setDescription5(null);
        }

        if (!empty($task5['vreme'])) {
          $fastTask->setTime5($task5['vreme']);
        }

        $fastTask->setCar5(null);
        $fastTask->setDriver5(null);
        if (isset($task5['vozilo'])) {
          if ($task5['vozilo'] != '---') {
            $fastTask->setCar5($task5['vozilo']);
            $fastTask->setDriver5($fastTask->getGeo15());
            if (isset($task5['vozac'])) {
              if ($task5['vozac'] != '---') {
                $fastTask->setDriver5($task5['vozac']);
              }
            }
          }
        }

      } else {
        $fastTask->setGeo15(null);
        $fastTask->setGeo25(null);
        $fastTask->setGeo35(null);
        $fastTask->setActivity5(null);
        $fastTask->setOprema5(null);
        $fastTask->setDescription5(null);
        $fastTask->setTime5(null);
        $fastTask->setCar5(null);
        $fastTask->setDriver5(null);
        if(!is_null($fastTask->getTask5())) {
          $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
          $fastTask->setTask5(null);
        }
      }
    }
    if (isset($data['task_quick_form6'])) {
      $task6 = $data['task_quick_form6'];
      if (!is_null($fastTask->getId())) {
        $proj6 = $task6['projekat'];
        if ($proj6 == '---') {
          $proj6 = null;
        }
        if (isset($task6['geo'][0])) {
          $geo16 = $task6['geo'][0];
          if ($geo16 == '---') {
            $geo16 = null;
          }
        } else {
          $geo16 = null;
        }

        if (isset($task6['geo'][1])) {
          $geo26 = $task6['geo'][1];
          if ($geo26 == '---') {
            $geo26 = null;
          }
        } else {
          $geo26 = null;
        }
        if (isset($task6['geo'][2])) {
          $geo36 = $task6['geo'][2];
          if ($geo36 == '---') {
            $geo36 = null;
          }
        } else {
          $geo36 = null;
        }

        if (isset($task6['vozilo'])) {
          $vozilo6 = $task6['vozilo'];
          if ($vozilo6 == '---') {
            $vozilo6 = null;
          }
        } else {
          $vozilo6 = null;
        }
        if (isset($task6['vozac'])) {
          $vozac6 = $task6['vozac'];
          if ($vozac6 == '---') {
            $vozac6 = null;
          }
        } else {
          $vozac6 = null;
        }

        if (isset($task6['napomena'])) {
          $napomena6 = trim($task6['napomena']);
          if (empty($napomena6)) {
            $napomena6 = null;
          }
        } else {
          $napomena6 = null;
        }
        if (is_null($proj6)) {
          if (!is_null($fastTask->getTask6())) {
            if ($fastTask->getTask6() > 0) {
              $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
            }
          }
          $fastTask->setProject6(null);
          $fastTask->setGeo16(null);
          $fastTask->setGeo26(null);
          $fastTask->setGeo36(null);
          $fastTask->setActivity6(null);
          $fastTask->setOprema6(null);
          $fastTask->setDescription6(null);
          $fastTask->setTime6(null);
          $fastTask->setCar6(null);
          $fastTask->setDriver6(null);
          $fastTask->setTask6(null);
          $fastTask->setStatus6(null);
        } else {
          if ($fastTask->getProject6() != $proj6 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask6())) {
              if ($fastTask->getTask6() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
                $fastTask->setTask6(0);
              }
            }
            $fastTask->setStatus6(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo16() != $geo16 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask6())) {
              if ($fastTask->getTask6() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
                $fastTask->setTask6(0);
              }
            }
            $fastTask->setStatus6(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo26() != $geo26 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask6())) {
              if ($fastTask->getTask6() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
                $fastTask->setTask6(0);
              }
            }
            $fastTask->setStatus6(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo36() != $geo36 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask6())) {
              if ($fastTask->getTask6() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
                $fastTask->setTask6(0);
              }
            }
            $fastTask->setStatus6(FastTaskData::EDIT);
          }
          if (isset($task6['aktivnosti']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getActivity6() != $task6['aktivnosti']) {
              if (!is_null($fastTask->getTask6())) {
                if ($fastTask->getTask6() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
                  $fastTask->setTask6(0);
                }
              }
              $fastTask->setStatus6(FastTaskData::EDIT);
            }
          }
          if (isset($task6['oprema']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getOprema6() != $task6['oprema']) {
              if (!is_null($fastTask->getTask6())) {
                if ($fastTask->getTask6() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
                  $fastTask->setTask6(0);
                }
              }
              $fastTask->setStatus6(FastTaskData::EDIT);
            }
          }
          if ($fastTask->getDescription6() != $napomena6 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask6())) {
              if ($fastTask->getTask6() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
                $fastTask->setTask6(0);
              }
            }
            $fastTask->setStatus6(FastTaskData::EDIT);
          }
          if (isset($task6['vreme']) && $fastTask->getTime6() != $task6['vreme'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask6())) {
              if ($fastTask->getTask6() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
                $fastTask->setTask6(0);
              }
            }
            $fastTask->setStatus6(FastTaskData::EDIT);
          }
          if ($fastTask->getCar6() != $vozilo6 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask6())) {
              if ($fastTask->getTask6() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
                $fastTask->setTask6(0);
              }
            }
            $fastTask->setStatus6(FastTaskData::EDIT);
          }
          if ($fastTask->getDriver6() != $vozac6 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask6())) {
              if ($fastTask->getTask6() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
                $fastTask->setTask6(0);
              }
            }
            $fastTask->setStatus6(FastTaskData::EDIT);
          }
        }
      }
      if (($task6['projekat']) !== '---') {
        $noTasks++;
        $fastTask->setProject6($task6['projekat']);
        if (($task6['geo'][0]) !== '---') {
          $fastTask->setGeo16($task6['geo'][0]);
        } else {
          $fastTask->setGeo16(null);
        }
        if (($task6['geo'][1]) !== '---') {
          $fastTask->setGeo26($task6['geo'][1]);
        }else {
          $fastTask->setGeo26(null);
        }
        if (($task6['geo'][2]) !== '---') {
          $fastTask->setGeo36($task6['geo'][2]);
        } else {
          $fastTask->setGeo36(null);
        }

        if (isset($task6['aktivnosti'])) {
          $fastTask->setActivity6($task6['aktivnosti']);
        } else {
          $fastTask->setActivity6(null);
        }

        if (isset($task6['oprema'])) {
          $fastTask->setOprema6($task6['oprema']);
        } else {
          $fastTask->setOprema6(null);
        }

        if (!empty(trim($task6['napomena']))) {
          $fastTask->setDescription6($task6['napomena']);
        } else {
          $fastTask->setDescription6(null);
        }

        if (!empty($task6['vreme'])) {
          $fastTask->setTime6($task6['vreme']);
        }

        $fastTask->setCar6(null);
        $fastTask->setDriver6(null);
        if (isset($task6['vozilo'])) {
          if ($task6['vozilo'] != '---') {
            $fastTask->setCar6($task6['vozilo']);
            $fastTask->setDriver6($fastTask->getGeo16());
            if (isset($task6['vozac'])) {
              if ($task6['vozac'] != '---') {
                $fastTask->setDriver6($task6['vozac']);
              }
            }
          }
        }

      } else {
        $fastTask->setGeo16(null);
        $fastTask->setGeo26(null);
        $fastTask->setGeo36(null);
        $fastTask->setActivity6(null);
        $fastTask->setOprema6(null);
        $fastTask->setDescription6(null);
        $fastTask->setTime6(null);
        $fastTask->setCar6(null);
        $fastTask->setDriver6(null);
        if(!is_null($fastTask->getTask6())) {
          $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
          $fastTask->setTask6(null);
        }
      }
    }
    if (isset($data['task_quick_form7'])) {
      $task7 = $data['task_quick_form7'];
      if (!is_null($fastTask->getId())) {
        $proj7 = $task7['projekat'];
        if ($proj7 == '---') {
          $proj7 = null;
        }
        if (isset($task7['geo'][0])) {
          $geo17 = $task7['geo'][0];
          if ($geo17 == '---') {
            $geo17 = null;
          }
        } else {
          $geo17 = null;
        }

        if (isset($task7['geo'][1])) {
          $geo27 = $task7['geo'][1];
          if ($geo27 == '---') {
            $geo27 = null;
          }
        } else {
          $geo27 = null;
        }
        if (isset($task7['geo'][2])) {
          $geo37 = $task7['geo'][2];
          if ($geo37 == '---') {
            $geo37 = null;
          }
        } else {
          $geo37 = null;
        }

        if (isset($task7['vozilo'])) {
          $vozilo7 = $task7['vozilo'];
          if ($vozilo7 == '---') {
            $vozilo7 = null;
          }
        } else {
          $vozilo7 = null;
        }
        if (isset($task7['vozac'])) {
          $vozac7 = $task7['vozac'];
          if ($vozac7 == '---') {
            $vozac7 = null;
          }
        } else {
          $vozac7 = null;
        }

        if (isset($task7['napomena'])) {
          $napomena7 = trim($task7['napomena']);
          if (empty($napomena7)) {
            $napomena7 = null;
          }
        } else {
          $napomena7 = null;
        }
        if (is_null($proj7)) {
          if (!is_null($fastTask->getTask7())) {
            if ($fastTask->getTask7() > 0) {
              $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
            }
          }
          $fastTask->setProject7(null);
          $fastTask->setGeo17(null);
          $fastTask->setGeo27(null);
          $fastTask->setGeo37(null);
          $fastTask->setActivity7(null);
          $fastTask->setOprema7(null);
          $fastTask->setDescription7(null);
          $fastTask->setTime7(null);
          $fastTask->setCar7(null);
          $fastTask->setDriver7(null);
          $fastTask->setTask7(null);
          $fastTask->setStatus7(null);
        } else {
          if ($fastTask->getProject7() != $proj7 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask7())) {
              if ($fastTask->getTask7() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
                $fastTask->setTask7(0);
              }
            }
            $fastTask->setStatus7(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo17() != $geo17 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask7())) {
              if ($fastTask->getTask7() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
                $fastTask->setTask7(0);
              }
            }
            $fastTask->setStatus7(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo27() != $geo27 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask7())) {
              if ($fastTask->getTask7() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
                $fastTask->setTask7(0);
              }
            }
            $fastTask->setStatus7(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo37() != $geo37 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask7())) {
              if ($fastTask->getTask7() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
                $fastTask->setTask7(0);
              }
            }
            $fastTask->setStatus7(FastTaskData::EDIT);
          }
          if (isset($task7['aktivnosti']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getActivity7() != $task7['aktivnosti']) {
              if (!is_null($fastTask->getTask7())) {
                if ($fastTask->getTask7() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
                  $fastTask->setTask7(0);
                }
              }
              $fastTask->setStatus7(FastTaskData::EDIT);
            }
          }
          if (isset($task7['oprema']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getOprema7() != $task7['oprema']) {
              if (!is_null($fastTask->getTask7())) {
                if ($fastTask->getTask7() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
                  $fastTask->setTask7(0);
                }
              }
              $fastTask->setStatus7(FastTaskData::EDIT);
            }
          }
          if ($fastTask->getDescription7() != $napomena7 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask7())) {
              if ($fastTask->getTask7() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
                $fastTask->setTask7(0);
              }
            }
            $fastTask->setStatus7(FastTaskData::EDIT);
          }
          if (isset($task7['vreme']) && $fastTask->getTime7() != $task7['vreme'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask7())) {
              if ($fastTask->getTask7() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
                $fastTask->setTask7(0);
              }
            }
            $fastTask->setStatus7(FastTaskData::EDIT);
          }
          if ($fastTask->getCar7() != $vozilo7 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask7())) {
              if ($fastTask->getTask7() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
                $fastTask->setTask7(0);
              }
            }
            $fastTask->setStatus7(FastTaskData::EDIT);
          }
          if ($fastTask->getDriver7() != $vozac7 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask7())) {
              if ($fastTask->getTask7() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
                $fastTask->setTask7(0);
              }
            }
            $fastTask->setStatus7(FastTaskData::EDIT);
          }
        }
      }
      if (($task7['projekat']) !== '---') {
        $noTasks++;
        $fastTask->setProject7($task7['projekat']);
        if (($task7['geo'][0]) !== '---') {
          $fastTask->setGeo17($task7['geo'][0]);
        } else {
          $fastTask->setGeo17(null);
        }
        if (($task7['geo'][1]) !== '---') {
          $fastTask->setGeo27($task7['geo'][1]);
        }else {
          $fastTask->setGeo27(null);
        }
        if (($task7['geo'][2]) !== '---') {
          $fastTask->setGeo37($task7['geo'][2]);
        } else {
          $fastTask->setGeo37(null);
        }

        if (isset($task7['aktivnosti'])) {
          $fastTask->setActivity7($task7['aktivnosti']);
        } else {
          $fastTask->setActivity7(null);
        }

        if (isset($task7['oprema'])) {
          $fastTask->setOprema7($task7['oprema']);
        } else {
          $fastTask->setOprema7(null);
        }

        if (!empty(trim($task7['napomena']))) {
          $fastTask->setDescription7($task7['napomena']);
        } else {
          $fastTask->setDescription7(null);
        }

        if (!empty($task7['vreme'])) {
          $fastTask->setTime7($task7['vreme']);
        }

        $fastTask->setCar7(null);
        $fastTask->setDriver7(null);
        if (isset($task7['vozilo'])) {
          if ($task7['vozilo'] != '---') {
            $fastTask->setCar7($task7['vozilo']);
            $fastTask->setDriver7($fastTask->getGeo17());
            if (isset($task7['vozac'])) {
              if ($task7['vozac'] != '---') {
                $fastTask->setDriver7($task7['vozac']);
              }
            }
          }
        }

      } else {
        $fastTask->setGeo17(null);
        $fastTask->setGeo27(null);
        $fastTask->setGeo37(null);
        $fastTask->setActivity7(null);
        $fastTask->setOprema7(null);
        $fastTask->setDescription7(null);
        $fastTask->setTime7(null);
        $fastTask->setCar7(null);
        $fastTask->setDriver7(null);
        if(!is_null($fastTask->getTask7())) {
          $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
          $fastTask->setTask7(null);
        }
      }
    }
    if (isset($data['task_quick_form8'])) {
      $task8 = $data['task_quick_form8'];
      if (!is_null($fastTask->getId())) {
        $proj8 = $task8['projekat'];
        if ($proj8 == '---') {
          $proj8 = null;
        }
        if (isset($task8['geo'][0])) {
          $geo18 = $task8['geo'][0];
          if ($geo18 == '---') {
            $geo18 = null;
          }
        } else {
          $geo18 = null;
        }

        if (isset($task8['geo'][1])) {
          $geo28 = $task8['geo'][1];
          if ($geo28 == '---') {
            $geo28 = null;
          }
        } else {
          $geo28 = null;
        }
        if (isset($task8['geo'][2])) {
          $geo38 = $task8['geo'][2];
          if ($geo38 == '---') {
            $geo38 = null;
          }
        } else {
          $geo38 = null;
        }

        if (isset($task8['vozilo'])) {
          $vozilo8 = $task8['vozilo'];
          if ($vozilo8 == '---') {
            $vozilo8 = null;
          }
        } else {
          $vozilo8 = null;
        }
        if (isset($task8['vozac'])) {
          $vozac8 = $task8['vozac'];
          if ($vozac8 == '---') {
            $vozac8 = null;
          }
        } else {
          $vozac8 = null;
        }

        if (isset($task8['napomena'])) {
          $napomena8 = trim($task8['napomena']);
          if (empty($napomena8)) {
            $napomena8 = null;
          }
        } else {
          $napomena8 = null;
        }
        if (is_null($proj8)) {
          if (!is_null($fastTask->getTask8())) {
            if ($fastTask->getTask8() > 0) {
              $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
            }
          }
          $fastTask->setProject8(null);
          $fastTask->setGeo18(null);
          $fastTask->setGeo28(null);
          $fastTask->setGeo38(null);
          $fastTask->setActivity8(null);
          $fastTask->setOprema8(null);
          $fastTask->setDescription8(null);
          $fastTask->setTime8(null);
          $fastTask->setCar8(null);
          $fastTask->setDriver8(null);
          $fastTask->setTask8(null);
          $fastTask->setStatus8(null);
        } else {
          if ($fastTask->getProject8() != $proj8 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask8())) {
              if ($fastTask->getTask8() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
                $fastTask->setTask8(0);
              }
            }
            $fastTask->setStatus8(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo18() != $geo18 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask8())) {
              if ($fastTask->getTask8() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
                $fastTask->setTask8(0);
              }
            }
            $fastTask->setStatus8(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo28() != $geo28 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask8())) {
              if ($fastTask->getTask8() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
                $fastTask->setTask8(0);
              }
            }
            $fastTask->setStatus8(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo38() != $geo38 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask8())) {
              if ($fastTask->getTask8() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
                $fastTask->setTask8(0);
              }
            }
            $fastTask->setStatus8(FastTaskData::EDIT);
          }
          if (isset($task8['aktivnosti']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getActivity8() != $task8['aktivnosti']) {
              if (!is_null($fastTask->getTask8())) {
                if ($fastTask->getTask8() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
                  $fastTask->setTask8(0);
                }
              }
              $fastTask->setStatus8(FastTaskData::EDIT);
            }
          }
          if (isset($task8['oprema']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getOprema8() != $task8['oprema']) {
              if (!is_null($fastTask->getTask8())) {
                if ($fastTask->getTask8() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
                  $fastTask->setTask8(0);
                }
              }
              $fastTask->setStatus8(FastTaskData::EDIT);
            }
          }
          if ($fastTask->getDescription8() != $napomena8 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask8())) {
              if ($fastTask->getTask8() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
                $fastTask->setTask8(0);
              }
            }
            $fastTask->setStatus8(FastTaskData::EDIT);
          }
          if (isset($task8['vreme']) && $fastTask->getTime8() != $task8['vreme'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask8())) {
              if ($fastTask->getTask8() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
                $fastTask->setTask8(0);
              }
            }
            $fastTask->setStatus8(FastTaskData::EDIT);
          }
          if ($fastTask->getCar8() != $vozilo8 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask8())) {
              if ($fastTask->getTask8() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
                $fastTask->setTask8(0);
              }
            }
            $fastTask->setStatus8(FastTaskData::EDIT);
          }
          if ($fastTask->getDriver8() != $vozac8 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask8())) {
              if ($fastTask->getTask8() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
                $fastTask->setTask8(0);
              }
            }
            $fastTask->setStatus8(FastTaskData::EDIT);
          }
        }
      }
      if (($task8['projekat']) !== '---') {
        $noTasks++;
        $fastTask->setProject8($task8['projekat']);
        if (($task8['geo'][0]) !== '---') {
          $fastTask->setGeo18($task8['geo'][0]);
        } else {
          $fastTask->setGeo18(null);
        }
        if (($task8['geo'][1]) !== '---') {
          $fastTask->setGeo28($task8['geo'][1]);
        }else {
          $fastTask->setGeo28(null);
        }
        if (($task8['geo'][2]) !== '---') {
          $fastTask->setGeo38($task8['geo'][2]);
        } else {
          $fastTask->setGeo38(null);
        }

        if (isset($task8['aktivnosti'])) {
          $fastTask->setActivity8($task8['aktivnosti']);
        } else {
          $fastTask->setActivity8(null);
        }

        if (isset($task8['oprema'])) {
          $fastTask->setOprema8($task8['oprema']);
        } else {
          $fastTask->setOprema8(null);
        }

        if (!empty(trim($task8['napomena']))) {
          $fastTask->setDescription8($task8['napomena']);
        } else {
          $fastTask->setDescription8(null);
        }

        if (!empty($task8['vreme'])) {
          $fastTask->setTime8($task8['vreme']);
        }

        $fastTask->setCar8(null);
        $fastTask->setDriver8(null);
        if (isset($task8['vozilo'])) {
          if ($task8['vozilo'] != '---') {
            $fastTask->setCar8($task8['vozilo']);
            $fastTask->setDriver8($fastTask->getGeo18());
            if (isset($task8['vozac'])) {
              if ($task8['vozac'] != '---') {
                $fastTask->setDriver8($task8['vozac']);
              }
            }
          }
        }

      } else {
        $fastTask->setGeo18(null);
        $fastTask->setGeo28(null);
        $fastTask->setGeo38(null);
        $fastTask->setActivity8(null);
        $fastTask->setOprema8(null);
        $fastTask->setDescription8(null);
        $fastTask->setTime8(null);
        $fastTask->setCar8(null);
        $fastTask->setDriver8(null);
        if(!is_null($fastTask->getTask8())) {
          $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
          $fastTask->setTask8(null);
        }
      }
    }
    if (isset($data['task_quick_form9'])) {
      $task9 = $data['task_quick_form9'];
      if (!is_null($fastTask->getId())) {
        $proj9 = $task9['projekat'];
        if ($proj9 == '---') {
          $proj9 = null;
        }
        if (isset($task9['geo'][0])) {
          $geo19 = $task9['geo'][0];
          if ($geo19 == '---') {
            $geo19 = null;
          }
        } else {
          $geo19 = null;
        }

        if (isset($task9['geo'][1])) {
          $geo29 = $task9['geo'][1];
          if ($geo29 == '---') {
            $geo29 = null;
          }
        } else {
          $geo29 = null;
        }
        if (isset($task9['geo'][2])) {
          $geo39 = $task9['geo'][2];
          if ($geo39 == '---') {
            $geo39 = null;
          }
        } else {
          $geo39 = null;
        }

        if (isset($task9['vozilo'])) {
          $vozilo9 = $task9['vozilo'];
          if ($vozilo9 == '---') {
            $vozilo9 = null;
          }
        } else {
          $vozilo9 = null;
        }
        if (isset($task9['vozac'])) {
          $vozac9 = $task9['vozac'];
          if ($vozac9 == '---') {
            $vozac9 = null;
          }
        } else {
          $vozac9 = null;
        }

        if (isset($task9['napomena'])) {
          $napomena9 = trim($task9['napomena']);
          if (empty($napomena9)) {
            $napomena9 = null;
          }
        } else {
          $napomena9 = null;
        }
        if (is_null($proj9)) {
          if (!is_null($fastTask->getTask9())) {
            if ($fastTask->getTask9() > 0) {
              $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
            }
          }
          $fastTask->setProject9(null);
          $fastTask->setGeo19(null);
          $fastTask->setGeo29(null);
          $fastTask->setGeo39(null);
          $fastTask->setActivity9(null);
          $fastTask->setOprema9(null);
          $fastTask->setDescription9(null);
          $fastTask->setTime9(null);
          $fastTask->setCar9(null);
          $fastTask->setDriver9(null);
          $fastTask->setTask9(null);
          $fastTask->setStatus9(null);
        } else {
          if ($fastTask->getProject9() != $proj9 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask9())) {
              if ($fastTask->getTask9() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
                $fastTask->setTask9(0);
              }
            }
            $fastTask->setStatus9(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo19() != $geo19 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask9())) {
              if ($fastTask->getTask9() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
                $fastTask->setTask9(0);
              }
            }
            $fastTask->setStatus9(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo29() != $geo29 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask9())) {
              if ($fastTask->getTask9() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
                $fastTask->setTask9(0);
              }
            }
            $fastTask->setStatus9(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo39() != $geo39 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask9())) {
              if ($fastTask->getTask9() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
                $fastTask->setTask9(0);
              }
            }
            $fastTask->setStatus9(FastTaskData::EDIT);
          }
          if (isset($task9['aktivnosti']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getActivity9() != $task9['aktivnosti']) {
              if (!is_null($fastTask->getTask9())) {
                if ($fastTask->getTask9() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
                  $fastTask->setTask9(0);
                }
              }
              $fastTask->setStatus9(FastTaskData::EDIT);
            }
          }
          if (isset($task9['oprema']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getOprema9() != $task9['oprema']) {
              if (!is_null($fastTask->getTask9())) {
                if ($fastTask->getTask9() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
                  $fastTask->setTask9(0);
                }
              }
              $fastTask->setStatus9(FastTaskData::EDIT);
            }
          }
          if ($fastTask->getDescription9() != $napomena9 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask9())) {
              if ($fastTask->getTask9() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
                $fastTask->setTask9(0);
              }
            }
            $fastTask->setStatus9(FastTaskData::EDIT);
          }
          if (isset($task9['vreme']) && $fastTask->getTime9() != $task9['vreme'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask9())) {
              if ($fastTask->getTask9() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
                $fastTask->setTask9(0);
              }
            }
            $fastTask->setStatus9(FastTaskData::EDIT);
          }
          if ($fastTask->getCar9() != $vozilo9 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask9())) {
              if ($fastTask->getTask9() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
                $fastTask->setTask9(0);
              }
            }
            $fastTask->setStatus9(FastTaskData::EDIT);
          }
          if ($fastTask->getDriver9() != $vozac9 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask9())) {
              if ($fastTask->getTask9() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
                $fastTask->setTask9(0);
              }
            }
            $fastTask->setStatus9(FastTaskData::EDIT);
          }
        }
      }
      if (($task9['projekat']) !== '---') {
        $noTasks++;
        $fastTask->setProject9($task9['projekat']);
        if (($task9['geo'][0]) !== '---') {
          $fastTask->setGeo19($task9['geo'][0]);
        } else {
          $fastTask->setGeo19(null);
        }
        if (($task9['geo'][1]) !== '---') {
          $fastTask->setGeo29($task9['geo'][1]);
        }else {
          $fastTask->setGeo29(null);
        }
        if (($task9['geo'][2]) !== '---') {
          $fastTask->setGeo39($task9['geo'][2]);
        } else {
          $fastTask->setGeo39(null);
        }

        if (isset($task9['aktivnosti'])) {
          $fastTask->setActivity9($task9['aktivnosti']);
        } else {
          $fastTask->setActivity9(null);
        }

        if (isset($task9['oprema'])) {
          $fastTask->setOprema9($task9['oprema']);
        } else {
          $fastTask->setOprema9(null);
        }

        if (!empty(trim($task9['napomena']))) {
          $fastTask->setDescription9($task9['napomena']);
        } else {
          $fastTask->setDescription9(null);
        }

        if (!empty($task9['vreme'])) {
          $fastTask->setTime9($task9['vreme']);
        }

        $fastTask->setCar9(null);
        $fastTask->setDriver9(null);
        if (isset($task9['vozilo'])) {
          if ($task9['vozilo'] != '---') {
            $fastTask->setCar9($task9['vozilo']);
            $fastTask->setDriver9($fastTask->getGeo19());
            if (isset($task9['vozac'])) {
              if ($task9['vozac'] != '---') {
                $fastTask->setDriver9($task9['vozac']);
              }
            }
          }
        }
      } else {
        $fastTask->setGeo19(null);
        $fastTask->setGeo29(null);
        $fastTask->setGeo39(null);
        $fastTask->setActivity9(null);
        $fastTask->setOprema9(null);
        $fastTask->setDescription9(null);
        $fastTask->setTime9(null);
        $fastTask->setCar9(null);
        $fastTask->setDriver9(null);
        if(!is_null($fastTask->getTask9())) {
          $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
          $fastTask->setTask9(null);
        }
      }
    }
    if (isset($data['task_quick_form10'])) {
      $task10 = $data['task_quick_form10'];
      if (!is_null($fastTask->getId())) {
        $proj10 = $task10['projekat'];
        if ($proj10 == '---') {
          $proj10 = null;
        }
        if (isset($task10['geo'][0])) {
          $geo110 = $task10['geo'][0];
          if ($geo110 == '---') {
            $geo110 = null;
          }
        } else {
          $geo110 = null;
        }

        if (isset($task10['geo'][1])) {
          $geo210 = $task10['geo'][1];
          if ($geo210 == '---') {
            $geo210 = null;
          }
        } else {
          $geo210 = null;
        }
        if (isset($task10['geo'][2])) {
          $geo310 = $task10['geo'][2];
          if ($geo310 == '---') {
            $geo310 = null;
          }
        } else {
          $geo310 = null;
        }

        if (isset($task10['vozilo'])) {
          $vozilo10 = $task10['vozilo'];
          if ($vozilo10 == '---') {
            $vozilo10 = null;
          }
        } else {
          $vozilo10 = null;
        }
        if (isset($task10['vozac'])) {
          $vozac10 = $task10['vozac'];
          if ($vozac10 == '---') {
            $vozac10 = null;
          }
        } else {
          $vozac10 = null;
        }

        if (isset($task10['napomena'])) {
          $napomena10 = trim($task10['napomena']);
          if (empty($napomena10)) {
            $napomena10 = null;
          }
        } else {
          $napomena10 = null;
        }
        if (is_null($proj10)) {
          if (!is_null($fastTask->getTask10())) {
            if ($fastTask->getTask10() > 0) {
              $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
            }
          }
          $fastTask->setProject10(null);
          $fastTask->setGeo110(null);
          $fastTask->setGeo210(null);
          $fastTask->setGeo310(null);
          $fastTask->setActivity10(null);
          $fastTask->setOprema10(null);
          $fastTask->setDescription10(null);
          $fastTask->setTime10(null);
          $fastTask->setCar10(null);
          $fastTask->setDriver10(null);
          $fastTask->setTask10(null);
          $fastTask->setStatus10(null);
        } else {
          if ($fastTask->getProject10() != $proj10 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask10())) {
              if ($fastTask->getTask10() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
                $fastTask->setTask10(0);
              }
            }
            $fastTask->setStatus10(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo110() != $geo110 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask10())) {
              if ($fastTask->getTask10() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
                $fastTask->setTask10(0);
              }
            }
            $fastTask->setStatus10(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo210() != $geo210 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask10())) {
              if ($fastTask->getTask10() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
                $fastTask->setTask10(0);
              }
            }
            $fastTask->setStatus10(FastTaskData::EDIT);
          }
          if ($fastTask->getGeo310() != $geo310 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask10())) {
              if ($fastTask->getTask10() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
                $fastTask->setTask10(0);
              }
            }
            $fastTask->setStatus10(FastTaskData::EDIT);
          }
          if (isset($task10['aktivnosti']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getActivity10() != $task10['aktivnosti']) {
              if (!is_null($fastTask->getTask10())) {
                if ($fastTask->getTask10() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
                  $fastTask->setTask10(0);
                }
              }
              $fastTask->setStatus10(FastTaskData::EDIT);
            }
          }
          if (isset($task10['oprema']) && $status == FastTaskData::EDIT) {
            if ($fastTask->getOprema10() != $task10['oprema']) {
              if (!is_null($fastTask->getTask10())) {
                if ($fastTask->getTask10() != 0) {
                  $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
                  $fastTask->setTask10(0);
                }
              }
              $fastTask->setStatus10(FastTaskData::EDIT);
            }
          }
          if ($fastTask->getDescription10() != $napomena10 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask10())) {
              if ($fastTask->getTask10() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
                $fastTask->setTask10(0);
              }
            }
            $fastTask->setStatus10(FastTaskData::EDIT);
          }
          if (isset($task10['vreme']) && $fastTask->getTime10() != $task10['vreme'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask10())) {
              if ($fastTask->getTask10() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
                $fastTask->setTask10(0);
              }
            }
            $fastTask->setStatus10(FastTaskData::EDIT);
          }
          if ($fastTask->getCar10() != $vozilo10 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask10())) {
              if ($fastTask->getTask10() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
                $fastTask->setTask10(0);
              }
            }
            $fastTask->setStatus10(FastTaskData::EDIT);
          }
          if ($fastTask->getDriver10() != $vozac10 && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask10())) {
              if ($fastTask->getTask10() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
                $fastTask->setTask10(0);
              }
            }
            $fastTask->setStatus10(FastTaskData::EDIT);
          }
        }
      }
      if (($task10['projekat']) !== '---') {
        $noTasks++;
        $fastTask->setProject10($task10['projekat']);
        if (($task10['geo'][0]) !== '---') {
          $fastTask->setGeo110($task10['geo'][0]);
        } else {
          $fastTask->setGeo110(null);
        }
        if (($task10['geo'][1]) !== '---') {
          $fastTask->setGeo210($task10['geo'][1]);
        }else {
          $fastTask->setGeo210(null);
        }
        if (($task10['geo'][2]) !== '---') {
          $fastTask->setGeo310($task10['geo'][2]);
        } else {
          $fastTask->setGeo310(null);
        }

        if (isset($task10['aktivnosti'])) {
          $fastTask->setActivity10($task10['aktivnosti']);
        } else {
          $fastTask->setActivity10(null);
        }

        if (isset($task10['oprema'])) {
          $fastTask->setOprema10($task10['oprema']);
        } else {
          $fastTask->setOprema10(null);
        }

        if (!empty(trim($task10['napomena']))) {
          $fastTask->setDescription10($task10['napomena']);
        } else {
          $fastTask->setDescription10(null);
        }

        if (!empty($task10['vreme'])) {
          $fastTask->setTime10($task10['vreme']);
        }

        $fastTask->setCar10(null);
        $fastTask->setDriver10(null);
        if (isset($task10['vozilo'])) {
          if ($task10['vozilo'] != '---') {
            $fastTask->setCar10($task10['vozilo']);
            $fastTask->setDriver10($fastTask->getGeo110());
            if (isset($task10['vozac'])) {
              if ($task10['vozac'] != '---') {
                $fastTask->setDriver10($task10['vozac']);
              }
            }
          }
        }

      } else {
        $fastTask->setGeo110(null);
        $fastTask->setGeo210(null);
        $fastTask->setGeo310(null);
        $fastTask->setActivity10(null);
        $fastTask->setOprema10(null);
        $fastTask->setDescription10(null);
        $fastTask->setTime10(null);
        $fastTask->setCar10(null);
        $fastTask->setDriver10(null);
        if(!is_null($fastTask->getTask10())) {
          $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
          $fastTask->setTask10(null);
        }
      }
    }

    $fastTask->setNoTasks($noTasks);

    $stanja[] = $fastTask->getStatus1();
    $stanja[] = $fastTask->getStatus2();
    $stanja[] = $fastTask->getStatus3();
    $stanja[] = $fastTask->getStatus4();
    $stanja[] = $fastTask->getStatus5();
    $stanja[] = $fastTask->getStatus6();
    $stanja[] = $fastTask->getStatus7();
    $stanja[] = $fastTask->getStatus8();
    $stanja[] = $fastTask->getStatus9();
    $stanja[] = $fastTask->getStatus10();

    if (in_array(FastTaskData::EDIT, $stanja, true)) {
      $fastTask->setStatus(FastTaskData::EDIT);
    } else {
      $fastTask->setStatus(FastTaskData::OPEN);
    }

    if ($currentTime > $editTime) {
      if (is_null($fastTask->getId())) {
        $plan = $this->getEntityManager()->getRepository(Task::class)->createTasksFromList($fastTask, $this->getEntityManager()->getRepository(User::class)->find(1));
        $timetable = $this->getEntityManager()->getRepository(FastTask::class)->getTimetableByFastTasks($plan);
        $datum = $plan->getDatum();
        $users= $this->getEntityManager()->getRepository(FastTask::class)->getUsersForEmail($plan, FastTaskData::SAVED);
        $this->mail->plan($timetable, $users, $datum);
      }
    }

    $this->save($fastTask);
    if ($status == FastTaskData::EDIT) {
      $this->getEntityManager()->getRepository(Task::class)->createTasksFromListEdited($fastTask, $this->getEntityManager()->getRepository(User::class)->find(1));
    }
    return $fastTask;
  }


  public function save(FastTask $fastTask): FastTask {
    if (is_null($fastTask->getId())) {
      $this->getEntityManager()->persist($fastTask);
    }

    $this->getEntityManager()->flush();
    return $fastTask;
  }

  public function remove(FastTask $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): FastTask {
    if (empty($id)) {
      return new FastTask();
    }
    return $this->getEntityManager()->getRepository(FastTask::class)->find($id);

  }

  public function getDisabledDates(): array {

    $dates = [];
    $qb = $this->createQueryBuilder('f');
    $qb
      ->andWhere($qb->expr()->orX(
        $qb->expr()->eq('f.status', ':status2'),
        $qb->expr()->eq('f.status', ':status3'),
        $qb->expr()->eq('f.status', ':status4')
      ))
      ->setParameter('status2', FastTaskData::OPEN)
      ->setParameter('status3', FastTaskData::SAVED)
      ->setParameter('status4', FastTaskData::EDIT);


    $query = $qb->getQuery();
    $fastTasks = $query->getResult();

    if (!empty($fastTasks)) {
      foreach ($fastTasks as $task) {
        $dates[] = $task->getDatum()->format('d.m.Y');
      }
    }
    return $dates;
  }

//    /**
//     * @return FastTask[] Returns an array of FastTask objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FastTask
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
