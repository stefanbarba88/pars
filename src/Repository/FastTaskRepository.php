<?php

namespace App\Repository;

use App\Classes\Data\FastTaskData;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\Car;
use App\Entity\Company;
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
use Symfony\Bundle\SecurityBundle\Security;
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
  private $security;
  public function __construct(ManagerRegistry $registry, MailService $mail, Security $security) {
    parent::__construct($registry, FastTask::class);
    $this->mail = $mail;
    $this->security = $security;
  }

  public function makeView(FastTask $fastTask): array {

    $fastTaskView = [];
    $fastTaskViewZamene = [];

    if (!is_null($fastTask->getProject1())) {

      $oprema1 = [];
      $aktivnosti1 = [];

      if (!is_null($fastTask->getOprema1())) {
        foreach ($fastTask->getOprema1() as $opr1) {
          $oprema1[] = $this->getEntityManager()->getRepository(Tool::class)->findOneBy(['id' => $opr1]);
        }
      }

      if (!is_null($fastTask->getActivity1())) {
        foreach ($fastTask->getActivity1() as $akt1) {
          $aktivnosti1[] = $this->getEntityManager()->getRepository(Activity::class)->findOneBy(['id' => $akt1]);
        }
      }



      $fastTaskView[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getProject1()]),
        'vreme' => $fastTask->getTime1(),
        'zaposleni1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo11()]),
        'zaposleni2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo21()]),
        'zaposleni3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>  $fastTask->getGeo31()]),
        'vozilo' => $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $fastTask->getCar1()]),
        'vozac' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getDriver1()]),
        'naplativ' => $fastTask->getFree1(),
        'napomena' => $fastTask->getDescription1(),
        'status' => $fastTask->getStatus1(),
        'aktivnosti' => $aktivnosti1,
        'oprema' => $oprema1,
      ];

    }
    if (!is_null($fastTask->getProject2())) {

      $oprema2 = [];
      $aktivnosti2 = [];

      if (!is_null($fastTask->getOprema2())) {
        foreach ($fastTask->getOprema2() as $opr2) {
          $oprema2[] = $this->getEntityManager()->getRepository(Tool::class)->findOneBy(['id' => $opr2]);
        }
      }

      if (!is_null($fastTask->getActivity2())) {
        foreach ($fastTask->getActivity2() as $akt2) {
          $aktivnosti2[] = $this->getEntityManager()->getRepository(Activity::class)->findOneBy(['id' => $akt2]);
        }
      }

      $fastTaskView[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getProject2()]),
        'vreme' => $fastTask->getTime2(),
        'zaposleni1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo12()]),
        'zaposleni2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo22()]),
        'zaposleni3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>  $fastTask->getGeo32()]),
        'vozilo' => $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $fastTask->getCar2()]),
        'vozac' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getDriver2()]),
        'naplativ' => $fastTask->getFree2(),
        'napomena' => $fastTask->getDescription2(),
        'status' => $fastTask->getStatus2(),
        'aktivnosti' => $aktivnosti2,
        'oprema' => $oprema2,
      ];

    }
    if (!is_null($fastTask->getProject3())) {

      $oprema3 = [];
      $aktivnosti3 = [];

      if (!is_null($fastTask->getOprema3())) {
        foreach ($fastTask->getOprema3() as $opr3) {
          $oprema3[] = $this->getEntityManager()->getRepository(Tool::class)->findOneBy(['id' => $opr3]);
        }
      }

      if (!is_null($fastTask->getActivity3())) {
        foreach ($fastTask->getActivity3() as $akt3) {
          $aktivnosti3[] = $this->getEntityManager()->getRepository(Activity::class)->findOneBy(['id' => $akt3]);
        }
      }

      $fastTaskView[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getProject3()]),
        'vreme' => $fastTask->getTime3(),
        'zaposleni1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo13()]),
        'zaposleni2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo23()]),
        'zaposleni3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>  $fastTask->getGeo33()]),
        'vozilo' => $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $fastTask->getCar3()]),
        'vozac' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getDriver3()]),
        'naplativ' => $fastTask->getFree3(),
        'napomena' => $fastTask->getDescription3(),
        'status' => $fastTask->getStatus3(),
        'aktivnosti' => $aktivnosti3,
        'oprema' => $oprema3,
      ];

    }
    if (!is_null($fastTask->getProject4())) {

      $oprema4 = [];
      $aktivnosti4 = [];

      if (!is_null($fastTask->getOprema4())) {
        foreach ($fastTask->getOprema4() as $opr4) {
          $oprema4[] = $this->getEntityManager()->getRepository(Tool::class)->findOneBy(['id' => $opr4]);
        }
      }

      if (!is_null($fastTask->getActivity4())) {
        foreach ($fastTask->getActivity4() as $akt4) {
          $aktivnosti4[] = $this->getEntityManager()->getRepository(Activity::class)->findOneBy(['id' => $akt4]);
        }
      }

      $fastTaskView[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getProject4()]),
        'vreme' => $fastTask->getTime4(),
        'zaposleni1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo14()]),
        'zaposleni2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo24()]),
        'zaposleni3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>  $fastTask->getGeo34()]),
        'vozilo' => $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $fastTask->getCar4()]),
        'vozac' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getDriver4()]),
        'naplativ' => $fastTask->getFree4(),
        'napomena' => $fastTask->getDescription4(),
        'status' => $fastTask->getStatus4(),
        'aktivnosti' => $aktivnosti4,
        'oprema' => $oprema4,
      ];

    }
    if (!is_null($fastTask->getProject5())) {

      $oprema5 = [];
      $aktivnosti5 = [];

      if (!is_null($fastTask->getOprema5())) {
        foreach ($fastTask->getOprema5() as $opr5) {
          $oprema5[] = $this->getEntityManager()->getRepository(Tool::class)->findOneBy(['id' => $opr5]);
        }
      }

      if (!is_null($fastTask->getActivity5())) {
        foreach ($fastTask->getActivity5() as $akt5) {
          $aktivnosti5[] = $this->getEntityManager()->getRepository(Activity::class)->findOneBy(['id' => $akt5]);
        }
      }

      $fastTaskView[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getProject5()]),
        'vreme' => $fastTask->getTime5(),
        'zaposleni1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo15()]),
        'zaposleni2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo25()]),
        'zaposleni3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>  $fastTask->getGeo35()]),
        'vozilo' => $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $fastTask->getCar5()]),
        'vozac' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getDriver5()]),
        'naplativ' => $fastTask->getFree5(),
        'napomena' => $fastTask->getDescription5(),
        'status' => $fastTask->getStatus5(),
        'aktivnosti' => $aktivnosti5,
        'oprema' => $oprema5,
      ];

    }
    if (!is_null($fastTask->getProject6())) {

      $oprema6 = [];
      $aktivnosti6 = [];

      if (!is_null($fastTask->getOprema6())) {
        foreach ($fastTask->getOprema6() as $opr6) {
          $oprema6[] = $this->getEntityManager()->getRepository(Tool::class)->findOneBy(['id' => $opr6]);
        }
      }

      if (!is_null($fastTask->getActivity6())) {
        foreach ($fastTask->getActivity6() as $akt6) {
          $aktivnosti6[] = $this->getEntityManager()->getRepository(Activity::class)->findOneBy(['id' => $akt6]);
        }
      }

      $fastTaskView[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getProject6()]),
        'vreme' => $fastTask->getTime6(),
        'zaposleni1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo16()]),
        'zaposleni2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo26()]),
        'zaposleni3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>  $fastTask->getGeo36()]),
        'vozilo' => $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $fastTask->getCar6()]),
        'vozac' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getDriver6()]),
        'naplativ' => $fastTask->getFree6(),
        'napomena' => $fastTask->getDescription6(),
        'status' => $fastTask->getStatus6(),
        'aktivnosti' => $aktivnosti6,
        'oprema' => $oprema6,
      ];

    }
    if (!is_null($fastTask->getProject7())) {

      $oprema7 = [];
      $aktivnosti7 = [];

      if (!is_null($fastTask->getOprema7())) {
        foreach ($fastTask->getOprema7() as $opr7) {
          $oprema7[] = $this->getEntityManager()->getRepository(Tool::class)->findOneBy(['id' => $opr7]);
        }
      }

      if (!is_null($fastTask->getActivity7())) {
        foreach ($fastTask->getActivity7() as $akt7) {
          $aktivnosti7[] = $this->getEntityManager()->getRepository(Activity::class)->findOneBy(['id' => $akt7]);
        }
      }
      $fastTaskView[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getProject7()]),
        'vreme' => $fastTask->getTime7(),
        'zaposleni1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo17()]),
        'zaposleni2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo27()]),
        'zaposleni3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>  $fastTask->getGeo37()]),
        'vozilo' => $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $fastTask->getCar7()]),
        'vozac' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getDriver7()]),
        'naplativ' => $fastTask->getFree7(),
        'napomena' => $fastTask->getDescription7(),
        'status' => $fastTask->getStatus7(),
        'aktivnosti' => $aktivnosti7,
        'oprema' => $oprema7,
      ];

    }
    if (!is_null($fastTask->getProject8())) {

      $oprema8 = [];
      $aktivnosti8 = [];

      if (!is_null($fastTask->getOprema8())) {
        foreach ($fastTask->getOprema8() as $opr8) {
          $oprema8[] = $this->getEntityManager()->getRepository(Tool::class)->findOneBy(['id' => $opr8]);
        }
      }

      if (!is_null($fastTask->getActivity8())) {
        foreach ($fastTask->getActivity8() as $akt8) {
          $aktivnosti8[] = $this->getEntityManager()->getRepository(Activity::class)->findOneBy(['id' => $akt8]);
        }
      }

      $fastTaskView[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getProject8()]),
        'vreme' => $fastTask->getTime8(),
        'zaposleni1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo18()]),
        'zaposleni2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo28()]),
        'zaposleni3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>  $fastTask->getGeo38()]),
        'vozilo' => $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $fastTask->getCar8()]),
        'vozac' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getDriver8()]),
        'naplativ' => $fastTask->getFree8(),
        'napomena' => $fastTask->getDescription8(),
        'status' => $fastTask->getStatus8(),
        'aktivnosti' => $aktivnosti8,
        'oprema' => $oprema8,
      ];

    }
    if (!is_null($fastTask->getProject9())) {

      $oprema9 = [];
      $aktivnosti9 = [];

      if (!is_null($fastTask->getOprema9())) {
        foreach ($fastTask->getOprema9() as $opr9) {
          $oprema9[] = $this->getEntityManager()->getRepository(Tool::class)->findOneBy(['id' => $opr9]);
        }
      }

      if (!is_null($fastTask->getActivity9())) {
        foreach ($fastTask->getActivity9() as $akt9) {
          $aktivnosti9[] = $this->getEntityManager()->getRepository(Activity::class)->findOneBy(['id' => $akt9]);
        }
      }

      $fastTaskView[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getProject9()]),
        'vreme' => $fastTask->getTime9(),
        'zaposleni1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo19()]),
        'zaposleni2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo29()]),
        'zaposleni3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>  $fastTask->getGeo39()]),
        'vozilo' => $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $fastTask->getCar9()]),
        'vozac' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getDriver9()]),
        'naplativ' => $fastTask->getFree9(),
        'napomena' => $fastTask->getDescription9(),
        'status' => $fastTask->getStatus9(),
        'aktivnosti' => $aktivnosti9,
        'oprema' => $oprema9,
      ];

    }
    if (!is_null($fastTask->getProject10())) {

      $oprema10 = [];
      $aktivnosti10 = [];

      if (!is_null($fastTask->getOprema10())) {
        foreach ($fastTask->getOprema10() as $opr10) {
          $oprema10[] = $this->getEntityManager()->getRepository(Tool::class)->findOneBy(['id' => $opr10]);
        }
      }

      if (!is_null($fastTask->getActivity10())) {
        foreach ($fastTask->getActivity10() as $akt10) {
          $aktivnosti10[] = $this->getEntityManager()->getRepository(Activity::class)->findOneBy(['id' => $akt10]);
        }
      }

      $fastTaskView[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getProject10()]),
        'vreme' => $fastTask->getTime10(),
        'zaposleni1' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo110()]),
        'zaposleni2' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getGeo210()]),
        'zaposleni3' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' =>  $fastTask->getGeo310()]),
        'vozilo' => $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $fastTask->getCar10()]),
        'vozac' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getDriver10()]),
        'naplativ' => $fastTask->getFree10(),
        'napomena' => $fastTask->getDescription10(),
        'status' => $fastTask->getStatus10(),
        'aktivnosti' => $aktivnosti10,
        'oprema' => $oprema10,
      ];

    }

    if (!is_null($fastTask->getZproject1())) {

      $fastTaskViewZamene[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getZproject1()]),
        'vreme' => $fastTask->getZtime1(),
        'zaposleni' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getZgeo1()]),
        'napomena' => $fastTask->getZdescription1(),
      ];

    }
    if (!is_null($fastTask->getZproject2())) {

      $fastTaskViewZamene[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getZproject2()]),
        'vreme' => $fastTask->getZtime2(),
        'zaposleni' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getZgeo2()]),
        'napomena' => $fastTask->getZdescription2(),
      ];

    }
    if (!is_null($fastTask->getZproject3())) {

      $fastTaskViewZamene[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getZproject3()]),
        'vreme' => $fastTask->getZtime3(),
        'zaposleni' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getZgeo3()]),
        'napomena' => $fastTask->getZdescription3(),
      ];

    }
    if (!is_null($fastTask->getZproject4())) {

      $fastTaskViewZamene[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getZproject4()]),
        'vreme' => $fastTask->getZtime4(),
        'zaposleni' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getZgeo4()]),
        'napomena' => $fastTask->getZdescription4(),
      ];

    }
    if (!is_null($fastTask->getZproject5())) {

      $fastTaskViewZamene[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getZproject5()]),
        'vreme' => $fastTask->getZtime5(),
        'zaposleni' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getZgeo5()]),
        'napomena' => $fastTask->getZdescription5(),
      ];

    }
    if (!is_null($fastTask->getZproject6())) {

      $fastTaskViewZamene[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getZproject6()]),
        'vreme' => $fastTask->getZtime6(),
        'zaposleni' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getZgeo6()]),
        'napomena' => $fastTask->getZdescription6(),
      ];

    }
    if (!is_null($fastTask->getZproject7())) {

      $fastTaskViewZamene[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getZproject7()]),
        'vreme' => $fastTask->getZtime7(),
        'zaposleni' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getZgeo7()]),
        'napomena' => $fastTask->getZdescription7(),
      ];

    }
    if (!is_null($fastTask->getZproject8())) {

      $fastTaskViewZamene[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getZproject8()]),
        'vreme' => $fastTask->getZtime8(),
        'zaposleni' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getZgeo8()]),
        'napomena' => $fastTask->getZdescription8(),
      ];

    }
    if (!is_null($fastTask->getZproject9())) {

      $fastTaskViewZamene[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getZproject9()]),
        'vreme' => $fastTask->getZtime9(),
        'zaposleni' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getZgeo9()]),
        'napomena' => $fastTask->getZdescription9(),
      ];

    }
    if (!is_null($fastTask->getZproject10())) {

      $fastTaskViewZamene[] = [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $fastTask->getZproject10()]),
        'vreme' => $fastTask->getZtime10(),
        'zaposleni' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $fastTask->getZgeo10()]),
        'napomena' => $fastTask->getZdescription10(),
      ];

    }

    return [
      'zadaci' => $fastTaskView,
      'zamene' => $fastTaskViewZamene
    ];
  }

  public function countPlanRadaActive():int {
    $company = $this->security->getUser()->getCompany();
    $noPlan = $this->createQueryBuilder('c')
      ->andWhere('c.status <> :final')
      ->andWhere('c.company = :company')
      ->setParameter(':company', $company)
      ->setParameter('final', FastTaskData::FINAL)
      ->getQuery()
      ->getResult();

    return count($noPlan);
  }

  public function findTaskInPlan(Task $task): bool {
    $company = $task->getCompany();
    $qb = $this->createQueryBuilder('t');
    $qb
      ->where('t.status = :status1')
      ->orWhere('t.status = :status2')
      ->andWhere('t.company = :company')
      ->setParameter(':company', $company)
      ->setParameter('status1', FastTaskData::SAVED)
      ->setParameter('status2', FastTaskData::EDIT);

    $query = $qb->getQuery();
    $fastTask = $query->getResult();

    if (!empty($fastTask)) {
      $plan = $fastTask[0];

      if ($plan->getTask1() == $task->getId()) {
        return true;
      }
      if ($plan->getTask2() == $task->getId()) {
        return true;
      }
      if ($plan->getTask3() == $task->getId()) {
        return true;
      }
      if ($plan->getTask4() == $task->getId()) {
        return true;
      }
      if ($plan->getTask5() == $task->getId()) {
        return true;
      }
      if ($plan->getTask6() == $task->getId()) {
        return true;
      }
      if ($plan->getTask7() == $task->getId()) {
        return true;
      }
      if ($plan->getTask8() == $task->getId()) {
        return true;
      }
      if ($plan->getTask9() == $task->getId()) {
        return true;
      }
      if ($plan->getTask10() == $task->getId()) {
        return true;
      }


    }

    return false;

  }

  public function getAllPlans(): array {
    $company = $this->security->getUser()->getCompany();
    $fastTasks = $this->getEntityManager()->getRepository(FastTask::class)->findBy(['company' => $company]);
    $plans = [];
    foreach ($fastTasks as $plan) {
      $plans[] = [ $plan, $this->getPlanStatus($plan)];
    }
    return $plans;
  }

  public function getAllPlansPaginator() {
    $company = $this->security->getUser()->getCompany();
    $qb = $this->createQueryBuilder('f');
    $qb
      ->where('f.company = :company')
      ->setParameter(':company', $company)
      ->orderBy('f.datum', 'DESC');
    return $qb->getQuery();

  }

  public function getPlanStatus(FastTask $plan): int {

    $status = 0;
    $datum = $plan->getDatum();
    $currentTime = new DateTimeImmutable();
    $editTime = $datum->sub(new DateInterval('PT25H'));

    if ($currentTime < $editTime) {
      $status = 1;
    }
    return $status;
  }


  public function findCarToReserve(User $user): ?Car {
    $company = $this->security->getUser()->getCompany();
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

        ->andWhere('f.company = :company')
        ->setParameter(':company', $company)
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
    $company = $this->security->getUser()->getCompany();
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
        ->andWhere('f.company = :company')
        ->setParameter(':company', $company)
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
    $company = $this->security->getUser()->getCompany();
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
        ->andWhere('f.company = :company')
        ->setParameter(':company', $company)
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
    $company = $this->security->getUser()->getCompany();
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
        ->andWhere('f.company = :company')
        ->setParameter(':company', $company)
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
  public function getSubsByFastTasks(FastTask $task): array {

    $subs = [
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject1()]),
        'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo1()]),
        'napomena' => $task->getZdescription1(),
        'vreme' => $task->getZtime1(),
        'status' => $task->getZstatus1(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject2()]),
        'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo2()]),
        'napomena' => $task->getZdescription2(),
        'vreme' => $task->getZtime2(),
        'status' => $task->getZstatus2(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject3()]),
        'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo3()]),
        'napomena' => $task->getZdescription3(),
        'vreme' => $task->getZtime3(),
        'status' => $task->getZstatus3(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject4()]),
        'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo4()]),
        'napomena' => $task->getZdescription4(),
        'vreme' => $task->getZtime4(),
        'status' => $task->getZstatus4(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject5()]),
        'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo5()]),
        'napomena' => $task->getZdescription5(),
        'vreme' => $task->getZtime5(),
        'status' => $task->getZstatus5(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject6()]),
        'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo6()]),
        'napomena' => $task->getZdescription6(),
        'vreme' => $task->getZtime6(),
        'status' => $task->getZstatus6(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject7()]),
        'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo7()]),
        'napomena' => $task->getZdescription7(),
        'vreme' => $task->getZtime7(),
        'status' => $task->getZstatus7(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject8()]),
        'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo8()]),
        'napomena' => $task->getZdescription8(),
        'vreme' => $task->getZtime8(),
        'status' => $task->getZstatus8(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject9()]),
        'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo9()]),
        'napomena' => $task->getZdescription9(),
        'vreme' => $task->getZtime9(),
        'status' => $task->getZstatus9(),
      ],
      [
        'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject10()]),
        'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo10()]),
        'napomena' => $task->getZdescription10(),
        'vreme' => $task->getZtime10(),
        'status' => $task->getZstatus10(),
      ],

    ];

    return $subs;
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
  public function getUsersSubsForEmail(FastTask $task, int $status): array {

    $users = [];
    if ($status == FastTaskData::EDIT) {
      if ($task->getZstatus1() == FastTaskData::EDIT) {
        $users[] = $task->getZgeo1();
      }

      if ($task->getZstatus2() == FastTaskData::EDIT) {
        $users[] = $task->getZgeo2();
      }

      if ($task->getZstatus3() == FastTaskData::EDIT) {
        $users[] = $task->getZgeo3();
      }

      if ($task->getZstatus4() == FastTaskData::EDIT) {
        $users[] = $task->getZgeo4();
      }

      if ($task->getZstatus5() == FastTaskData::EDIT) {
        $users[] = $task->getZgeo5();
      }

      if ($task->getZstatus6() == FastTaskData::EDIT) {
        $users[] = $task->getZgeo6();
      }

      if ($task->getZstatus7() == FastTaskData::EDIT) {
        $users[] = $task->getZgeo7();
      }

      if ($task->getZstatus8() == FastTaskData::EDIT) {
        $users[] = $task->getZgeo8();
      }

      if ($task->getZstatus9() == FastTaskData::EDIT) {
        $users[] = $task->getZgeo9();
      }

      if ($task->getZstatus10() == FastTaskData::EDIT) {
        $users[] = $task->getZgeo10();
      }


    } else {
      $users[] = $task->getZgeo1();
      $users[] = $task->getZgeo2();
      $users[] = $task->getZgeo3();
      $users[] = $task->getZgeo4();
      $users[] = $task->getZgeo5();
      $users[] = $task->getZgeo6();
      $users[] = $task->getZgeo7();
      $users[] = $task->getZgeo8();
      $users[] = $task->getZgeo9();
      $users[] = $task->getZgeo10();

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
    $company = $this->security->getUser()->getCompany();
//    $today = new DateTimeImmutable(); // Trenutni datum i vrijeme
    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('f');
    $qb
      ->where($qb->expr()->between('f.datum', ':start', ':end'))
      ->andWhere('f.company = :company')
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate)
      ->setParameter(':company', $company);

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

  public function getTimeTableActive(): array {
    $company = $this->security->getUser()->getCompany();
    $datum = new DateTimeImmutable();
    $startDate = $datum->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $datum->format('Y-m-d 23:59:59'); // Kraj dana
    $qb = $this->createQueryBuilder('f');
    $qb
      ->andWhere('f.status <> :status')
      ->andWhere('f.datum BETWEEN :startDate AND :endDate')
      ->andWhere('f.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':status', FastTaskData::OPEN)
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->orderBy('f.datum', 'DESC')
      ->setMaxResults(1);

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

  public function getTimeTableSubsActive(): array {
    $company = $this->security->getUser()->getCompany();
    $datum = new DateTimeImmutable();
    $startDate = $datum->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $datum->format('Y-m-d 23:59:59'); // Kraj dana
    $qb = $this->createQueryBuilder('f');
    $qb
      ->andWhere('f.status <> :status')
      ->andWhere('f.datum BETWEEN :startDate AND :endDate')
      ->andWhere('f.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':status', FastTaskData::OPEN)
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->orderBy('f.datum', 'DESC')
      ->setMaxResults(1);

    $query = $qb->getQuery();
    $fastTasks = $query->getResult();
    $tasks = [];
    if (!empty ($fastTasks)) {
      foreach ($fastTasks as $task) {

        if (!is_null($task->getZtime1())) {
          $time1 = $task->getZtime1();
        } else {
          $time1 = null;
        }
        if (!is_null($task->getZtime2())) {
          $time2 = $task->getZtime2();
        } else {
          $time2 = null;
        }
        if (!is_null($task->getZtime3())) {
          $time3 = $task->getZtime3();
        } else {
          $time3 = null;
        }
        if (!is_null($task->getZtime4())) {
          $time4 = $task->getZtime4();
        } else {
          $time4 = null;
        }
        if (!is_null($task->getZtime5())) {
          $time5 = $task->getZtime5();
        } else {
          $time5 = null;
        }
        if (!is_null($task->getZtime6())) {
          $time6 = $task->getZtime6();
        } else {
          $time6 = null;
        }
        if (!is_null($task->getZtime7())) {
          $time7 = $task->getZtime7();
        } else {
          $time7 = null;
        }
        if (!is_null($task->getZtime8())) {
          $time8 = $task->getZtime8();
        } else {
          $time8 = null;
        }
        if (!is_null($task->getZtime9())) {
          $time9 = $task->getZtime9();
        } else {
          $time9 = null;
        }
        if (!is_null($task->getZtime10())) {
          $time10 = $task->getZtime10();
        } else {
          $time10 = null;
        }

        $tasks = [
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject1()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo1()]),
            'napomena' => $task->getZdescription1(),
            'vreme' => $time1,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject2()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo2()]),
            'napomena' => $task->getZdescription2(),
            'vreme' => $time2,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject3()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo3()]),
            'napomena' => $task->getZdescription3(),
            'vreme' => $time3,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject4()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo4()]),
            'napomena' => $task->getZdescription4(),
            'vreme' => $time4,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject5()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo5()]),
            'napomena' => $task->getZdescription5(),
            'vreme' => $time5,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject6()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo6()]),
            'napomena' => $task->getZdescription6(),
            'vreme' => $time6,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject7()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo7()]),
            'napomena' => $task->getZdescription7(),
            'vreme' => $time7,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject8()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo8()]),
            'napomena' => $task->getZdescription8(),
            'vreme' => $time8,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject9()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo9()]),
            'napomena' => $task->getZdescription9(),
            'vreme' => $time9,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject10()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo10()]),
            'napomena' => $task->getZdescription10(),
            'vreme' => $time10,
          ]

        ];
      }
    }

    usort($tasks, function($a, $b) {
      return $a['vreme'] <=> $b['vreme'];
    });

    return $tasks;
  }

  public function getSubs(DateTimeImmutable $date): array {
    $company = $this->security->getUser()->getCompany();
//    $today = new DateTimeImmutable(); // Trenutni datum i vrijeme
    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('f');
    $qb
      ->where($qb->expr()->between('f.datum', ':start', ':end'))
      ->andWhere('f.company = :company')
      ->setParameter(':company', $company)
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate);

    $query = $qb->getQuery();
    $fastTasks = $query->getResult();
    $tasks = [];
    if (!empty ($fastTasks)) {
      foreach ($fastTasks as $task) {

        if (!is_null($task->getZtime1())) {
          $time1 = $task->getZtime1();
        } else {
          $time1 = null;
        }
        if (!is_null($task->getZtime2())) {
          $time2 = $task->getZtime2();
        } else {
          $time2 = null;
        }
        if (!is_null($task->getZtime3())) {
          $time3 = $task->getZtime3();
        } else {
          $time3 = null;
        }
        if (!is_null($task->getZtime4())) {
          $time4 = $task->getZtime4();
        } else {
          $time4 = null;
        }
        if (!is_null($task->getZtime5())) {
          $time5 = $task->getZtime5();
        } else {
          $time5 = null;
        }
        if (!is_null($task->getZtime6())) {
          $time6 = $task->getZtime6();
        } else {
          $time6 = null;
        }
        if (!is_null($task->getZtime7())) {
          $time7 = $task->getZtime7();
        } else {
          $time7 = null;
        }
        if (!is_null($task->getZtime8())) {
          $time8 = $task->getZtime8();
        } else {
          $time8 = null;
        }
        if (!is_null($task->getZtime9())) {
          $time9 = $task->getZtime9();
        } else {
          $time9 = null;
        }
        if (!is_null($task->getZtime10())) {
          $time10 = $task->getZtime10();
        } else {
          $time10 = null;
        }

        $tasks = [
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject1()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo1()]),
            'napomena' => $task->getZdescription1(),
            'vreme' => $time1,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject2()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo2()]),
            'napomena' => $task->getZdescription2(),
            'vreme' => $time2,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject3()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo3()]),
            'napomena' => $task->getZdescription3(),
            'vreme' => $time3,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject4()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo4()]),
            'napomena' => $task->getZdescription4(),
            'vreme' => $time4,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject5()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo5()]),
            'napomena' => $task->getZdescription5(),
            'vreme' => $time5,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject6()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo6()]),
            'napomena' => $task->getZdescription6(),
            'vreme' => $time6,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject7()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo7()]),
            'napomena' => $task->getZdescription7(),
            'vreme' => $time7,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject8()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo8()]),
            'napomena' => $task->getZdescription8(),
            'vreme' => $time8,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject9()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo9()]),
            'napomena' => $task->getZdescription9(),
            'vreme' => $time9,
          ],
          [
            'projekat' => $this->getEntityManager()->getRepository(Project::class)->findOneBy(['id' => $task->getZproject10()]),
            'geo' => $this->getEntityManager()->getRepository(User::class)->findOneBy(['id' => $task->getZgeo10()]),
            'napomena' => $task->getZdescription10(),
            'vreme' => $time10,
          ]

        ];
      }
    }

    usort($tasks, function($a, $b) {
      return $a['vreme'] <=> $b['vreme'];
    });

    return $tasks;
  }

  public function getTimeTableId(DateTimeImmutable $date): int {
    $company = $this->security->getUser()->getCompany();
    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('f');
    $qb
      ->where($qb->expr()->between('f.datum', ':start', ':end'))
      ->andWhere('f.status = :status1 OR f.status = :status2')
      ->andWhere('f.company = :company')
      ->setParameter(':company', $company)
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

  public function getTimeTableFinishCommand(DateTimeImmutable $date, Company $company): array {

    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('f');
    $qb
      ->where($qb->expr()->between('f.datum', ':start', ':end'))
      ->andWhere('f.status = :status1 OR f.status = :status2')
      ->andWhere('f.company = :company')
      ->setParameter(':company', $company)
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate)
      ->setParameter('status1', FastTaskData::SAVED)
      ->setParameter('status2', FastTaskData::EDIT)
      ->setMaxResults(1);

    $query = $qb->getQuery();
    return $query->getResult();


  }

  public function getTimeTableTomorrowId(DateTimeImmutable $date): int {
    $company = $this->security->getUser()->getCompany();
    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('f');
    $qb
      ->where($qb->expr()->between('f.datum', ':start', ':end'))
      ->andWhere('f.company = :company')
      ->setParameter(':company', $company)
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

  public function saveFastTask(FastTask $fastTask, array $data): ?FastTask {
    $datum = $data['task_quick_form_datum'];
    $everyone = $data['task_quick_form_everyone'];
    $format = "d.m.Y H:i:s";
    $dateTime = DateTimeImmutable::createFromFormat($format, $datum . '14:30:00');
    $company = $fastTask->getCompany();
    if (is_null($fastTask->getId())) {

      $startPlan = $dateTime->format('Y-m-d 00:00:00'); // Početak dana
      $endPlan = $dateTime->format('Y-m-d 23:59:59'); // Kraj dana

      $qb = $this->createQueryBuilder('f');
      $qb
        ->where($qb->expr()->between('f.datum', ':start', ':end'))
        ->andWhere('f.company = :company')
        ->setParameter(':company', $company)
        ->setParameter('start', $startPlan)
        ->setParameter('end', $endPlan)
        ->setMaxResults(1);

      $query = $qb->getQuery();
      $fast = $query->getResult();

      if (!empty($fast)) {
        return null;
      }

    }


    $currentTime = new DateTimeImmutable();
    $editTime = $dateTime->sub(new DateInterval('P1D'));


    if ($currentTime > $editTime) {
      $status = FastTaskData::EDIT;
    } else {
      $status = FastTaskData::OPEN;
    }

    $stanja = [];

    $fastTask->setDatum($dateTime);
    $fastTask->setEveryone($everyone);
    $noTasks = 0;
    $noSubs = 0;

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
          $fastTask->setFree1(null);
        } else {
          if ($fastTask->getFree1() != $task1['naplativ'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask1())) {
              if ($fastTask->getTask1() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask1());
                $fastTask->setTask1(0);
              }
            }
            $fastTask->setStatus1(FastTaskData::EDIT);
          }
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
        $fastTask->setFree1($task1['naplativ']);
      } else {
        $fastTask->setFree1(null);
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
          $fastTask->setFree2(null);
        } else {
          if ($fastTask->getFree2() != $task2['naplativ'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask2())) {
              if ($fastTask->getTask2() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask2());
                $fastTask->setTask2(0);
              }
            }
            $fastTask->setStatus2(FastTaskData::EDIT);
          }
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

        $fastTask->setFree2($task2['naplativ']);
      } else {
        $fastTask->setFree2(null);
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
          $fastTask->setFree3(null);
        } else {
          if ($fastTask->getFree3() != $task3['naplativ'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask3())) {
              if ($fastTask->getTask3() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask3());
                $fastTask->setTask3(0);
              }
            }
            $fastTask->setStatus3(FastTaskData::EDIT);
          }
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

        $fastTask->setFree3($task3['naplativ']);
      } else {
        $fastTask->setFree3(null);
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
          $fastTask->setFree4(null);
        } else {
          if ($fastTask->getFree4() != $task4['naplativ'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask4())) {
              if ($fastTask->getTask4() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask4());
                $fastTask->setTask4(0);
              }
            }
            $fastTask->setStatus4(FastTaskData::EDIT);
          }
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

        $fastTask->setFree4($task4['naplativ']);
      } else {
        $fastTask->setFree4(null);
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
          $fastTask->setFree5(null);
        } else {
          if ($fastTask->getFree5() != $task5['naplativ'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask5())) {
              if ($fastTask->getTask5() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask5());
                $fastTask->setTask5(0);
              }
            }
            $fastTask->setStatus5(FastTaskData::EDIT);
          }
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

        $fastTask->setFree5($task5['naplativ']);
      } else {
        $fastTask->setFree5(null);
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
          $fastTask->setFree6(null);
        } else {
          if ($fastTask->getFree6() != $task6['naplativ'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask6())) {
              if ($fastTask->getTask6() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask6());
                $fastTask->setTask6(0);
              }
            }
            $fastTask->setStatus6(FastTaskData::EDIT);
          }
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

        $fastTask->setFree6($task6['naplativ']);
      } else {
        $fastTask->setFree6(null);
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
          $fastTask->setFree7(null);
        } else {
          if ($fastTask->getFree7() != $task7['naplativ'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask7())) {
              if ($fastTask->getTask7() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask7());
                $fastTask->setTask7(0);
              }
            }
            $fastTask->setStatus7(FastTaskData::EDIT);
          }
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

        $fastTask->setFree7($task7['naplativ']);
      } else {
        $fastTask->setFree7(null);
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
          $fastTask->setFree8(null);
        } else {
          if ($fastTask->getFree8() != $task8['naplativ'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask8())) {
              if ($fastTask->getTask8() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask8());
                $fastTask->setTask8(0);
              }
            }
            $fastTask->setStatus8(FastTaskData::EDIT);
          }
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

        $fastTask->setFree8($task8['naplativ']);
      } else {
        $fastTask->setFree8(null);
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
          $fastTask->setFree9(null);
        } else {
          if ($fastTask->getFree9() != $task9['naplativ'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask9())) {
              if ($fastTask->getTask9() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask9());
                $fastTask->setTask9(0);
              }
            }
            $fastTask->setStatus9(FastTaskData::EDIT);
          }
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
        $fastTask->setFree9($task9['naplativ']);
      } else {
        $fastTask->setFree9(null);
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
          $fastTask->setFree10(null);
        } else {
          if ($fastTask->getFree10() != $task10['naplativ'] && $status == FastTaskData::EDIT) {
            if (!is_null($fastTask->getTask10())) {
              if ($fastTask->getTask10() != 0) {
                $this->getEntityManager()->getRepository(Task::class)->remove($fastTask->getTask10());
                $fastTask->setTask10(0);
              }
            }
            $fastTask->setStatus10(FastTaskData::EDIT);
          }
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

        $fastTask->setFree10($task10['naplativ']);
      } else {
        $fastTask->setFree10(null);
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

    if (isset($data['task_quick_zamena_form1'])) {
      $zamena1 = $data['task_quick_zamena_form1'];

      if (!is_null($fastTask->getId())) {
        $zproj1 = $zamena1['projekat'];
        if ($zproj1 == '---') {
          $zproj1 = null;
        }

        if (isset($zamena1['geo'][0])) {
          $zgeo1 = $zamena1['geo'][0];
          if (empty($zgeo1)) {
            $zgeo1 = null;
          }
        } else {
          $zgeo1 = null;
        }


        if (isset($zamena1['napomena'])) {
          $znapomena1 = trim($zamena1['napomena']);
          if (empty($znapomena1)) {
            $znapomena1 = null;
          }
        } else {
          $znapomena1 = null;
        }

        if (is_null($zproj1)) {
          $fastTask->setZproject1(null);
          $fastTask->setZgeo1(null);
          $fastTask->setZdescription1(null);
          $fastTask->setZtime1(null);
          $fastTask->setZstatus1(null);

        } else {

          if ($fastTask->getZProject1() != $zproj1 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus1(FastTaskData::EDIT);
          }
          if ($fastTask->getZgeo1() != $zgeo1 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus1(FastTaskData::EDIT);
          }

          if ($fastTask->getZdescription1() != $znapomena1 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus1(FastTaskData::EDIT);
          }
          if (isset($zamena1['vreme']) && $fastTask->getZtime1() != $zamena1['vreme'] && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus1(FastTaskData::EDIT);
          }
        }
      }
      if (($zamena1['projekat']) !== '---') {
        $noSubs++;

        $fastTask->setZproject1($zamena1['projekat']);

        if (!empty($zamena1['geo'][0])) {
          $fastTask->setZgeo1($zamena1['geo'][0]);
        } else {
          $fastTask->setZgeo1(null);
        }

        if (!empty(trim($zamena1['napomena']))) {
          $fastTask->setZdescription1($zamena1['napomena']);
        } else {
          $fastTask->setZdescription1(null);
        }

        if (!empty($zamena1['vreme'])) {
          $fastTask->setzTime1($zamena1['vreme']);
        }

      } else {
        $fastTask->setZproject1(null);
        $fastTask->setZgeo1(null);
        $fastTask->setZdescription1(null);
        $fastTask->setZtime1(null);
      }
    }
    if (isset($data['task_quick_zamena_form2'])) {
      $zamena2 = $data['task_quick_zamena_form2'];

      if (!is_null($fastTask->getId())) {
        $zproj2 = $zamena2['projekat'];
        if ($zproj2 == '---') {
          $zproj2 = null;
        }

        if (isset($zamena2['geo'][0])) {
          $zgeo2 = $zamena2['geo'][0];
          if (empty($zgeo2)) {
            $zgeo2 = null;
          }
        } else {
          $zgeo2 = null;
        }


        if (isset($zamena2['napomena'])) {
          $znapomena2 = trim($zamena2['napomena']);
          if (empty($znapomena2)) {
            $znapomena2 = null;
          }
        } else {
          $znapomena2 = null;
        }

        if (is_null($zproj2)) {
          $fastTask->setZproject2(null);
          $fastTask->setZgeo2(null);
          $fastTask->setZdescription2(null);
          $fastTask->setZtime2(null);
          $fastTask->setZstatus2(null);

        } else {

          if ($fastTask->getZProject2() != $zproj2 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus2(FastTaskData::EDIT);
          }
          if ($fastTask->getZgeo2() != $zgeo2 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus2(FastTaskData::EDIT);
          }

          if ($fastTask->getZdescription2() != $znapomena2 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus2(FastTaskData::EDIT);
          }
          if (isset($zamena2['vreme']) && $fastTask->getZtime2() != $zamena2['vreme'] && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus2(FastTaskData::EDIT);
          }
        }
      }
      if (($zamena2['projekat']) !== '---') {
        $noSubs++;
        $fastTask->setZproject2($zamena2['projekat']);

        if (!empty($zamena2['geo'][0])) {
          $fastTask->setZgeo2($zamena2['geo'][0]);
        } else {
          $fastTask->setZgeo2(null);
        }

        if (!empty(trim($zamena2['napomena']))) {
          $fastTask->setZdescription2($zamena2['napomena']);
        } else {
          $fastTask->setZdescription2(null);
        }

        if (!empty($zamena2['vreme'])) {
          $fastTask->setzTime2($zamena2['vreme']);
        }

      } else {
        $fastTask->setZproject2(null);
        $fastTask->setZgeo2(null);
        $fastTask->setZdescription2(null);
        $fastTask->setZtime2(null);
      }
    }
    if (isset($data['task_quick_zamena_form3'])) {
      $zamena3 = $data['task_quick_zamena_form3'];

      if (!is_null($fastTask->getId())) {
        $zproj3 = $zamena3['projekat'];
        if ($zproj3 == '---') {
          $zproj3 = null;
        }

        if (isset($zamena3['geo'][0])) {
          $zgeo3 = $zamena3['geo'][0];
          if (empty($zgeo3)) {
            $zgeo3 = null;
          }
        } else {
          $zgeo3 = null;
        }


        if (isset($zamena3['napomena'])) {
          $znapomena3 = trim($zamena3['napomena']);
          if (empty($znapomena3)) {
            $znapomena3 = null;
          }
        } else {
          $znapomena3 = null;
        }

        if (is_null($zproj3)) {
          $fastTask->setZproject3(null);
          $fastTask->setZgeo3(null);
          $fastTask->setZdescription3(null);
          $fastTask->setZtime3(null);
          $fastTask->setZstatus3(null);

        } else {

          if ($fastTask->getZProject3() != $zproj3 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus3(FastTaskData::EDIT);
          }
          if ($fastTask->getZgeo3() != $zgeo3 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus3(FastTaskData::EDIT);
          }

          if ($fastTask->getZdescription3() != $znapomena3 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus3(FastTaskData::EDIT);
          }
          if (isset($zamena3['vreme']) && $fastTask->getZtime3() != $zamena3['vreme'] && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus3(FastTaskData::EDIT);
          }
        }
      }
      if (($zamena3['projekat']) !== '---') {
        $noSubs++;
        $fastTask->setZproject3($zamena3['projekat']);

        if (!empty($zamena3['geo'][0])) {
          $fastTask->setZgeo3($zamena3['geo'][0]);
        } else {
          $fastTask->setZgeo3(null);
        }

        if (!empty(trim($zamena3['napomena']))) {
          $fastTask->setZdescription3($zamena3['napomena']);
        } else {
          $fastTask->setZdescription3(null);
        }

        if (!empty($zamena3['vreme'])) {
          $fastTask->setzTime3($zamena3['vreme']);
        }

      } else {
        $fastTask->setZproject3(null);
        $fastTask->setZgeo3(null);
        $fastTask->setZdescription3(null);
        $fastTask->setZtime3(null);
      }
    }
    if (isset($data['task_quick_zamena_form4'])) {
      $zamena4 = $data['task_quick_zamena_form4'];

      if (!is_null($fastTask->getId())) {
        $zproj4 = $zamena4['projekat'];
        if ($zproj4 == '---') {
          $zproj4 = null;
        }

        if (isset($zamena4['geo'][0])) {
          $zgeo4 = $zamena4['geo'][0];
          if (empty($zgeo4)) {
            $zgeo4 = null;
          }
        } else {
          $zgeo4 = null;
        }


        if (isset($zamena4['napomena'])) {
          $znapomena4 = trim($zamena4['napomena']);
          if (empty($znapomena4)) {
            $znapomena4 = null;
          }
        } else {
          $znapomena4 = null;
        }

        if (is_null($zproj4)) {
          $fastTask->setZproject4(null);
          $fastTask->setZgeo4(null);
          $fastTask->setZdescription4(null);
          $fastTask->setZtime4(null);
          $fastTask->setZstatus4(null);

        } else {

          if ($fastTask->getZProject4() != $zproj4 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus4(FastTaskData::EDIT);
          }
          if ($fastTask->getZgeo4() != $zgeo4 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus4(FastTaskData::EDIT);
          }

          if ($fastTask->getZdescription4() != $znapomena4 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus4(FastTaskData::EDIT);
          }
          if (isset($zamena4['vreme']) && $fastTask->getZtime4() != $zamena4['vreme'] && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus4(FastTaskData::EDIT);
          }
        }
      }
      if (($zamena4['projekat']) !== '---') {
        $noSubs++;
        $fastTask->setZproject4($zamena4['projekat']);

        if (!empty($zamena4['geo'][0])) {
          $fastTask->setZgeo4($zamena4['geo'][0]);
        } else {
          $fastTask->setZgeo4(null);
        }

        if (!empty(trim($zamena4['napomena']))) {
          $fastTask->setZdescription4($zamena4['napomena']);
        } else {
          $fastTask->setZdescription4(null);
        }

        if (!empty($zamena4['vreme'])) {
          $fastTask->setzTime4($zamena4['vreme']);
        }

      } else {
        $fastTask->setZproject4(null);
        $fastTask->setZgeo4(null);
        $fastTask->setZdescription4(null);
        $fastTask->setZtime4(null);
      }
    }
    if (isset($data['task_quick_zamena_form5'])) {
      $zamena5 = $data['task_quick_zamena_form5'];

      if (!is_null($fastTask->getId())) {
        $zproj5 = $zamena5['projekat'];
        if ($zproj5 == '---') {
          $zproj5 = null;
        }

        if (isset($zamena5['geo'][0])) {
          $zgeo5 = $zamena5['geo'][0];
          if (empty($zgeo5)) {
            $zgeo5 = null;
          }
        } else {
          $zgeo5 = null;
        }


        if (isset($zamena5['napomena'])) {
          $znapomena5 = trim($zamena5['napomena']);
          if (empty($znapomena5)) {
            $znapomena5 = null;
          }
        } else {
          $znapomena5 = null;
        }

        if (is_null($zproj5)) {
          $fastTask->setZproject5(null);
          $fastTask->setZgeo5(null);
          $fastTask->setZdescription5(null);
          $fastTask->setZtime5(null);
          $fastTask->setZstatus5(null);

        } else {

          if ($fastTask->getZProject5() != $zproj5 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus5(FastTaskData::EDIT);
          }
          if ($fastTask->getZgeo5() != $zgeo5 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus5(FastTaskData::EDIT);
          }

          if ($fastTask->getZdescription5() != $znapomena5 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus5(FastTaskData::EDIT);
          }
          if (isset($zamena5['vreme']) && $fastTask->getZtime5() != $zamena5['vreme'] && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus5(FastTaskData::EDIT);
          }
        }
      }
      if (($zamena5['projekat']) !== '---') {
        $noSubs++;
        $fastTask->setZproject5($zamena5['projekat']);

        if (!empty($zamena5['geo'][0])) {
          $fastTask->setZgeo5($zamena5['geo'][0]);
        } else {
          $fastTask->setZgeo5(null);
        }

        if (!empty(trim($zamena5['napomena']))) {
          $fastTask->setZdescription5($zamena5['napomena']);
        } else {
          $fastTask->setZdescription5(null);
        }

        if (!empty($zamena5['vreme'])) {
          $fastTask->setzTime5($zamena5['vreme']);
        }

      } else {
        $fastTask->setZproject5(null);
        $fastTask->setZgeo5(null);
        $fastTask->setZdescription5(null);
        $fastTask->setZtime5(null);
      }
    }
    if (isset($data['task_quick_zamena_form6'])) {
      $zamena6 = $data['task_quick_zamena_form6'];

      if (!is_null($fastTask->getId())) {
        $zproj6 = $zamena6['projekat'];
        if ($zproj6 == '---') {
          $zproj6 = null;
        }

        if (isset($zamena6['geo'][0])) {
          $zgeo6 = $zamena6['geo'][0];
          if (empty($zgeo6)) {
            $zgeo6 = null;
          }
        } else {
          $zgeo6 = null;
        }


        if (isset($zamena6['napomena'])) {
          $znapomena6 = trim($zamena6['napomena']);
          if (empty($znapomena6)) {
            $znapomena6 = null;
          }
        } else {
          $znapomena6 = null;
        }

        if (is_null($zproj6)) {
          $fastTask->setZproject6(null);
          $fastTask->setZgeo6(null);
          $fastTask->setZdescription6(null);
          $fastTask->setZtime6(null);
          $fastTask->setZstatus6(null);

        } else {

          if ($fastTask->getZProject6() != $zproj6 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus6(FastTaskData::EDIT);
          }
          if ($fastTask->getZgeo6() != $zgeo6 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus6(FastTaskData::EDIT);
          }

          if ($fastTask->getZdescription6() != $znapomena6 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus6(FastTaskData::EDIT);
          }
          if (isset($zamena6['vreme']) && $fastTask->getZtime6() != $zamena6['vreme'] && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus6(FastTaskData::EDIT);
          }
        }
      }
      if (($zamena6['projekat']) !== '---') {
        $noSubs++;
        $fastTask->setZproject6($zamena6['projekat']);

        if (!empty($zamena6['geo'][0])) {
          $fastTask->setZgeo6($zamena6['geo'][0]);
        } else {
          $fastTask->setZgeo6(null);
        }

        if (!empty(trim($zamena6['napomena']))) {
          $fastTask->setZdescription6($zamena6['napomena']);
        } else {
          $fastTask->setZdescription6(null);
        }

        if (!empty($zamena6['vreme'])) {
          $fastTask->setzTime6($zamena6['vreme']);
        }

      } else {
        $fastTask->setZproject6(null);
        $fastTask->setZgeo6(null);
        $fastTask->setZdescription6(null);
        $fastTask->setZtime6(null);
      }
    }
    if (isset($data['task_quick_zamena_form7'])) {
      $zamena7 = $data['task_quick_zamena_form7'];

      if (!is_null($fastTask->getId())) {
        $zproj7 = $zamena7['projekat'];
        if ($zproj7 == '---') {
          $zproj7 = null;
        }

        if (isset($zamena7['geo'][0])) {
          $zgeo7 = $zamena7['geo'][0];
          if (empty($zgeo7)) {
            $zgeo7 = null;
          }
        } else {
          $zgeo7 = null;
        }


        if (isset($zamena7['napomena'])) {
          $znapomena7 = trim($zamena7['napomena']);
          if (empty($znapomena7)) {
            $znapomena7 = null;
          }
        } else {
          $znapomena7 = null;
        }

        if (is_null($zproj7)) {
          $fastTask->setZproject7(null);
          $fastTask->setZgeo7(null);
          $fastTask->setZdescription7(null);
          $fastTask->setZtime7(null);
          $fastTask->setZstatus7(null);

        } else {

          if ($fastTask->getZProject7() != $zproj7 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus7(FastTaskData::EDIT);
          }
          if ($fastTask->getZgeo7() != $zgeo7 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus7(FastTaskData::EDIT);
          }

          if ($fastTask->getZdescription7() != $znapomena7 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus7(FastTaskData::EDIT);
          }
          if (isset($zamena7['vreme']) && $fastTask->getZtime7() != $zamena7['vreme'] && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus7(FastTaskData::EDIT);
          }
        }
      }
      if (($zamena7['projekat']) !== '---') {
        $noSubs++;
        $fastTask->setZproject7($zamena7['projekat']);

        if (!empty($zamena7['geo'][0])) {
          $fastTask->setZgeo7($zamena7['geo'][0]);
        } else {
          $fastTask->setZgeo7(null);
        }

        if (!empty(trim($zamena7['napomena']))) {
          $fastTask->setZdescription7($zamena7['napomena']);
        } else {
          $fastTask->setZdescription7(null);
        }

        if (!empty($zamena7['vreme'])) {
          $fastTask->setzTime7($zamena7['vreme']);
        }

      } else {
        $fastTask->setZproject7(null);
        $fastTask->setZgeo7(null);
        $fastTask->setZdescription7(null);
        $fastTask->setZtime7(null);
      }
    }
    if (isset($data['task_quick_zamena_form8'])) {
      $zamena8 = $data['task_quick_zamena_form8'];

      if (!is_null($fastTask->getId())) {
        $zproj8 = $zamena8['projekat'];
        if ($zproj8 == '---') {
          $zproj8 = null;
        }

        if (isset($zamena8['geo'][0])) {
          $zgeo8 = $zamena8['geo'][0];
          if (empty($zgeo8)) {
            $zgeo8 = null;
          }
        } else {
          $zgeo8 = null;
        }


        if (isset($zamena8['napomena'])) {
          $znapomena8 = trim($zamena8['napomena']);
          if (empty($znapomena8)) {
            $znapomena8 = null;
          }
        } else {
          $znapomena8 = null;
        }

        if (is_null($zproj8)) {
          $fastTask->setZproject8(null);
          $fastTask->setZgeo8(null);
          $fastTask->setZdescription8(null);
          $fastTask->setZtime8(null);
          $fastTask->setZstatus8(null);

        } else {

          if ($fastTask->getZProject8() != $zproj8 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus8(FastTaskData::EDIT);
          }
          if ($fastTask->getZgeo8() != $zgeo8 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus8(FastTaskData::EDIT);
          }

          if ($fastTask->getZdescription8() != $znapomena8 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus8(FastTaskData::EDIT);
          }
          if (isset($zamena8['vreme']) && $fastTask->getZtime8() != $zamena8['vreme'] && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus8(FastTaskData::EDIT);
          }
        }
      }
      if (($zamena8['projekat']) !== '---') {
        $noSubs++;
        $fastTask->setZproject8($zamena8['projekat']);

        if (!empty($zamena8['geo'][0])) {
          $fastTask->setZgeo8($zamena8['geo'][0]);
        } else {
          $fastTask->setZgeo8(null);
        }

        if (!empty(trim($zamena8['napomena']))) {
          $fastTask->setZdescription8($zamena8['napomena']);
        } else {
          $fastTask->setZdescription8(null);
        }

        if (!empty($zamena8['vreme'])) {
          $fastTask->setzTime8($zamena8['vreme']);
        }

      } else {
        $fastTask->setZproject8(null);
        $fastTask->setZgeo8(null);
        $fastTask->setZdescription8(null);
        $fastTask->setZtime8(null);
      }
    }
    if (isset($data['task_quick_zamena_form9'])) {
      $zamena9 = $data['task_quick_zamena_form9'];

      if (!is_null($fastTask->getId())) {
        $zproj9 = $zamena9['projekat'];
        if ($zproj9 == '---') {
          $zproj9 = null;
        }

        if (isset($zamena9['geo'][0])) {
          $zgeo9 = $zamena9['geo'][0];
          if (empty($zgeo9)) {
            $zgeo9 = null;
          }
        } else {
          $zgeo9 = null;
        }


        if (isset($zamena9['napomena'])) {
          $znapomena9 = trim($zamena9['napomena']);
          if (empty($znapomena9)) {
            $znapomena9 = null;
          }
        } else {
          $znapomena9 = null;
        }

        if (is_null($zproj9)) {
          $fastTask->setZproject9(null);
          $fastTask->setZgeo9(null);
          $fastTask->setZdescription9(null);
          $fastTask->setZtime9(null);
          $fastTask->setZstatus9(null);

        } else {

          if ($fastTask->getZProject9() != $zproj9 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus9(FastTaskData::EDIT);
          }
          if ($fastTask->getZgeo9() != $zgeo9 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus9(FastTaskData::EDIT);
          }

          if ($fastTask->getZdescription9() != $znapomena9 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus9(FastTaskData::EDIT);
          }
          if (isset($zamena9['vreme']) && $fastTask->getZtime9() != $zamena9['vreme'] && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus9(FastTaskData::EDIT);
          }
        }
      }
      if (($zamena9['projekat']) !== '---') {
        $noSubs++;
        $fastTask->setZproject9($zamena9['projekat']);

        if (!empty($zamena9['geo'][0])) {
          $fastTask->setZgeo9($zamena9['geo'][0]);
        } else {
          $fastTask->setZgeo9(null);
        }

        if (!empty(trim($zamena9['napomena']))) {
          $fastTask->setZdescription9($zamena9['napomena']);
        } else {
          $fastTask->setZdescription9(null);
        }

        if (!empty($zamena9['vreme'])) {
          $fastTask->setzTime9($zamena9['vreme']);
        }

      } else {
        $fastTask->setZproject9(null);
        $fastTask->setZgeo9(null);
        $fastTask->setZdescription9(null);
        $fastTask->setZtime9(null);
      }
    }
    if (isset($data['task_quick_zamena_form10'])) {
      $zamena10 = $data['task_quick_zamena_form10'];

      if (!is_null($fastTask->getId())) {
        $zproj10 = $zamena10['projekat'];
        if ($zproj10 == '---') {
          $zproj10 = null;
        }

        if (isset($zamena10['geo'][0])) {
          $zgeo10 = $zamena10['geo'][0];
          if (empty($zgeo10)) {
            $zgeo10 = null;
          }
        } else {
          $zgeo10 = null;
        }


        if (isset($zamena10['napomena'])) {
          $znapomena10 = trim($zamena10['napomena']);
          if (empty($znapomena10)) {
            $znapomena10 = null;
          }
        } else {
          $znapomena10 = null;
        }

        if (is_null($zproj10)) {
          $fastTask->setZproject10(null);
          $fastTask->setZgeo10(null);
          $fastTask->setZdescription10(null);
          $fastTask->setZtime10(null);
          $fastTask->setZstatus10(null);

        } else {

          if ($fastTask->getZProject10() != $zproj10 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus10(FastTaskData::EDIT);
          }
          if ($fastTask->getZgeo10() != $zgeo10 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus10(FastTaskData::EDIT);
          }

          if ($fastTask->getZdescription10() != $znapomena10 && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus10(FastTaskData::EDIT);
          }
          if (isset($zamena10['vreme']) && $fastTask->getZtime10() != $zamena10['vreme'] && $status == FastTaskData::EDIT) {
            $fastTask->setZstatus10(FastTaskData::EDIT);
          }
        }
      }
      if (($zamena10['projekat']) !== '---') {
        $noSubs++;
        $fastTask->setZproject10($zamena10['projekat']);

        if (!empty($zamena10['geo'][0])) {
          $fastTask->setZgeo10($zamena10['geo'][0]);
        } else {
          $fastTask->setZgeo10(null);
        }

        if (!empty(trim($zamena10['napomena']))) {
          $fastTask->setZdescription10($zamena10['napomena']);
        } else {
          $fastTask->setZdescription10(null);
        }

        if (!empty($zamena10['vreme'])) {
          $fastTask->setzTime10($zamena10['vreme']);
        }

      } else {
        $fastTask->setZproject10(null);
        $fastTask->setZgeo10(null);
        $fastTask->setZdescription10(null);
        $fastTask->setZtime10(null);
      }
    }

    $fastTask->setNoTasks($noTasks);
    $fastTask->setNoSubs($noSubs);

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

    $stanja[] = $fastTask->getZstatus1();
    $stanja[] = $fastTask->getZstatus2();
    $stanja[] = $fastTask->getZstatus3();
    $stanja[] = $fastTask->getZstatus4();
    $stanja[] = $fastTask->getZstatus5();
    $stanja[] = $fastTask->getZstatus6();
    $stanja[] = $fastTask->getZstatus7();
    $stanja[] = $fastTask->getZstatus8();
    $stanja[] = $fastTask->getZstatus9();
    $stanja[] = $fastTask->getZstatus10();

    if (in_array(FastTaskData::EDIT, $stanja, true)) {
      $fastTask->setStatus(FastTaskData::EDIT);
    } else {
      $fastTask->setStatus(FastTaskData::OPEN);
    }

    if ($currentTime > $editTime) {
      if (is_null($fastTask->getId())) {

        $superadmin = $this->getEntityManager()->getRepository(User::class)->findOneBy(['company' => $company, 'userType' => UserRolesData::ROLE_SUPER_ADMIN, 'isSuspended' => false], ['id' => 'ASC']);

        $plan = $this->getEntityManager()->getRepository(Task::class)->createTasksFromList($fastTask, $superadmin);
        $datum = $plan->getDatum();

        $timetable = $this->getEntityManager()->getRepository(FastTask::class)->getTimetableByFastTasks($plan);
        $subs = $this->getEntityManager()->getRepository(FastTask::class)->getSubsByFastTasks($plan);

        $users = $this->getEntityManager()->getRepository(FastTask::class)->getUsersForEmail($plan, FastTaskData::SAVED);
        $usersSub = $this->getEntityManager()->getRepository(FastTask::class)->getUsersSubsForEmail($plan, FastTaskData::SAVED);

        if ($fastTask->getCompany()->getId() == 1) {
          $users[] = $this->getEntityManager()->getRepository(User::class)->find(25);
          $usersSub[] = $this->getEntityManager()->getRepository(User::class)->find(25);
        }

        $this->mail->plan($timetable, $users, $datum);
        $this->mail->subs($subs, $usersSub, $datum);
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

  public function delete(FastTask $fastTask): void {
    $this->getEntityManager()->remove($fastTask);
    $this->getEntityManager()->flush();
  }

  public function findForForm(int $id = 0): FastTask {
    if (empty($id)) {
      $task = new FastTask();
      $task->setCompany($this->security->getUser()->getCompany());
      return $task;
    }
    return $this->getEntityManager()->getRepository(FastTask::class)->find($id);

  }

  public function getPlan(): FastTask {
    $company = $this->security->getUser()->getCompany();
    $qb = $this->createQueryBuilder('f');
    $qb
      ->andWhere('f.status <> :status')
      ->andWhere('f.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':status', FastTaskData::OPEN)
      ->orderBy('f.datum', 'DESC')
      ->setMaxResults(1);

    $query = $qb->getQuery();
    $fast = $query->getResult();

  return $fast;


  }

  public function getDisabledDates(): array {
    $company = $this->security->getUser()->getCompany();
    $dates = [];
    $qb = $this->createQueryBuilder('f');
    $qb
      ->andWhere($qb->expr()->orX(
        $qb->expr()->eq('f.status', ':status2'),
        $qb->expr()->eq('f.status', ':status3'),
        $qb->expr()->eq('f.status', ':status4')
      ))
      ->andWhere('f.company = :company')
      ->setParameter('company', $company)
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
