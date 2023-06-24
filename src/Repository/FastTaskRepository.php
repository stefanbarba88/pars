<?php

namespace App\Repository;

use App\Classes\Data\FastTaskData;
use App\Entity\Activity;
use App\Entity\Car;
use App\Entity\FastTask;
use App\Entity\Project;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FastTask>
 *
 * @method FastTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method FastTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method FastTask[]    findAll()
 * @method FastTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FastTaskRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, FastTask::class);
  }

  public function getTimeTable(): array {

    $today = new DateTimeImmutable(); // Trenutni datum i vrijeme
    $startDate = $today->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $today->format('Y-m-d 23:59:59'); // Kraj dana

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


        $car1 = [];
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo11()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo11()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo11())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo21()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo21()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo21())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo31()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo31()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo31())->getCar()]);
          }
        }

        $car2 = [];
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo12()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo12()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo12())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo22()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo22()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo22())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo32()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo32()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo32())->getCar()]);
          }
        }

        $car3 = [];
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo13()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo13()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo13())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo23()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo23()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo23())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo33()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo33()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo33())->getCar()]);
          }
        }

        $car4 = [];
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo14()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo14()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo14())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo24()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo24()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo24())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo34()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo34()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo34())->getCar()]);
          }
        }

        $car5 = [];
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo15()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo15()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo15())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo25()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo25()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo25())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo35()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo35()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo35())->getCar()]);
          }
        }

        $car6 = [];
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo16()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo16()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo16())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo26()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo26()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo26())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo36()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo36()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo36())->getCar()]);
          }
        }

        $car7 = [];
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo17()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo17()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo17())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo27()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo27()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo27())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo37()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo37()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo37())->getCar()]);
          }
        }

        $car8 = [];
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo18()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo18()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo18())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo28()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo28()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo28())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo38()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo38()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo38())->getCar()]);
          }
        }

        $car9 = [];
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo19()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo19()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo19())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo29()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo29()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo29())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo39()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo39()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo39())->getCar()]);
          }
        }

        $car10 = [];
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo110()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo110()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo110())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo210()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo210()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo210())->getCar()]);
          }
        }
        if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo310()]))) {
          if (!is_null($this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo310()])->getCar())) {
            $car1 = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $this->getEntityManager()->getRepository(User::class)->find($task->getGeo310())->getCar()]);
          }
        }


        $tasks = [
            [
              'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject1()]),
              'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo11()]),
              'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo21()]),
              'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo31()]),
              'aktivnosti' => $activity1,
              'oprema' => $task->getOprema1(),
              'napomena' => $task->getDescription1(),
              'vozilo' => $car1,
            ],
            [
              'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject2()]),
              'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo12()]),
              'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo22()]),
              'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo32()]),
              'aktivnosti' => $activity2,
              'oprema' => $task->getOprema2(),
              'napomena' => $task->getDescription2(),
              'vozilo' => $car2,
            ],
            [
              'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject3()]),
              'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo13()]),
              'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo23()]),
              'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo33()]),
              'aktivnosti' => $activity3,
              'oprema' => $task->getOprema3(),
              'napomena' => $task->getDescription3(),
              'vozilo' => $car3,
            ],
            [
              'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject4()]),
              'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo14()]),
              'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo24()]),
              'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo34()]),
              'aktivnosti' => $activity4,
              'oprema' => $task->getOprema4(),
              'napomena' => $task->getDescription4(),
              'vozilo' => $car4,
            ],
            [
              'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject5()]),
              'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo15()]),
              'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo25()]),
              'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo35()]),
              'aktivnosti' => $activity5,
              'oprema' => $task->getOprema5(),
              'napomena' => $task->getDescription5(),
              'vozilo' => $car5,
            ],
            [
              'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject6()]),
              'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo16()]),
              'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo26()]),
              'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo36()]),
              'aktivnosti' => $activity6,
              'oprema' => $task->getOprema6(),
              'napomena' => $task->getDescription6(),
              'vozilo' => $car6,
            ],
            [
              'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject7()]),
              'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo17()]),
              'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo27()]),
              'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo37()]),
              'aktivnosti' => $activity7,
              'oprema' => $task->getOprema7(),
              'napomena' => $task->getDescription7(),
              'vozilo' => $car7,
            ],
            [
              'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject8()]),
              'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo18()]),
              'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo28()]),
              'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo38()]),
              'aktivnosti' => $activity8,
              'oprema' => $task->getOprema8(),
              'napomena' => $task->getDescription8(),
              'vozilo' => $car8,
            ],
            [
              'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject9()]),
              'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo19()]),
              'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo29()]),
              'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo39()]),
              'aktivnosti' => $activity9,
              'oprema' => $task->getOprema9(),
              'napomena' => $task->getDescription9(),
              'vozilo' => $car9,
            ],
            [
              'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getProject10()]),
              'geo1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo110()]),
              'geo2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getGeo210()]),
              'geo3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>$task->getGeo310()]),
              'aktivnosti' => $activity10,
              'oprema' => $task->getOprema10(),
              'napomena' => $task->getDescription10(),
              'vozilo' => $car10,
            ]
          ];
      }
    }

    return $tasks;
  }

  public function saveFastTask(FastTask $fastTask, array $data): FastTask {


    $datum = $data['task_quick_form_datum'];
    $format = "d.m.Y H:i:s";
    $dateTime = DateTimeImmutable::createFromFormat($format, $datum . '15:00:00');
    $currentTime = new DateTimeImmutable();

//    $currentTime = DateTimeImmutable::createFromFormat($format, '24.6.2023 15:00:00');

    if ($currentTime > $dateTime) {
      $fastTask->setStatus(FastTaskData::EDIT);
    } else {
      $fastTask->setStatus(FastTaskData::OPEN);
    }

    $fastTask->setDatum($dateTime);
    $noTasks = 0;

    $task1 = $data['task_quick_form1'];
    if (!is_null($fastTask->getId())) {

      $proj1 = $task1['projekat'];
      if ($proj1 == '---') {
        $proj1 = null;
      }
      $geo11 = $task1['geo'][0];
      if ($geo11 == '---') {
        $geo11 = null;
      }
      $geo21 = $task1['geo'][1];
      if ($geo21 == '---') {
        $geo21 = null;
      }
      $geo31 = $task1['geo'][2];
      if ($geo31 == '---') {
        $geo31 = null;
      }

      if ($fastTask->getProject1() != $proj1 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus1(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo11() != $geo11 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus1(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo21() != $geo21 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus1(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo31() != $geo31 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus1(FastTaskData::EDIT);
      }
      if (isset($task1['aktivnosti']) && $fastTask->getActivity1() != $task1['aktivnosti'] && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus1(FastTaskData::EDIT);
      }
      if ($fastTask->getOprema1() != trim($task1['oprema']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus1(FastTaskData::EDIT);
      }
      if ($fastTask->getDescription1() != trim($task1['napomena']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus1(FastTaskData::EDIT);
      }

    }
    if (($task1['projekat']) !== '---') {
      $noTasks++;
      $fastTask->setProject1($task1['projekat']);
      if (($task1['geo'][0]) !== '---') {
        $fastTask->setGeo11($task1['geo'][0]);
      }
      if (($task1['geo'][1]) !== '---') {
        $fastTask->setGeo21($task1['geo'][1]);
      }
      if (($task1['geo'][2]) !== '---') {
        $fastTask->setGeo31($task1['geo'][2]);
      }

      if (isset($task1['aktivnosti'])) {
        $fastTask->setActivity1($task1['aktivnosti']);
      }

      if (!empty($task1['oprema'])) {
        $fastTask->setOprema1($task1['oprema']);
      }

      if (!empty($task1['napomena'])) {
        $fastTask->setDescription1($task1['napomena']);
      }

    }

    $task2 = $data['task_quick_form2'];
    if (!is_null($fastTask->getId())) {
      $proj2 = $task2['projekat'];
      if ($proj2 == '---') {
        $proj2 = null;
      }
      $geo12 = $task2['geo'][0];
      if ($geo12 == '---') {
        $geo12 = null;
      }
      $geo22 = $task2['geo'][1];
      if ($geo22 == '---') {
        $geo22 = null;
      }
      $geo32 = $task2['geo'][2];
      if ($geo32 == '---') {
        $geo32 = null;
      }

      if ($fastTask->getProject2() != $proj2 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus2(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo12() != $geo12 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus2(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo22() != $geo22 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus2(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo32() != $geo32 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus2(FastTaskData::EDIT);
      }
      if (isset($task2['aktivnosti']) && $fastTask->getActivity2() != $task2['aktivnosti'] && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus2(FastTaskData::EDIT);
      }
      if ($fastTask->getOprema2() != trim($task2['oprema']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus2(FastTaskData::EDIT);
      }
      if ($fastTask->getDescription2() != trim($task2['napomena']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus2(FastTaskData::EDIT);
      }

    }
    if (($task2['projekat']) !== '---') {
      $noTasks++;
      $fastTask->setProject2($task2['projekat']);
      if (($task2['geo'][0]) !== '---') {
        $fastTask->setGeo12($task2['geo'][0]);
      }
      if (($task2['geo'][1]) !== '---') {
        $fastTask->setGeo22($task2['geo'][1]);
      }
      if (($task2['geo'][2]) !== '---') {
        $fastTask->setGeo32($task2['geo'][2]);
      }

      if (isset($task2['aktivnosti'])) {
        $fastTask->setActivity2($task2['aktivnosti']);
      }

      if (!empty($task2['oprema'])) {
        $fastTask->setOprema2($task2['oprema']);
      }

      if (!empty($task2['napomena'])) {
        $fastTask->setDescription2($task2['napomena']);
      }
    }

    $task3 = $data['task_quick_form3'];
    if (!is_null($fastTask->getId())) {
      $proj3 = $task3['projekat'];
      if ($proj3 == '---') {
        $proj3 = null;
      }
      $geo13 = $task3['geo'][0];
      if ($geo13 == '---') {
        $geo13 = null;
      }
      $geo23 = $task3['geo'][1];
      if ($geo23 == '---') {
        $geo23 = null;
      }
      $geo33 = $task3['geo'][2];
      if ($geo33 == '---') {
        $geo33 = null;
      }

      if ($fastTask->getProject3() != $proj3 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus3(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo13() != $geo13 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus3(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo23() != $geo23 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus3(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo33() != $geo33 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus3(FastTaskData::EDIT);
      }
      if (isset($task3['aktivnosti']) && $fastTask->getActivity3() != $task3['aktivnosti'] && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus3(FastTaskData::EDIT);
      }
      if ($fastTask->getOprema3() != trim($task3['oprema']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus3(FastTaskData::EDIT);
      }
      if ($fastTask->getDescription3() != trim($task3['napomena']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus3(FastTaskData::EDIT);
      }

    }
    if (($task3['projekat']) !== '---') {
      $noTasks++;
      $fastTask->setProject3($task3['projekat']);
      if (($task3['geo'][0]) !== '---') {
        $fastTask->setGeo13($task3['geo'][0]);
      }
      if (($task3['geo'][1]) !== '---') {
        $fastTask->setGeo23($task3['geo'][1]);
      }
      if (($task3['geo'][2]) !== '---') {
        $fastTask->setGeo33($task3['geo'][2]);
      }

      if (isset($task3['aktivnosti'])) {
        $fastTask->setActivity3($task3['aktivnosti']);
      }

      if (!empty($task3['oprema'])) {
        $fastTask->setOprema3($task3['oprema']);
      }

      if (!empty($task3['napomena'])) {
        $fastTask->setDescription3($task3['napomena']);
      }
    }

    $task4 = $data['task_quick_form4'];
    if (!is_null($fastTask->getId())) {
      $proj4 = $task4['projekat'];
      if ($proj4 == '---') {
        $proj4 = null;
      }
      $geo14 = $task4['geo'][0];
      if ($geo14 == '---') {
        $geo14 = null;
      }
      $geo24 = $task4['geo'][1];
      if ($geo24 == '---') {
        $geo24 = null;
      }
      $geo34 = $task4['geo'][2];
      if ($geo34 == '---') {
        $geo34 = null;
      }

      if ($fastTask->getProject4() != $proj4 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus4(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo14() != $geo14 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus4(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo24() != $geo24 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus4(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo34() != $geo34 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus4(FastTaskData::EDIT);
      }
      if (isset($task4['aktivnosti']) && $fastTask->getActivity4() != $task4['aktivnosti'] && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus4(FastTaskData::EDIT);
      }
      if ($fastTask->getOprema4() != trim($task4['oprema']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus4(FastTaskData::EDIT);
      }
      if ($fastTask->getDescription4() != trim($task4['napomena']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus4(FastTaskData::EDIT);
      }

    }
    if (($task4['projekat']) !== '---') {
      $noTasks++;
      $fastTask->setProject4($task4['projekat']);
      if (($task4['geo'][0]) !== '---') {
        $fastTask->setGeo14($task4['geo'][0]);
      }
      if (($task4['geo'][1]) !== '---') {
        $fastTask->setGeo24($task4['geo'][1]);
      }
      if (($task4['geo'][2]) !== '---') {
        $fastTask->setGeo34($task4['geo'][2]);
      }

      if (isset($task4['aktivnosti'])) {
        $fastTask->setActivity4($task4['aktivnosti']);
      }

      if (!empty($task4['oprema'])) {
        $fastTask->setOprema4($task4['oprema']);
      }

      if (!empty($task4['napomena'])) {
        $fastTask->setDescription4($task4['napomena']);
      }
    }

    $task5 = $data['task_quick_form5'];
    if (!is_null($fastTask->getId())) {
      $proj5 = $task5['projekat'];
      if ($proj5 == '---') {
        $proj5 = null;
      }
      $geo15 = $task5['geo'][0];
      if ($geo15 == '---') {
        $geo15 = null;
      }
      $geo25 = $task5['geo'][1];
      if ($geo25 == '---') {
        $geo25 = null;
      }
      $geo35 = $task5['geo'][2];
      if ($geo35 == '---') {
        $geo35 = null;
      }

      if ($fastTask->getProject5() != $proj5 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus5(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo15() != $geo15 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus5(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo25() != $geo25 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus5(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo35() != $geo35 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus5(FastTaskData::EDIT);
      }
      if (isset($task5['aktivnosti']) && $fastTask->getActivity5() != $task5['aktivnosti'] && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus5(FastTaskData::EDIT);
      }
      if ($fastTask->getOprema5() != trim($task5['oprema']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus5(FastTaskData::EDIT);
      }
      if ($fastTask->getDescription5() != trim($task5['napomena']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus5(FastTaskData::EDIT);
      }

    }
    if (($task5['projekat']) !== '---') {
      $noTasks++;
      $fastTask->setProject5($task5['projekat']);
      if (($task5['geo'][0]) !== '---') {
        $fastTask->setGeo15($task5['geo'][0]);
      }
      if (($task5['geo'][1]) !== '---') {
        $fastTask->setGeo25($task5['geo'][1]);
      }
      if (($task5['geo'][2]) !== '---') {
        $fastTask->setGeo35($task5['geo'][2]);
      }

      if (isset($task5['aktivnosti'])) {
        $fastTask->setActivity5($task5['aktivnosti']);
      }

      if (!empty($task5['oprema'])) {
        $fastTask->setOprema5($task5['oprema']);
      }

      if (!empty($task5['napomena'])) {
        $fastTask->setDescription5($task5['napomena']);
      }
    }

    $task6 = $data['task_quick_form6'];
    if (!is_null($fastTask->getId())) {
      $proj6 = $task6['projekat'];
      if ($proj6 == '---') {
        $proj6 = null;
      }
      $geo16 = $task6['geo'][0];
      if ($geo16 == '---') {
        $geo16 = null;
      }
      $geo26 = $task6['geo'][1];
      if ($geo26 == '---') {
        $geo26 = null;
      }
      $geo36 = $task6['geo'][2];
      if ($geo36 == '---') {
        $geo36 = null;
      }

      if ($fastTask->getProject6() != $proj6 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus6(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo16() != $geo16 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus6(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo26() != $geo26 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus6(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo36() != $geo36 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus6(FastTaskData::EDIT);
      }
      if (isset($task6['aktivnosti']) && $fastTask->getActivity6() != $task6['aktivnosti'] && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus6(FastTaskData::EDIT);
      }
      if ($fastTask->getOprema6() != trim($task6['oprema']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus6(FastTaskData::EDIT);
      }
      if ($fastTask->getDescription6() != trim($task6['napomena']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus6(FastTaskData::EDIT);
      }

    }
    if (($task6['projekat']) !== '---') {
      $noTasks++;
      $fastTask->setProject6($task6['projekat']);
      if (($task6['geo'][0]) !== '---') {
        $fastTask->setGeo16($task6['geo'][0]);
      }
      if (($task6['geo'][1]) !== '---') {
        $fastTask->setGeo26($task6['geo'][1]);
      }
      if (($task6['geo'][2]) !== '---') {
        $fastTask->setGeo36($task6['geo'][2]);
      }

      if (isset($task6['aktivnosti'])) {
        $fastTask->setActivity6($task6['aktivnosti']);
      }

      if (!empty($task6['oprema'])) {
        $fastTask->setOprema6($task6['oprema']);
      }

      if (!empty($task6['napomena'])) {
        $fastTask->setDescription6($task6['napomena']);
      }
    }

    $task7 = $data['task_quick_form7'];
    if (!is_null($fastTask->getId())) {
      $proj7 = $task7['projekat'];
      if ($proj7 == '---') {
        $proj7 = null;
      }
      $geo17 = $task7['geo'][0];
      if ($geo17 == '---') {
        $geo17 = null;
      }
      $geo27 = $task7['geo'][1];
      if ($geo27 == '---') {
        $geo27 = null;
      }
      $geo37 = $task7['geo'][2];
      if ($geo37 == '---') {
        $geo37 = null;
      }

      if ($fastTask->getProject7() != $proj7 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus7(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo17() != $geo17 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus7(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo27() != $geo27 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus7(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo37() != $geo37 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus7(FastTaskData::EDIT);
      }
      if (isset($task7['aktivnosti']) && $fastTask->getActivity7() != $task7['aktivnosti'] && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus7(FastTaskData::EDIT);
      }
      if ($fastTask->getOprema7() != trim($task7['oprema']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus7(FastTaskData::EDIT);
      }
      if ($fastTask->getDescription7() != trim($task7['napomena']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus7(FastTaskData::EDIT);
      }

    }
    if (($task7['projekat']) !== '---') {
      $noTasks++;
      $fastTask->setProject7($task7['projekat']);
      if (($task7['geo'][0]) !== '---') {
        $fastTask->setGeo17($task7['geo'][0]);
      }
      if (($task7['geo'][1]) !== '---') {
        $fastTask->setGeo27($task7['geo'][1]);
      }
      if (($task7['geo'][2]) !== '---') {
        $fastTask->setGeo37($task7['geo'][2]);
      }

      if (isset($task7['aktivnosti'])) {
        $fastTask->setActivity7($task7['aktivnosti']);
      }

      if (!empty($task7['oprema'])) {
        $fastTask->setOprema7($task7['oprema']);
      }

      if (!empty($task7['napomena'])) {
        $fastTask->setDescription7($task7['napomena']);
      }
    }

    $task8 = $data['task_quick_form8'];
    if (!is_null($fastTask->getId())) {
      $proj8 = $task8['projekat'];
      if ($proj8 == '---') {
        $proj8 = null;
      }
      $geo18 = $task8['geo'][0];
      if ($geo18 == '---') {
        $geo18 = null;
      }
      $geo28 = $task8['geo'][1];
      if ($geo28 == '---') {
        $geo28 = null;
      }
      $geo38 = $task8['geo'][2];
      if ($geo38 == '---') {
        $geo38 = null;
      }

      if ($fastTask->getProject8() != $proj8 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus8(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo18() != $geo18 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus8(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo28() != $geo28 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus8(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo38() != $geo38 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus8(FastTaskData::EDIT);
      }
      if (isset($task8['aktivnosti']) && $fastTask->getActivity8() != $task8['aktivnosti'] && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus8(FastTaskData::EDIT);
      }
      if ($fastTask->getOprema8() != trim($task8['oprema']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus8(FastTaskData::EDIT);
      }
      if ($fastTask->getDescription8() != trim($task8['napomena']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus8(FastTaskData::EDIT);
      }

    }
    if (($task8['projekat']) !== '---') {
      $noTasks++;
      $fastTask->setProject8($task8['projekat']);
      if (($task8['geo'][0]) !== '---') {
        $fastTask->setGeo18($task8['geo'][0]);
      }
      if (($task8['geo'][1]) !== '---') {
        $fastTask->setGeo28($task8['geo'][1]);
      }
      if (($task8['geo'][2]) !== '---') {
        $fastTask->setGeo38($task8['geo'][2]);
      }

      if (isset($task8['aktivnosti'])) {
        $fastTask->setActivity8($task8['aktivnosti']);
      }

      if (!empty($task8['oprema'])) {
        $fastTask->setOprema8($task8['oprema']);
      }

      if (!empty($task8['napomena'])) {
        $fastTask->setDescription8($task8['napomena']);
      }
    }

    $task9 = $data['task_quick_form9'];
    if (!is_null($fastTask->getId())) {
      $proj9 = $task9['projekat'];
      if ($proj9 == '---') {
        $proj9 = null;
      }
      $geo19 = $task9['geo'][0];
      if ($geo19 == '---') {
        $geo19 = null;
      }
      $geo29 = $task9['geo'][1];
      if ($geo29 == '---') {
        $geo29 = null;
      }
      $geo39 = $task9['geo'][2];
      if ($geo39 == '---') {
        $geo39 = null;
      }

      if ($fastTask->getProject9() != $proj9 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus9(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo19() != $geo19 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus9(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo29() != $geo29 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus9(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo39() != $geo39 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus9(FastTaskData::EDIT);
      }
      if (isset($task9['aktivnosti']) && $fastTask->getActivity9() != $task9['aktivnosti'] && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus9(FastTaskData::EDIT);
      }
      if ($fastTask->getOprema9() != trim($task9['oprema']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus9(FastTaskData::EDIT);
      }
      if ($fastTask->getDescription9() != trim($task9['napomena']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus9(FastTaskData::EDIT);
      }

    }
    if (($task9['projekat']) !== '---') {
      $noTasks++;
      $fastTask->setProject9($task9['projekat']);
      if (($task9['geo'][0]) !== '---') {
        $fastTask->setGeo19($task9['geo'][0]);
      }
      if (($task9['geo'][1]) !== '---') {
        $fastTask->setGeo29($task9['geo'][1]);
      }
      if (($task9['geo'][2]) !== '---') {
        $fastTask->setGeo39($task9['geo'][2]);
      }

      if (isset($task9['aktivnosti'])) {
        $fastTask->setActivity9($task9['aktivnosti']);
      }

      if (!empty($task9['oprema'])) {
        $fastTask->setOprema9($task9['oprema']);
      }

      if (!empty($task9['napomena'])) {
        $fastTask->setDescription9($task9['napomena']);
      }
    }

    $task10 = $data['task_quick_form10'];
    if (!is_null($fastTask->getId())) {
      $proj10 = $task10['projekat'];
      if ($proj10 == '---') {
        $proj10 = null;
      }
      $geo110 = $task10['geo'][0];
      if ($geo110 == '---') {
        $geo110 = null;
      }
      $geo210 = $task10['geo'][1];
      if ($geo210 == '---') {
        $geo210 = null;
      }
      $geo310 = $task10['geo'][2];
      if ($geo310 == '---') {
        $geo310 = null;
      }

      if ($fastTask->getProject10() != $proj10 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus10(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo110() != $geo110 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus10(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo210() != $geo210 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus10(FastTaskData::EDIT);
      }
      if ($fastTask->getGeo310() != $geo310 && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus10(FastTaskData::EDIT);
      }
      if (isset($task10['aktivnosti']) && $fastTask->getActivity10() != $task10['aktivnosti'] && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus10(FastTaskData::EDIT);
      }
      if ($fastTask->getOprema10() != trim($task10['oprema']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus10(FastTaskData::EDIT);
      }
      if ($fastTask->getDescription10() != trim($task10['napomena']) && $fastTask->getStatus() == FastTaskData::EDIT) {
        $fastTask->setStatus10(FastTaskData::EDIT);
      }

    }
    if (($task10['projekat']) !== '---') {
      $noTasks++;
      $fastTask->setProject10($task10['projekat']);
      if (($task10['geo'][0]) !== '---') {
        $fastTask->setGeo110($task10['geo'][0]);
      }
      if (($task10['geo'][1]) !== '---') {
        $fastTask->setGeo210($task10['geo'][1]);
      }
      if (($task10['geo'][2]) !== '---') {
        $fastTask->setGeo310($task10['geo'][2]);
      }

      if (isset($task10['aktivnosti'])) {
        $fastTask->setActivity10($task10['aktivnosti']);
      }

      if (!empty($task10['oprema'])) {
        $fastTask->setOprema10($task10['oprema']);
      }

      if (!empty($task10['napomena'])) {
        $fastTask->setDescription10($task10['napomena']);
      }
    }

    $fastTask->setNoTasks($noTasks);

    return $this->save($fastTask);
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
