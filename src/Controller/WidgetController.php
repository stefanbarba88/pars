<?php

namespace App\Controller;

use App\Classes\Data\InternTaskStatusData;
use App\Classes\Data\TaskStatusData;
use App\Classes\Data\UserRolesData;
use App\Entity\Addon;
use App\Entity\Calendar;
use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\Client;
use App\Entity\Comment;
use App\Entity\Company;
use App\Entity\Expense;
use App\Entity\ManagerChecklist;
use App\Entity\Overtime;
use App\Entity\Plan;
use App\Entity\Project;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\Ticket;
use App\Entity\Tool;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

use JetBrains\PhpStorm\NoReturn;
use Proxies\__CG__\App\Entity\FastTask;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class WidgetController extends AbstractController {
    public function __construct(private readonly ManagerRegistry $em) {
    }

    #[NoReturn] #[Route('/sidebar', name: 'app_sidebar')]
    public function adminMainSidebar(?string $_route = null, SessionInterface $session): Response {
        $loggedUser = $this->getUser();
        $args = [];
        $args['user'] = $loggedUser;
        $args['admin'] = false;

        if ($session->has('admin')) {
            $args['admin'] = true;
        }

        $args['current_route'] = $_route;

        if ($loggedUser->getuserType() == UserRolesData::ROLE_EMPLOYEE && $args['admin'] == false) {

            $args['countTasksActiveByUser'] = $this->em->getRepository(Task::class)->countGetTasksByUser($loggedUser);
            $args['countTasksUnclosedByUser'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedByUser($loggedUser);
//    $args['countTasksArchiveByUser'] = $this->em->getRepository(Task::class)->countGetTasksArchiveByUser($loggedUser);
            $args['countTasksUnclosedLogsByUser'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedLogsByUser($loggedUser);

            $args['checklistActive'] = $this->em->getRepository(ManagerChecklist::class)->findBy(['user' => $this->getUser(), 'status' => InternTaskStatusData::NIJE_ZAPOCETO]);
            $args['countChecklistActive'] = count($args['checklistActive']);

            if ($loggedUser->getCompany()->getSettings()->isCar()) {
                $args['lastReservation'] = $this->em->getRepository(CarReservation::class)->findOneBy(['driver' => $loggedUser, 'finished' => null], ['id' => 'desc']);
            }

            if ($loggedUser->isInTask()) {
                $args['activeStopwatch'] = $this->em->getRepository(StopwatchTime::class)->findActiveStopwatchByUser($loggedUser);
            }

        } else {

            //modul Basic
            $args['countUsers'] = $this->em->getRepository(User::class)->countUsersByLoggedUser($loggedUser);
            $args['countUsersActive'] = $this->em->getRepository(User::class)->countUsersActiveByLoggedUser($loggedUser);
            $args['countContacts'] = $this->em->getRepository(User::class)->countContacts();
            $args['countContactsActive'] = $this->em->getRepository(User::class)->countContactsActive();
            $args['countClientsActive'] = $this->em->getRepository(Client::class)->countClientsActive();

            $args['countEmployees'] = $this->em->getRepository(User::class)->countEmployees();
            $args['countEmployeesActive'] = $this->em->getRepository(User::class)->countEmployeesActive();

            $args['countProjectsArchive'] = $this->em->getRepository(Project::class)->count(['isSuspended' => true, 'company' => $loggedUser->getCompany()]);
            $args['countProjectsActive'] = $this->em->getRepository(Project::class)->count(['isSuspended' => false, 'company' => $loggedUser->getCompany()]);

            $args['countComments'] = $this->em->getRepository(Comment::class)->count([]);
            $args['countCommentsActive'] = $this->em->getRepository(Comment::class)->countCommentsActive();

            $args['countLogCheck'] = $this->em->getRepository(StopwatchTime::class)->findAllToCheckCount();
            $args['countTasksAdminActive'] = $this->em->getRepository(Task::class)->countGetTasksByUser($loggedUser);
            $args['countTasksActive'] = $this->em->getRepository(Task::class)->countGetTasks();
            $args['countTasksUnclosed'] = $this->em->getRepository(Task::class)->countGetTasksUnclosed();
            $args['countTasksArchive'] = $this->em->getRepository(Task::class)->countGetTasksArchive();
            $nezatvoreniLogovi = $this->em->getRepository(Task::class)->getTasksUnclosedLogsPaginator();
            $args['countTasksUnclosedLogs'] = count($nezatvoreniLogovi);

            $args['countTickets'] = $this->em->getRepository(Ticket::class)->count(['company' => $loggedUser->getCompany(), 'status' => 0]);

//      $args['countProjectsPermanent'] = $this->em->getRepository(Project::class)->count(['isSuspended' => false, 'type' => TipProjektaData::FIKSNO, 'company' => $loggedUser->getCompany()]);
//      $args['countProjectsChange'] = $this->em->getRepository(Project::class)->count(['isSuspended' => false, 'type' => TipProjektaData::LETECE, 'company' => $loggedUser->getCompany()]);
//      $args['countProjectsMix'] = $this->em->getRepository(Project::class)->count(['isSuspended' => false, 'type' => TipProjektaData::KOMBINOVANO, 'company' => $loggedUser->getCompany()]);

            $args['countPlanRada'] = $this->em->getRepository(Plan::class)->countPlanRadaActive();


            //modul kalendar
            if ($loggedUser->getCompany()->getSettings()->isCalendar()) {
                $args['countEmployeesOnTask'] = $this->em->getRepository(User::class)->countEmployeesOnTask();
                $args['countEmployeesOffTask'] = $this->em->getRepository(User::class)->countEmployeesOffTask();
                $args['countOvertime'] = $this->em->getRepository(Overtime::class)->count(['status' => 0, 'company' => $loggedUser->getCompany()]);
                $args['countCalendarRequests'] = $this->em->getRepository(Calendar::class)->countCalendarRequests();
            }


            //modul Vozila
            if ($loggedUser->getCompany()->getSettings()->isCar()) {
                $args['lastReservation'] = $this->em->getRepository(CarReservation::class)->findOneBy(['driver' => $loggedUser, 'finished' => null], ['id' => 'desc']);
                $args['countCars'] = $this->em->getRepository(Car::class)->count(['company' => $loggedUser->getCompany()]);
                $args['countActiveCars'] = $this->em->getRepository(Car::class)->count(['isSuspended' => false, 'company' => $loggedUser->getCompany()]);
                $args['countCarsActive'] = $this->em->getRepository(Car::class)->count(['isReserved' => true, 'isSuspended' => false, 'company' => $loggedUser->getCompany()]);
                $args['countCarsInactive'] = $this->em->getRepository(Car::class)->count(['isReserved' => false, 'isSuspended' => false, 'company' => $loggedUser->getCompany()]);
            }

            //modul Oprema
            if ($loggedUser->getCompany()->getSettings()->isTool()) {
                $args['countAllTools'] = $this->em->getRepository(Tool::class)->count(['company' => $loggedUser->getCompany()]);
                $args['countTools'] = $this->em->getRepository(Tool::class)->countTools();
                $args['countToolsActive'] = $this->em->getRepository(Tool::class)->countToolsActive();
                $args['countToolsInactive'] = $this->em->getRepository(Tool::class)->countToolsInactive();
            }
            $args['checklistActive'] = $this->em->getRepository(ManagerChecklist::class)->findBy(['status' => InternTaskStatusData::NIJE_ZAPOCETO, 'company' => $loggedUser->getCompany()]);
            $args['checklistOnline'] = $this->em->getRepository(ManagerChecklist::class)->findBy(['status' => InternTaskStatusData::ZAPOCETO, 'company' => $loggedUser->getCompany()]);
            $args['verify'] = $this->em->getRepository(Task::class)->findBy(['status' => TaskStatusData::ZAVRSENO, 'company' => $loggedUser->getCompany()]);

            $args['countChecklistActive'] = count($args['checklistActive']);
            $args['countChecklistOnline'] = count($args['checklistOnline']);
            $args['countVerify'] = count($args['verify']);

        }

//    $args['checklistCreatedActive'] = $this->em->getRepository(ManagerChecklist::class)->findBy(['createdBy' => $this->getUser(), 'status' => InternTaskStatusData::NIJE_ZAPOCETO]);


        return $this->render('widget/main_admin_sidebar.html.twig', $args);
    }

    public function userProfilSidebar(User $user, SessionInterface $session): Response {
        $loggedUser = $this->getUser();
        $args['user'] = $user;
//    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['user' => $user]);
        $args['users'] = $this->em->getRepository(User::class)->findBy(['company' => $loggedUser->getCompany()]);
        $args['admin'] = false;

        if ($session->has('admin')) {
            $args['admin'] = true;
        }
        return $this->render('widget/user_profil_sidebar.html.twig', $args);
    }

    public function support(): Response {

        return $this->render('widget/support.html.twig');
    }

    public function employeeProfilSidebar(User $user, SessionInterface $session): Response {

        $args['user'] = $user;
//    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['user' => $user]);
        $args['users'] = $this->em->getRepository(User::class)->findBy(['company' => $user->getCompany()]);
        $args['admin'] = false;

        if ($session->has('admin')) {
            $args['admin'] = true;
        }
        return $this->render('widget/employee_profil_sidebar.html.twig', $args);
    }

    public function userProfilNavigation(User $user, SessionInterface $session): Response {

        $args['user'] = $user;
        $args['admin'] = false;

        if ($session->has('admin')) {
            $args['admin'] = true;
        }
        return $this->render('widget/users_nav.html.twig', $args);
    }

    public function employeeProfilNavigation(User $user, SessionInterface $session): Response {
        $args['admin'] = false;

        if ($session->has('admin')) {
            $args['admin'] = true;
        }
        $args['user'] = $user;
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

        return $this->render('widget/employee_nav.html.twig', $args);
    }

    public function carProfilNavigation(Car $car, SessionInterface $session): Response {
        $args['admin'] = false;

        if ($session->has('admin')) {
            $args['admin'] = true;
        }
        $args['car'] = $car;
        $args['countExpenses'] = $this->em->getRepository(Expense::class)->countExpenseByCar($car);
        $args['countReservations'] = $this->em->getRepository(CarReservation::class)->countReservationByCar($car);

        return $this->render('widget/car_nav.html.twig', $args);
    }

    public function toolProfilNavigation(Tool $tool, SessionInterface $session): Response {
        $args['admin'] = false;

        if ($session->has('admin')) {
            $args['admin'] = true;
        }
        $args['tool'] = $tool;
//    $args['countExpenses'] = $this->em->getRepository(Expense::class)->countExpenseByCar($car);
//    $args['countReservations'] = $this->em->getRepository(CarReservation::class)->countReservationByCar($car);

        return $this->render('widget/tool_nav.html.twig', $args);
    }

    public function projectProfilNavigation(Project $project, SessionInterface $session): Response {
        $args['admin'] = false;

        if ($session->has('admin')) {
            $args['admin'] = true;
        }
        $args['project'] = $project;

        return $this->render('widget/project_nav.html.twig', $args);
    }

    public function clientProfilNavigation(Client $client, SessionInterface $session): Response {
        $args['admin'] = false;

        if ($session->has('admin')) {
            $args['admin'] = true;
        }
        $args['client'] = $client;

        return $this->render('widget/clients_nav.html.twig', $args);
    }

    public function rightSidebar(): Response {

        $args['checklistUsers'] = $this->em->getRepository(User::class)->getUsersForQuickChecklist();
//    $args['countNotes'] = count($this->em->getRepository(Notes::class)->findBy(['isSuspended' => false, 'user' => $this->getUser()]));
//    $args['timeTask'] = count($this->em->getRepository(TimeTask::class)->findBy(['finish' => null, 'user' => $this->getUser()]));


        return $this->render('widget/right_sidebar.html.twig', $args);
    }

    public function confirmationModal(string $message): Response {

        $args['message'] = $message;

        return $this->render('widget/confirmation_modal.html.twig', $args);
    }

    public function header(SessionInterface $session): Response {

        $user = $this->getUser();
        $args['logged'] = $user;

        $args['admin'] = false;
        if ($session->has('admin')) {
            $args['admin'] = true;
        }

        return $this->render('includes/header.html.twig', $args);
    }

    public function headerUser(SessionInterface $session): Response {

        $user = $this->getUser();
        $args['logged'] = $user;
        $args['admin'] = false;
        if ($session->has('admin')) {
            $args['admin'] = true;
        }
//    dd($args);
        return $this->render('includes/header_user.html.twig', $args);
    }

    public function headerKadrovska(): Response {

        $user = $this->getUser();
        $args['logged'] = $user;

        return $this->render('_kadrovska/includes/header.html.twig', $args);
    }

    public function headerUserKadrovska(): Response {

        $user = $this->getUser();
        $args['logged'] = $user;
//    dd($args);
        return $this->render('_kadrovska/includes/header_user.html.twig', $args);
    }

    public function rightSidebarKadrovska(): Response {

        $args['checklistUsers'] = $this->em->getRepository(User::class)->getUsersForQuickChecklist();
//    $args['countNotes'] = count($this->em->getRepository(Notes::class)->findBy(['isSuspended' => false, 'user' => $this->getUser()]));
//    $args['timeTask'] = count($this->em->getRepository(TimeTask::class)->findBy(['finish' => null, 'user' => $this->getUser()]));


        return $this->render('_kadrovska/widget/right_sidebar.html.twig', $args);
    }
    public function adminMainSidebarKadrovska(?string $_route = null): Response {
        $loggedUser = $this->getUser();
        $args = [];
        $args['user'] = $loggedUser;

        $args['current_route'] = $_route;

        if ($loggedUser->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            $args['countEmployees'] = $this->em->getRepository(User::class)->getUsersCount($loggedUser);
            $args['countEmployeesArchive'] = $this->em->getRepository(User::class)->count(['isKadrovska' => true, 'isSuspended' => true, 'company' => $loggedUser->getCompany()]);

            $args['countAddons'] = $this->em->getRepository(Addon::class)->count(['company' => $loggedUser->getCompany(), 'isSuspended' => false]);
        } else {

            $args['countUsers'] = $this->em->getRepository(User::class)->count(['isKadrovska' => true, 'isSuspended' => false, 'userType' => UserRolesData::ROLE_MANAGER]);
            $args['countUsersArchive'] = $this->em->getRepository(User::class)->count(['isKadrovska' => true, 'isSuspended' => true, 'userType' => UserRolesData::ROLE_MANAGER]);

            $args['countCompanies'] = $this->em->getRepository(Company::class)->count(['firma' => $loggedUser->getCompany()->getId(), 'isSuspended' => false]);
            $args['countCompaniesArchive'] = $this->em->getRepository(Company::class)->count(['firma' => $loggedUser->getCompany()->getId(), 'isSuspended' => true]);

            $args['countEmployees'] = $this->em->getRepository(User::class)->getUsersCount($loggedUser);
            $args['countEmployeesArchive'] = $this->em->getRepository(User::class)->count(['isKadrovska' => true, 'isSuspended' => true, 'userType' => UserRolesData::ROLE_EMPLOYEE]);


        }

        return $this->render('_kadrovska/widget/main_admin_sidebar.html.twig', $args);
    }

    public function companyProfilNavigationKadrovska(Company $company): Response {

        $args['company'] = $company;

        return $this->render('_kadrovska/widget/company_nav.html.twig', $args);
    }

    public function userProfilNavigationKadrovska(User $user): Response {

        $args['user'] = $user;

        return $this->render('_kadrovska/widget/users_nav.html.twig', $args);
    }

    public function employeeProfilNavigationKadrovska(User $user): Response {

        $args['user'] = $user;
        return $this->render('_kadrovska/widget/employee_nav.html.twig', $args);
    }

    public function userProfilSidebarKadrovska(User $user): Response {
        $loggedUser = $this->getUser();
        $args['user'] = $user;
//    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['user' => $user]);
        $args['users'] = $this->em->getRepository(User::class)->findBy(['company' => $loggedUser->getCompany()]);

        return $this->render('_kadrovska/widget/user_profil_sidebar.html.twig', $args);
    }

    public function supportKadrovska(): Response {

        return $this->render('_kadrovska/widget/support.html.twig');
    }

    public function employeeProfilSidebarKadrovska(User $user): Response {

        $args['user'] = $user;
//    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['user' => $user]);
        $args['users'] = $this->em->getRepository(User::class)->findBy(['company' => $user->getCompany()]);

        return $this->render('_kadrovska/widget/employee_profil_sidebar.html.twig', $args);
    }
}
