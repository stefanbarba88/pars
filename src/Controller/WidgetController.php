<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\Client;
use App\Entity\Comment;
use App\Entity\Expense;
use App\Entity\Image;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\Team;
use App\Entity\Tool;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class WidgetController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  public function adminMainSidebar(): Response {
    $loggedUser = $this->getUser();
    $args = [];

    $args['countUsers'] = $this->em->getRepository(User::class)->countUsersByLoggedUser($loggedUser);
    $args['countUsersActive'] = $this->em->getRepository(User::class)->countUsersActiveByLoggedUser($loggedUser);

    $args['countContacts'] = $this->em->getRepository(User::class)->countContacts();
    $args['countContactsActive'] = $this->em->getRepository(User::class)->countContactsActive();
    $args['countClients'] = $this->em->getRepository(Client::class)->count([]);
    $args['countClientsActive'] = $this->em->getRepository(Client::class)->countClientsActive();

    $args['countEmployees'] = $this->em->getRepository(User::class)->countEmployees();
    $args['countEmployeesActive'] = $this->em->getRepository(User::class)->countEmployeesActive();

    $args['countEmployeesOnTask'] = $this->em->getRepository(User::class)->countEmployeesOnTask();
    $args['countEmployeesOffTask'] = $this->em->getRepository(User::class)->countEmployeesOffTask();

    $args['countProjectsPermanent'] = $this->em->getRepository(Project::class)->countProjectsPermanent();
    $args['countProjectsChange'] = $this->em->getRepository(Project::class)->countProjectsChange();
    $args['countProjects'] = $this->em->getRepository(Project::class)->count([]);
    $args['countProjectsActive'] = $this->em->getRepository(Project::class)->countProjectsActive();

    $args['countCalendarRequests'] = $this->em->getRepository(Calendar::class)->countCalendarRequests();

//    $args['countComments'] = $this->em->getRepository(Comment::class)->count([]);
//    $args['countCommentsActive'] = $this->em->getRepository(Comment::class)->countCommentsActive();

    $args['countComments'] = '';
    $args['countCommentsActive'] = '';


//    $args['countTasksActive'] = $this->em->getRepository(Task::class)->countGetTasks();
//    $args['countTasksUnclosed'] = $this->em->getRepository(Task::class)->countGetTasksUnclosed();
//    $args['countTasksArchive'] = $this->em->getRepository(Task::class)->countGetTasksArchive();
//
//    $args['countTasksActiveByUser'] = $this->em->getRepository(Task::class)->countGetTasksByUser($loggedUser);
//    $args['countTasksUnclosedByUser'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedByUser($loggedUser);
//    $args['countTasksArchiveByUser'] = $this->em->getRepository(Task::class)->countGetTasksArchiveByUser($loggedUser);
//
//    $args['countTasksUnclosedLogsByUser'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedLogsByUser($loggedUser);
//    $args['countTasksUnclosedLogs'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedLogs();

    $args['countTasksActive'] = '';
    $args['countTasksUnclosed'] = '';
    $args['countTasksArchive'] = '';

    $args['countTasksActiveByUser'] = '';
    $args['countTasksUnclosedByUser'] = '';
    $args['countTasksArchiveByUser'] = '';

    $args['countTasksUnclosedLogsByUser'] = '';
    $args['countTasksUnclosedLogs'] = '';

    $args['countAllTools'] = $this->em->getRepository(Tool::class)->count([]);
    $args['countTools'] = $this->em->getRepository(Tool::class)->countTools();
    $args['countToolsActive'] = $this->em->getRepository(Tool::class)->countToolsActive();
    $args['countToolsInactive'] = $this->em->getRepository(Tool::class)->countToolsInactive();

    $args['countCars'] = $this->em->getRepository(Car::class)->count([]);
    $args['countActiveCars'] = $this->em->getRepository(Car::class)->count(['isSuspended' => false]);
    $args['countCarsActive'] = $this->em->getRepository(Car::class)->count(['isReserved' => true, 'isSuspended' => false]);
    $args['countCarsInactive'] = $this->em->getRepository(Car::class)->count(['isReserved' => false, 'isSuspended' => false]);

    $args['user'] = $loggedUser;
    $args['lastReservation'] = $this->em->getRepository(CarReservation::class)->findOneBy(['driver' => $loggedUser, 'finished' => null], ['id' => 'desc']);

    return $this->render('widget/main_admin_sidebar.html.twig', $args);
  }

  public function userProfilSidebar(User $user): Response {

    $args['user'] = $user;
//    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['user' => $user]);
    $args['users'] = $this->em->getRepository(User::class)->findAll();

    return $this->render('widget/user_profil_sidebar.html.twig', $args);
  }

  public function support(): Response {

    return $this->render('widget/support.html.twig');
  }

  public function employeeProfilSidebar(User $user): Response {

    $args['user'] = $user;
//    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['user' => $user]);
    $args['users'] = $this->em->getRepository(User::class)->findAll();

    return $this->render('widget/employee_profil_sidebar.html.twig', $args);
  }

  public function userProfilNavigation(User $user): Response {

    $args['user'] = $user;

    return $this->render('widget/users_nav.html.twig', $args);
  }

  public function employeeProfilNavigation(User $user): Response {

    $args['user'] = $user;
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
    return $this->render('widget/employee_nav.html.twig', $args);
  }

  public function carProfilNavigation(Car $car): Response {

    $args['car'] = $car;
    $args['countExpenses'] = $this->em->getRepository(Expense::class)->countExpenseByCar($car);
    $args['countReservations'] = $this->em->getRepository(CarReservation::class)->countReservationByCar($car);

    return $this->render('widget/car_nav.html.twig', $args);
  }

  public function toolProfilNavigation(Tool $tool): Response {

    $args['tool'] = $tool;
//    $args['countExpenses'] = $this->em->getRepository(Expense::class)->countExpenseByCar($car);
//    $args['countReservations'] = $this->em->getRepository(CarReservation::class)->countReservationByCar($car);

    return $this->render('widget/tool_nav.html.twig', $args);
  }

  public function projectProfilNavigation(Project $project): Response {

    $args['project'] = $project;

    return $this->render('widget/project_nav.html.twig', $args);
  }

  public function clientProfilNavigation(Client $client): Response {

    $args['client'] = $client;

    return $this->render('widget/clients_nav.html.twig', $args);
  }

  public function rightSidebar(): Response {

    return $this->render('widget/right_sidebar.html.twig');
  }

  public function confirmationModal(string $message): Response {

    $args['message'] = $message;

    return $this->render('widget/confirmation_modal.html.twig', $args);
  }

  public function header(): Response {

    $user = $this->getUser();
    $args['logged'] = $user;

    return $this->render('includes/header.html.twig', $args);
  }

  public function headerUser(): Response {

    $user = $this->getUser();
    $args['logged'] = $user;
//    dd($args);
    return $this->render('includes/header_user.html.twig', $args);
  }

}
