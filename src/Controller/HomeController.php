<?php

namespace App\Controller;

use App\Classes\Data\FastTaskData;
use App\Classes\Data\InternTaskStatusData;
use App\Classes\Data\TaskStatusData;
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
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\Ticket;
use App\Entity\TimeTask;
use App\Entity\Tool;
use App\Entity\User;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


//#[IsGranted('ROLE_USER', message: 'Nemas pristup', statusCode: 403)]

class HomeController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }
  #[Route('/', name: 'app_home')]
  public function index(PaginatorInterface $paginator, Request $request, SessionInterface $session)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    if ($this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }
    $args = [];
    $user = $this->getUser();

    $company = $user->getCompany();
    $args['admin'] = false;
    if ($session->has('admin')) {
      $args['admin'] = true;
    }

    $args['user'] = $user;
    $args['client'] = $user->getCompany()->getSettings()->getIsClientView();
    $args['car'] = $user->getCompany()->getSettings()->isCar();
    $args['tool'] = $user->getCompany()->getSettings()->isTool();
    $args['calendar'] = $user->getCompany()->getSettings()->isCalendar();
    $args['settings'] = $user->getCompany()->getSettings();

    $args['danas'] = new DateTimeImmutable();
    $args['sutra'] = new DateTimeImmutable('tomorrow');


    if ($args['calendar']) {
      $args['noCalendar'] = $this->em->getRepository(Calendar::class)->countCalendarRequests();
    }
    if ($args['car']) {
      $args['noCars'] = $this->em->getRepository(Car::class)->count(['company' => $user->getCompany(), 'isSuspended' => false]);
    }
    if ($args['tool']) {
      $args['noTools'] = $this->em->getRepository(Tool::class)->count(['company' => $user->getCompany(), 'isSuspended' => false]);
    }
    if ($args['client']) {
      $ticket1 = $this->em->getRepository(Ticket::class)->count(['status' => InternTaskStatusData::NIJE_ZAPOCETO, 'company' => $user->getCompany()]);
      $ticket2 = $this->em->getRepository(Ticket::class)->count(['status' => InternTaskStatusData::ZAPOCETO, 'company' => $user->getCompany()]);
      $args['noTickets'] = $ticket1 + $ticket2;
    }
    $args['countTasksUnclosed'] = 0;

    if ($user->getUserType() == UserRolesData::ROLE_SUPER_ADMIN || $user->getUserType() == UserRolesData::ROLE_ADMIN || $args['admin']) {
      if ($user->getCompany()->getSettings()->isPlanTomorrow()) {
        $args['tomorrowTimetable'] = $this->em->getRepository(Task::class)->getTasksByDate($args['sutra']);
        $args['tomorrowTimetableIntern'] = $this->em->getRepository(ManagerChecklist::class)->getInternTasksByDate($args['sutra']);
      }

      if ($user->getCompany()->getSettings()->isPlanToday()) {
        $args['timetable'] = $this->em->getRepository(Task::class)->getTasksByDate($args['danas']);
        $args['timetableIntern'] = $this->em->getRepository(ManagerChecklist::class)->getInternTasksByDate($args['danas']);
      } else {
        $tasks = $this->em->getRepository(Task::class)->getTasksByCompanyHomePaginator($company);
        $pagination = $paginator->paginate(
          $tasks, /* query NOT result */
          $request->query->getInt('page', 1), /*page number*/
          6,
          [
            'pageName' => 'page',  // Menjamo naziv parametra za stranicu
            'pageParameterName' => 'page',  // Menjamo naziv parametra za stranicu
            'sortFieldParameterName' => 'sort',  // Menjamo naziv parametra za sortiranje
          ]
        );
        $args['pagination'] = $pagination;

        $interns = $this->em->getRepository(ManagerChecklist::class)->getChecklistToDoHomeCompanyPaginator($company);
        $pagination1 = $paginator->paginate(
          $interns, /* query NOT result */
          $request->query->getInt('page1', 1), /*page number*/
          6,
          [
            'pageName' => 'page1',  // Menjamo naziv parametra za stranicu
            'pageParameterName' => 'page1',  // Menjamo naziv parametra za stranicu
            'sortFieldParameterName' => 'sort1',  // Menjamo naziv parametra za sortiranje
          ]
        );
        $args['pagination1'] = $pagination1;
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
        $args['noTasks'] = $this->em->getRepository(Task::class)->countActiveTasks();

        $args['verify'] = $this->em->getRepository(Task::class)->findBy(['status' => TaskStatusData::ZAVRSENO, 'company' => $company]);
        $args['verifyNo'] = count($args['verify']);

//        $args['checklistActive'] = $this->em->getRepository(ManagerChecklist::class)->getChecklistUser($user);
//        $args['checklistCreatedByUserActive'] = $this->em->getRepository(ManagerChecklist::class)->getChecklistCreatedByUserActive($user);
//        $args['countChecklistActive'] = count($args['checklistActive']);
//        $args['countChecklistCreatedByUserActive'] = count($args['checklistCreatedByUserActive']);

        $args['noInternTasks'] = $this->em->getRepository(ManagerChecklist::class)->countInternTasks($user->getCompany());
      }
    }



    if ($user->getuserType() == UserRolesData::ROLE_EMPLOYEE && !$args['admin']) {
      $args['countTasksActiveByUser'] = $this->em->getRepository(Task::class)->countGetTasksByUser($user);
      $args['checklistActive'] = $this->em->getRepository(ManagerChecklist::class)->count(['user' => $user, 'status' => InternTaskStatusData::NIJE_ZAPOCETO]);

      if ($user->getCompany()->getSettings()->isPlanTomorrow()) {
        $args['planRadaSutra'] = $this->em->getRepository(Plan::class)->findOneBy(['company' => $company, 'datumKreiranja' => $args['sutra']->setTime(7,0) ]);
        $args['tomorrowTimetable'] = $this->em->getRepository(Task::class)->getTasksByDate($args['sutra']);
        $args['checklistTomorrow'] = $this->em->getRepository(ManagerChecklist::class)->getInternTasksByDateUser($args['sutra'], $user);
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

      if ($user->getCompany()->getSettings()->isPlanToday()) {
        $args['logs'] = $this->em->getRepository(Task::class)->findByUser($user, $args['danas'] );
        $args['checklistToday'] = $this->em->getRepository(ManagerChecklist::class)->getInternTasksByDateUser($args['danas'], $user);
      } else {
        $tasks = $this->em->getRepository(Task::class)->getTasksByUserHomePaginator($user);
        $pagination = $paginator->paginate(
          $tasks, /* query NOT result */
          $request->query->getInt('page', 1), /*page number*/
          6,
          [
            'pageName' => 'page',  // Menjamo naziv parametra za stranicu
            'pageParameterName' => 'page',  // Menjamo naziv parametra za stranicu
            'sortFieldParameterName' => 'sort',  // Menjamo naziv parametra za sortiranje
          ]
        );
        $args['pagination'] = $pagination;

        $interns = $this->em->getRepository(ManagerChecklist::class)->getChecklistToDoHomePaginator($user);
        $pagination1 = $paginator->paginate(
          $interns, /* query NOT result */
          $request->query->getInt('page1', 1), /*page number*/
          6,
          [
            'pageName' => 'page1',  // Menjamo naziv parametra za stranicu
            'pageParameterName' => 'page1',  // Menjamo naziv parametra za stranicu
            'sortFieldParameterName' => 'sort1',  // Menjamo naziv parametra za sortiranje
          ]
        );
        $args['pagination1'] = $pagination1;

      }

      $args['countTasksUnclosedByUser'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedByUser($user);
      $args['countTasksUnclosed'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedLogsByUser($user);


      if ($user->isInTask()) {
        $args['activeStopwatch'] = $this->em->getRepository(StopwatchTime::class)->findActiveStopwatchByUser($user);
      }

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

}
