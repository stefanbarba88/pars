<?php

namespace App\Controller;

use App\Classes\Data\FastTaskData;
use App\Classes\Data\InternTaskStatusData;
use App\Classes\Data\UserRolesData;
use App\Classes\JMBGcheck\JMBGcheck;
use App\Entity\Availability;
use App\Entity\Calendar;
use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\FastTask;
use App\Entity\ManagerChecklist;
use App\Entity\Plan;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\Ticket;
use App\Entity\TimeTask;
use App\Entity\Tool;
use App\Entity\User;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


//#[IsGranted('ROLE_USER', message: 'Nemas pristup', statusCode: 403)]

class HomeController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }
  #[Route('/', name: 'app_home')]
  public function index()    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    if ($this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }
    $args = [];
    $user = $this->getUser();
    $company = $user->getCompany();

    $args['user'] = $user;
    $args['client'] = $user->getCompany()->getSettings()->getIsClientView();
    $args['car'] = $user->getCompany()->getSettings()->isCar();
    $args['tool'] = $user->getCompany()->getSettings()->isTool();
    $args['calendar'] = $user->getCompany()->getSettings()->isCalendar();
    $args['settings'] = $user->getCompany()->getSettings();

    if ($args['calendar']) {
      $args['noCalendar'] = $this->em->getRepository(Calendar::class)->countCalendarRequests();
    }
    if ($args['car']) {
      $args['noCars'] = $this->em->getRepository(Car::class)->count(['company' => $user->getCompany()]);
    }
    if ($args['tool']) {
      $args['noTools'] = $this->em->getRepository(Tool::class)->count(['company' => $user->getCompany()]);
    }
    if ($args['client']) {
      $ticket1 = $this->em->getRepository(Ticket::class)->count(['status' => InternTaskStatusData::NIJE_ZAPOCETO, 'company' => $user->getCompany()]);
      $ticket2 = $this->em->getRepository(Ticket::class)->count(['status' => InternTaskStatusData::ZAPOCETO, 'company' => $user->getCompany()]);
      $args['noTickets'] = $ticket1 + $ticket2;
    }

    $args['sutra'] = new DateTimeImmutable('tomorrow');
    $args['danas'] = new DateTimeImmutable();

    if ($user->getCompany()->getSettings()->isPlanTomorrow()) {
      $args['tomorrowTimetable'] = $this->em->getRepository(Task::class)->getTasksByDate($args['sutra']);
      $args['tomorrowTimetableIntern'] = $this->em->getRepository(ManagerChecklist::class)->getInternTasksByDate($args['sutra']);
    }

    if ($user->getCompany()->getSettings()->isPlanToday()) {
      $args['timetable'] = $this->em->getRepository(Task::class)->getTasksByDate($args['danas']);
      $args['timetableIntern'] = $this->em->getRepository(ManagerChecklist::class)->getInternTasksByDate($args['danas']);
    }

    if ($user->getCompany()->getSettings()->isPlanEmployee()) {
      $args['dostupnosti'] = $this->em->getRepository(Availability::class)->getAllDostupnostiDanasBasic();
    }

    if ($user->getCompany()->getSettings()->isPlan()) {
      $args['planRada'] = $this->em->getRepository(Plan::class)->findOneBy(['company' => $company, 'datumKreiranja' => $args['danas']->setTime(7,0) ]);
      $args['planRadaSutra'] = $this->em->getRepository(Plan::class)->findOneBy(['company' => $company, 'datumKreiranja' => $args['sutra']->setTime(7,0) ]);
    }

    if ($user->getCompany()->getSettings()->isWidgete()) {
      $args['noZaposleni'] = $this->em->getRepository(User::class)->count(['company' => $user->getCompany(), 'isSuspended' => false, 'userType' => UserRolesData::ROLE_EMPLOYEE]);
      $args['noProjekata'] = $this->em->getRepository(Project::class)->count(['company' => $user->getCompany(), 'isSuspended' => false]);
      $args['noTasks'] = $this->em->getRepository(Task::class)->count(['company' => $user->getCompany(), 'isDeleted' => false]);
      $args['noInternTasks'] = $this->em->getRepository(ManagerChecklist::class)->count(['status' => InternTaskStatusData::NIJE_ZAPOCETO, 'company' => $user->getCompany()]);
    }

//
//    $args['plan'] = $this->em->getRepository(FastTask::class)->getTimeTableActive();
//    $args['subs'] = $this->em->getRepository(FastTask::class)->getTimeTableSubsActive();

//    $args['tomorrowTimetable'] = $this->em->getRepository(FastTask::class)->getTimetable($args['sutra']);
//    $args['tomorrowSubs'] = $this->em->getRepository(FastTask::class)->getSubs($args['sutra']);
//    $args['tomorrowTimetableId'] = $this->em->getRepository(FastTask::class)->getTimeTableTomorrowId($args['sutra']);
////    $args['dostupnosti'] = $this->em->getRepository(Availability::class)->getDostupnostDanas();

//    $args['dostupnostiSutra'] = $this->em->getRepository(Availability::class)->getAllDostupnostiSutra();
//
//    $args['nerasporedjenost'] = $this->em->getRepository(Availability::class)->getAllNerasporedjenost();
//    $args['tekuciPoslovi'] = $this->em->getRepository(TimeTask::class)->findBy(['company' => $user->getCompany(), 'finish' => null]);

//srediti ovaj upit, uzima puno resursa
//    $args['countTasksUnclosed'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedLogs();
    $args['countTasksUnclosed'] = 0;

//    $args['checklistActive'] = $this->em->getRepository(ManagerChecklist::class)->findBy(['user' => $user, 'status' => 0], ['priority' => 'ASC', 'id' => 'ASC']);
//    $args['countChecklistActive'] = count($args['checklistActive']);
//
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $args['countTasksActiveByUser'] = $this->em->getRepository(Task::class)->countGetTasksByUser($user);
      $args['checklistActive'] = $this->em->getRepository(ManagerChecklist::class)->count(['user' => $this->getUser(), 'status' => InternTaskStatusData::NIJE_ZAPOCETO]);

      if ($user->getCompany()->getSettings()->isPlanTomorrow()) {
        $args['checklistTomorrow'] = $this->em->getRepository(ManagerChecklist::class)->getInternTasksByDateUser($args['sutra'], $user);
      }

      if ($user->getCompany()->getSettings()->isPlanToday()) {
        $args['checklistToday'] = $this->em->getRepository(ManagerChecklist::class)->getInternTasksByDateUser($args['danas'], $user);
      }

      if ($user->getCompany()->getSettings()->isCar()) {
        $args['lastReservation'] = $this->em->getRepository(CarReservation::class)->findOneBy(['driver' => $user, 'finished' => null], ['id' => 'desc']);
      }

      if ($user->getCompany()->getSettings()->isTool()) {
        $args['noTools'] = 0;
        if ($user->getToolReservations()->isEmpty()) {
          $args['noTools'] = 0;
        } else {
          foreach ($user->getToolReservations() as $res) {
            if (is_null($res->getFinished())) {
              $args['noTools']++;
            }
          }
        }
      }


      $args['logs'] = $this->em->getRepository(TaskLog::class)->findByUser($user);
//        $args['logs'] = $this->em->getRepository(TaskLog::class)->findByUserBasic($user);


      $args['countTasksUnclosedByUser'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedByUser($user);
      $args['countTasksUnclosed'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedLogsByUser($user);


      $args['countLogs'] = $this->em->getRepository(TaskLog::class)->countLogsByUser($user);

      $mobileDetect = new MobileDetect();
      if($mobileDetect->isMobile()) {
        return $this->render('home/phone/index_employee.html.twig', $args);
      }
      return $this->render('home/index_employee.html.twig', $args);
    }

    if ($user->getUserType() == UserRolesData::ROLE_CLIENT ) {
      return $this->redirectToRoute('app_ticket_list');
    }

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('home/phone/index_admin.html.twig', $args);
    }

    return $this->render('home/index_admin.html.twig', $args);
  }

  public function nav(): Response {
//    $args = [];
////    $args['sindikati'] = $em->getRepository(GranskiSindikat::class)->findBy(['parent' => GranskiSindikat::PARENT]);
//
//    $userRole = $this->getUser()->getUserType();
//    $args['uputstvo'] = $em->getRepository(Uputstvo::class)->findOneBy(['role' => $userRole]);
//
//    switch ($userRole) {
//      case UserRolesData::ROLE_UPRAVNIK_GRANE;
//
//        $args['grana'] = $this->getUser()->getGranskiSindikatUpGrana();
//        $args['kongres'] = $em->getRepository(Kongres::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false], ['datumOdrzavanja'=>'DESC']);
//        $args['glavniOdbor'] = $em->getRepository(GlavniOdbor::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['izvrsniOdbor'] = $em->getRepository(IzvrsniOdbor::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['nadzorniOdbor'] = $em->getRepository(NadzorniOdbor::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['sekcijaZena'] = $em->getRepository(SekcijaZena::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['sekcijaMladih'] = $em->getRepository(SekcijaMladih::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['skupstina'] = $em->getRepository(Skupstina::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['predsednistvo'] = $em->getRepository(Predsednistvo::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//
//        return $this->render('navigation/upravnik_grane_navigation.html.twig', $args);
//
//      case UserRolesData::ROLE_UPRAVNIK_CENTRALE;
//        $args['savetUgs'] = $em->getRepository(SavetUgs::class)->findOneBy(['isArhiva' => false]);
//        $args['statutarniOdbor'] = $em->getRepository(StatutarniOdbor::class)->findOneBy(['isArhiva' => false]);
//        return $this->render('navigation/upravnik_centrale_navigation.html.twig', $args);
//
//      case UserRolesData::ROLE_REG_POVERENIK;
//        $args['poverenistvo'] = $this->getUser()->getPoverenistvo();
//        $args['grana'] = $args['poverenistvo']->getGranskiSindikat();
//        return $this->render('navigation/regionalni_poverenik_navigation.html.twig', $args);
//
//      case UserRolesData::ROLE_POVERENIK;
//        return $this->render('navigation/poverenik_navigation.html.twig', $args);
//
//      case UserRolesData::ROLE_SUPER_ADMIN;
//        $args['savetUgs'] = $em->getRepository(SavetUgs::class)->findOneBy(['isArhiva' => false]);
//        $args['statutarniOdbor'] = $em->getRepository(StatutarniOdbor::class)->findOneBy(['isArhiva' => false]);
//        return $this->render('navigation/super_admin_navigation.html.twig', $args);
//
//      default:
//        return new Response('');
//    }
  }
}
