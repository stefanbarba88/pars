<?php

namespace App\Controller;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\CalendarData;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\TipProjektaData;
use App\Classes\Data\UserRolesData;
use App\Classes\Slugify;
use App\Entity\Availability;
use App\Entity\Calendar;
use App\Entity\CarReservation;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Expense;
use App\Entity\Holiday;
use App\Entity\Image;
use App\Entity\ManagerChecklist;
use App\Entity\Notes;
use App\Entity\Overtime;
use App\Entity\Pdf;
use App\Entity\Task;
use App\Entity\ToolReservation;
use App\Entity\User;
use App\Entity\UserHistory;
use App\Entity\ZaposleniPozicija;
use App\Form\ProjectFormType;
use App\Form\UserEditAccountFormType;
use App\Form\UserEditImageFormType;
use App\Form\UserEditInfoFormType;
use App\Form\UserEditSelfAccountFormType;
use App\Form\UserSuspendedFormType;
use App\Service\UploadService;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;

use Exception;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/employees')]
class EmployeeController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_employees')]
  public function list(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    $args = [];

    $search = [];

    $search['ime'] = $request->query->get('ime');
    $search['prezime'] = $request->query->get('prezime');
    $search['pozicija'] = $request->query->get('pozicija');
    $search['vrsta'] = $request->query->get('vrsta');

    $users = $this->em->getRepository(User::class)->getEmployeesPaginator($korisnik, $search, 0);
//    $users = $this->em->getRepository(User::class)->getEmployeesPaginator($type);

    $pagination = $paginator->paginate(
      $users, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['pozicije'] = $this->em->getRepository(ZaposleniPozicija::class)->findBy(['company' => $korisnik->getCompany(), 'isSuspended' => false]);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/list.html.twig', $args);
    }

    return $this->render('employee/list.html.twig', $args);
  }

  #[Route('/list-project-type/', name: 'app_employees_project_type')]
  public function listProjectType(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    $args = [];


//    $users = $this->em->getRepository(User::class)->getEmployeesPaginator($korisnik, $search, 0);
    $args['users'] = $this->em->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false, 'company' => $korisnik->getCompany()], ['prezime' => 'ASC']);
//    $args['usersL'] = $this->em->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false, 'ProjectType' => TipProjektaData::LETECE], ['prezime' => 'ASC']);
    if ($request->isMethod('POST')) {

      $data = $request->request->all();
      if (isset($data['users'])) {
        $sviZaposleni = $this->em->getRepository(User::class)->getZaposleniNotMix();
        foreach ($sviZaposleni as $zaposleni) {
          if (!in_array($zaposleni->getId(), $data['users'])){
            $zaposleni->setProjectType(TipProjektaData::LETECE);
          } else {
            $zaposleni->setProjectType(TipProjektaData::FIKSNO);
          }
          $this->em->getRepository(User::class)->save($zaposleni);
        }
      }

      return $this->render('employee/list_project_control.html.twig', $args);

    }

//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      return $this->render('employee/phone/list.html.twig', $args);
//    }
    return $this->render('employee/list_project_control.html.twig', $args);
  }

  #[Route('/list-archive/', name: 'app_employees_archive')]
  public function archive(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    $args = [];

    $search = [];

    $search['ime'] = $request->query->get('ime');
    $search['prezime'] = $request->query->get('prezime');
    $search['pozicija'] = $request->query->get('pozicija');
    $search['vrsta'] = $request->query->get('vrsta');

    $type = $request->query->getInt('type');
    $users = $this->em->getRepository(User::class)->getEmployeesPaginator($korisnik, $search, 1);
//    $users = $this->em->getRepository(User::class)->getEmployeesPaginator($type);

    $pagination = $paginator->paginate(
      $users, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['pozicije'] = $this->em->getRepository(ZaposleniPozicija::class)->findBy(['company' => $korisnik->getCompany(), 'isSuspended' => false]);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/archive.html.twig', $args);
    }

    return $this->render('employee/archive.html.twig', $args);
  }

  #[Route('/view-profile/{id}', name: 'app_employee_profile_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['user'] = $usr;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/view_profile.html.twig', $args);
      }
      return $this->render('employee/phone/view_profile.html.twig', $args);
    }
    return $this->render('employee/view_profile.html.twig', $args);
  }

  #[Route('/view-activity/{id}', name: 'app_employee_activity_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewActivity(User $usr, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args = [];
    $args['user'] = $usr;
//    $tasks = $this->em->getRepository(Task::class)->getTasksArchiveByUser($usr);
    $tasks = $this->em->getRepository(Task::class)->getTasksArchiveByUserPaginator($usr);

    $pagination = $paginator->paginate(
      $tasks, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      10
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/phone/admin_view_activity.html.twig', $args);
      }
      return $this->render('employee/phone/view_activity.html.twig', $args);
    }
    return $this->render('employee/view_activity.html.twig', $args);
  }

  #[Route('/view-checklist/{id}', name: 'app_employee_checklist_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewChecklist(User $usr, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();

    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    $args = [];
    $args['user'] = $usr;
//    $tasks = $this->em->getRepository(Task::class)->getTasksArchiveByUser($usr);
    $tasks = $this->em->getRepository(ManagerChecklist::class)->getChecklistToDoPaginator($usr);
    $pagination = $paginator->paginate(
      $tasks, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      10
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/phone/admin_view_checklist.html.twig', $args);
      }
      return $this->render('employee/phone/view_checklist.html.twig', $args);
    }
    return $this->render('employee/view_checklist.html.twig', $args);
  }

  #[Route('/view-calendar/{id}', name: 'app_employee_calendar_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewCalendar(User $usr, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    $calendars = $usr->getCalendars()->toArray();
    $compareFunction = function ($a, $b) {
      return $b->getId() - $a->getId();
    };
    usort($calendars, $compareFunction);

    $pagination = $paginator->paginate(
      $calendars, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      10
    );

    $args['pagination'] = $pagination;

    $args['user'] = $usr;
    $year = date('Y');

    if ($usr->getCreated()->format('Y') != $year) {
      $args['noRadnihDana'] = $this->em->getRepository(Holiday::class)->brojRadnihDanaDoJuce();
      $args['noDays'] = $this->em->getRepository(Availability::class)->getDaysByUser($usr, $year);
    } else {
      $args['noRadnihDana'] = $this->em->getRepository(Holiday::class)->brojRadnihDanaDoJuceUser($usr);
      $args['noDays'] = $this->em->getRepository(Availability::class)->getDaysByUserUser($usr, $year);
    }

    $args['overtime'] = $this->em->getRepository(Overtime::class)->getOvertimeByUser($usr);
    $args['noRequests'] = $this->em->getRepository(Calendar::class)->getRequestByUser($usr, $year);
    $args['dostupnosti'] = $this->em->getRepository(Availability::class)->getDostupnostByUser($usr);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/phone/admin_view_calendar.html.twig', $args);
      }
      return $this->render('employee/phone/view_calendar.html.twig', $args);
    }
    return $this->render('employee/view_calendar.html.twig', $args);
  }

  #[Route('/view-cars/{id}', name: 'app_employee_car_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewCar(User $usr, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['user'] = $usr;
    $reservations = $this->em->getRepository(CarReservation::class)->getReservationsByUserPaginator($usr);
    $args['lastReservation'] = $this->em->getRepository(CarReservation::class)->findOneBy(['driver' => $usr], ['id' => 'desc']);
    $expenses = $this->em->getRepository(Expense::class)->getExpensesByUserPaginator($usr);


    $pagination = $paginator->paginate(
      $reservations, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      5,
      [
        'pageName' => 'page',  // Menjamo naziv parametra za stranicu
        'pageParameterName' => 'page',  // Menjamo naziv parametra za stranicu
        'sortFieldParameterName' => 'sort',  // Menjamo naziv parametra za sortiranje
    ]
    );

    $pagination1 = $paginator->paginate(
      $expenses, /* query NOT result */
      $request->query->getInt('page1', 1), /*page number*/
      5,
      [
        'pageName' => 'page1',  // Menjamo naziv parametra za stranicu
        'pageParameterName' => 'page1',  // Menjamo naziv parametra za stranicu
        'sortFieldParameterName' => 'sort1',  // Menjamo naziv parametra za sortiranje
      ]
    );

    $args['pagination'] = $pagination;
    $args['pagination1'] = $pagination1;


    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/phone/admin_view_cars.html.twig', $args);
      }
      return $this->render('employee/phone/view_cars.html.twig', $args);
    }
    return $this->render('employee/view_cars.html.twig', $args);
  }

  #[Route('/view-tools/{id}', name: 'app_employee_tools_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewTools(User $usr, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['user'] = $usr;
    $reservations = $this->em->getRepository(ToolReservation::class)->getReservationsByUserPaginator($usr);

    $pagination = $paginator->paginate(
      $reservations, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      10
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/phone/admin_view_tools.html.twig', $args);
      }
      return $this->render('employee/phone/view_tools.html.twig', $args);
    }

    return $this->render('employee/view_tools.html.twig', $args);
  }

  #[Route('/view-docs/{id}', name: 'app_employee_docs_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewDocs(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['user'] = $usr;
    $args['pdfs'] = $this->em->getRepository(User::class)->getPdfsByUser($usr);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/view_docs.html.twig', $args);
      }
      return $this->render('employee/phone/view_docs.html.twig', $args);
    }

    return $this->render('employee/view_docs.html.twig', $args);
  }

  #[Route('/view-images/{id}', name: 'app_employee_images_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewImages(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['user'] = $usr;
    $args['images'] = $this->em->getRepository(User::class)->getImagesByUser($usr);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/view_images.html.twig', $args);
      }
      return $this->render('employee/phone/view_images.html.twig', $args);
    }

    return $this->render('employee/view_images.html.twig', $args);
  }

  #[Route('/view-comments/{id}', name: 'app_employee_comments_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewComments(User $usr, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['user'] = $usr;
    $reservations = $this->em->getRepository(Comment::class)->getCommentsByUserPaginator($usr);

    $pagination = $paginator->paginate(
      $reservations, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      10
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/phone/admin_view_comments.html.twig', $args);
      }
      return $this->render('employee/phone/view_comments.html.twig', $args);
    }

    return $this->render('employee/view_comments.html.twig', $args);
  }

  #[Route('/view-notes/{id}', name: 'app_employee_notes_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewNotes(User $usr, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    $notes = $this->em->getRepository(Notes::class)->getNotesByUserPaginator($usr);


    $pagination = $paginator->paginate(
      $notes, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      10
    );

    $args['pagination'] = $pagination;


    $args['user'] = $usr;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/phone/admin_view_notes.html.twig', $args);
      }
      return $this->render('employee/phone/view_notes.html.twig', $args);
    }

    return $this->render('employee/view_notes.html.twig', $args);

  }

  #[Route('/reports', name: 'app_employee_reports')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formReport(Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    if ($request->isMethod('POST')) {
      $data = $request->request->all();
//      dd($data);
//      $args['reports'] = $this->em->getRepository(Project::class)->getReport($data['report_form']);
      $args['reports'] = $this->em->getRepository(User::class)->getReport($data['report_form']);

      $args['intern'] = $this->em->getRepository(ManagerChecklist::class)->getInternTasks($data['report_form']);

      $args['period'] = $data['report_form']['period'];
      $args['user'] = $this->em->getRepository(User::class)->find($data['report_form']['zaposleni']);

      if (isset($data['report_form']['datum'])){
        $args['datum'] = 1;
      }
      if (isset($data['report_form']['opis'])){
        $args['opis'] = 1;
      }
      if (isset($data['report_form']['klijent'])){
        $args['klijent'] = 1;
      }
      if (isset($data['report_form']['start'])){
        $args['start'] = 1;
      }
      if (isset($data['report_form']['stop'])){
        $args['stop'] = 1;
      }
      if (isset($data['report_form']['razlika'])){
        $args['razlika'] = 1;
      }
      if (isset($data['report_form']['razlikaz'])){
        $args['razlikaz'] = 1;
      }
      if (isset($data['report_form']['ukupno'])){
        $args['ukupno'] = 1;
      }
      if (isset($data['report_form']['ukupnoz'])){
        $args['ukupnoz'] = 1;
      }
      if (isset($data['report_form']['zaduzeni'])){
        $args['zaduzeni'] = 1;
      }
      if (isset($data['report_form']['napomena'])){
        $args['napomena'] = 1;
      }
      if (isset($data['report_form']['checklist'])){
        $args['checklist'] = 1;
      }
      if (isset($data['report_form']['robotika'])){
        $args['robotika'] = 1;
      }


      return $this->render('report_employee/view.html.twig', $args);

    }

    $args = [];

    $args['users'] =  $this->em->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'company' => $this->getUser()->getCompany(), 'isSuspended' => false],['prezime' => 'ASC']);
    $args['categories'] = $this->em->getRepository(Category::class)->getCategoriesTask();
    return $this->render('report_employee/control.html.twig', $args);
  }

  #[Route('/reports-archive', name: 'app_employee_archive_reports')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formReportArchive(Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    if ($request->isMethod('POST')) {
      $data = $request->request->all();
//      dd($data);
//      $args['reports'] = $this->em->getRepository(Project::class)->getReport($data['report_form']);
      $args['reports'] = $this->em->getRepository(User::class)->getReport($data['report_form']);
      $args['intern'] = $this->em->getRepository(ManagerChecklist::class)->getInternTasks($data['report_form']);

      $args['period'] = $data['report_form']['period'];
      $args['user'] = $this->em->getRepository(User::class)->find($data['report_form']['zaposleni']);

      if (isset($data['report_form']['datum'])){
        $args['datum'] = 1;
      }
      if (isset($data['report_form']['opis'])){
        $args['opis'] = 1;
      }
      if (isset($data['report_form']['klijent'])){
        $args['klijent'] = 1;
      }
      if (isset($data['report_form']['start'])){
        $args['start'] = 1;
      }
      if (isset($data['report_form']['stop'])){
        $args['stop'] = 1;
      }
      if (isset($data['report_form']['razlika'])){
        $args['razlika'] = 1;
      }
      if (isset($data['report_form']['razlikaz'])){
        $args['razlikaz'] = 1;
      }
      if (isset($data['report_form']['ukupno'])){
        $args['ukupno'] = 1;
      }
      if (isset($data['report_form']['ukupnoz'])){
        $args['ukupnoz'] = 1;
      }
      if (isset($data['report_form']['zaduzeni'])){
        $args['zaduzeni'] = 1;
      }
      if (isset($data['report_form']['napomena'])){
        $args['napomena'] = 1;
      }
      if (isset($data['report_form']['checklist'])){
        $args['checklist'] = 1;
      }
      if (isset($data['report_form']['robotika'])){
        $args['robotika'] = 1;
      }


      return $this->render('report_employee/view.html.twig', $args);

    }

    $args = [];

    $args['users'] =  $this->em->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'company' => $this->getUser()->getCompany(), 'isSuspended' => true],['prezime' => 'ASC']);
    $args['categories'] = $this->em->getRepository(Category::class)->getCategoriesTask();
    return $this->render('report_employee/control.html.twig', $args);
  }

  #[Route('/report-xls', name: 'app_employee_report_xls')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function xlsReport(Request $request, Slugify $slugify)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $datum = $request->query->get('date');
    $type = $request->query->get('type');



    $user = $this->em->getRepository(User::class)->find($request->query->get('user'));


    $report = $this->em->getRepository(User::class)->getReportXls($datum, $user);

    $publicDirectory = $this->getParameter('kernel.project_dir') . '/var/excel';
    $excelFilepath =  $publicDirectory . '/'.$user->getFullName() . '_'. $datum .'.xls';
    header('Content-Type: application/openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.time() . '_'. $slugify->slugify($user->getFullName(), '_') . $type . '.xls"');


//    $report = $this->em->getRepository(Project::class)->getReportXls($datum, $projekat);


//    $klijent = $projekat->getClientsJson();

    $spreadsheet = new Spreadsheet();

    $sheet = $spreadsheet->getActiveSheet();

    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    $sheet->getPageSetup()->setFitToWidth(1);
    $sheet->getPageSetup()->setFitToHeight(0);
    $sheet->getPageMargins()->setTop(1);
    $sheet->getPageMargins()->setRight(0.75);
    $sheet->getPageMargins()->setLeft(0.75);
    $sheet->getPageMargins()->setBottom(1);
    $styleArray = [
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_THICK,
          'color' => ['argb' => '000000'],
        ],
      ],
    ];

    if (!empty ($report)) {
      if ($type == 1) {
        $spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $maxCellWidth = 50;
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setWidth($maxCellWidth);
        $sheet->getColumnDimension('D')->setWidth($maxCellWidth);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setWidth($maxCellWidth);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);


        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->setCellValue('A1', $user->getFullName() . ': ' . $datum);
        $style = $sheet->getStyle('A1:N1');
        $font = $style->getFont();
        $font->setSize(18); // Postavite veličinu fonta na 14
        $font->setBold(true); // Postavite font kao boldiran

        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:K2');

        $sheet->mergeCells('N2:N3');

        $sheet->setCellValue('A2', 'Datum');
        $sheet->setCellValue('B2', 'Opis izvedenog posla');
        $sheet->setCellValue('N2', 'Izvršioci');

        $sheet->getStyle('A2:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


        $sheet->getStyle('B2:J2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:J2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


        $sheet->getStyle('N2:N3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('N2:N3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);



        $sheet->setCellValue('B3', 'Projekat / Kategorija');
        $sheet->setCellValue('C3', 'Aktivnosti');
        $sheet->setCellValue('D3', 'Obrada podataka*');
        $sheet->setCellValue('E3', 'Klijent*');
        $sheet->setCellValue('F3', 'Start');
        $sheet->setCellValue('G3', 'Kraj');
        $sheet->setCellValue('H3', 'Razlika');
        $sheet->setCellValue('I3', 'Razlika*');
        $sheet->setCellValue('J3', 'Ukupno');
        $sheet->setCellValue('K3', 'Ukupno*');
        $sheet->setCellValue('L3', 'Napomena');
        $sheet->setCellValue('M3', 'Robotika');


        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('D3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('I3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('K3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('L3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('M3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


        $font = $sheet->getStyle('A')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('B')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('C')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('D')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('E')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('F')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('G')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('H')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('I')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('J')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('K')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('L')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('M')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('N')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14



        $start = 4;
        $start1 = 4;
        $rows = [];

        foreach ($report[1] as $item) {

          if ($item != 1) {
            $offset = $item - 1;
            $sheet->mergeCells('A' . $start . ':A' . $start + $offset);
            $sheet->mergeCells('K' . $start . ':K' . $start + $offset);
            $sheet->mergeCells('J' . $start . ':J' . $start + $offset);

          }
          $rows[] = $start;
          $start = $start + $item;
        }
        $row = 0;
        $row1 = 0;
        $startAktivnosti = 4;


        foreach ($report[2] as $key => $item) {
          $start1 = $rows[$row1];
          $sheet->setCellValue('J' . $start1, $item['vremeR']);
          $sheet->getStyle('J' . $start1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('J' . $start1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

          $sheet->setCellValue('K' . $start1, $item['vreme']);
          $sheet->getStyle('K' . $start1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('K' . $start1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
          $row1++;
        }
        foreach ($report[0] as $key => $item) {

          $dan = '';

          if ($item[0]['dan'] == 1) {
            $dan = '(Praznik)';
          }
          if ($item[0]['dan'] == 3) {
            $dan = '(Nedelja)';
          }
          if ($item[0]['dan'] == 5) {
            $dan = '(Praznik i nedelja)';
          }

          $start = $rows[$row];

          if (empty($dan)) {
            $sheet->setCellValue('A' . $start, $key);
          } else {
            $sheet->setCellValue('A' . $start, $key . "\n" . $dan);
          }

          $sheet->getStyle('A' . $start)->getAlignment()->setWrapText(true);
          $sheet->getStyle('A' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('A' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

          $row++;
        }
        $row = 0;
        foreach ($report[3] as $item) {
          $start = $rows[$row];

          $hR = 0;
          $mR = 0;
          $h = 0;
          $m = 0;

          foreach ($item as $stopwatch) {
            $robotika = '';
            if ($stopwatch['robotika'] == 1) {
              $robotika = 'Da';
            }

            $aktivnosti = [];
            foreach ($stopwatch['activity'] as $akt) {
              if ($akt->getId() != 105 && $akt->getId() != 66) {
                $aktivnosti [] = $akt->getTitle();
              }
            }

            $recenice = array_map('trim', preg_split('/[.!?]+/', $stopwatch['additionalActivity'], -1, PREG_SPLIT_NO_EMPTY));
            $sveAktivnosti = array_merge($aktivnosti, $recenice);

            $combinedActivities = implode("\n", $sveAktivnosti);


            $sheet->setCellValue('C' . $startAktivnosti, $combinedActivities);
            $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $font = $sheet->getStyle('C' . $startAktivnosti)->getFont();
            $font->setSize(14); // Postavite veličinu fonta na 14

            $sheet->setCellValue('D' . $startAktivnosti, $stopwatch['additionalDesc']);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

//            $sheet->setCellValue('D' . $startAktivnosti, $stopwatch['additionalActivity']);
//            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setWrapText(true);
//            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
//            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
//            $font = $sheet->getStyle('D' . $startAktivnosti)->getFont();
//            $font->setSize(14); // Postavite veličinu fonta na 14

            $sheet->setCellValue('F' . $startAktivnosti, $stopwatch['start']->format('H:i'));
            $sheet->getStyle('F' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('G' . $startAktivnosti, $stopwatch['stop']->format('H:i'));
            $sheet->getStyle('G' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('H' . $startAktivnosti, $stopwatch['hoursReal'] . ':' . $stopwatch['minutesReal']);
            $sheet->getStyle('H' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('I' . $startAktivnosti, $stopwatch['hours'] . ':' . $stopwatch['minutes']);
            $sheet->getStyle('I' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('L' . $startAktivnosti, $stopwatch['description']);
            $sheet->getStyle('L' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('L' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('L' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('M' . $startAktivnosti, $robotika);
            $sheet->getStyle('M' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('M' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('M' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $users = '';
            $usersCount = count($stopwatch['users']);

            foreach ($stopwatch['users'] as $key => $user) {
              $users .= $user->getFullName();

              // Ako nije poslednji član u nizu, dodaj "\n"
              if ($key !== $usersCount - 1) {
                $users .= "\n";
              }
            }

            $sheet->setCellValue('N' . $startAktivnosti, $users);
            $sheet->getStyle('N' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('N' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('N' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


            if (!is_null($stopwatch['client'])) {
              $sheet->setCellValue('E' . $startAktivnosti, $stopwatch['client']->getTitle());
              $sheet->getStyle('E' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('E' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }
            if (!is_null($stopwatch['category'])) {
              $sheet->setCellValue('B' . $startAktivnosti, $stopwatch['project']->getTitle() . ' (' .$stopwatch['category']->getTitle() . ')');
              $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
              $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            } else {
              $sheet->setCellValue('B' . $startAktivnosti, $stopwatch['project']->getTitle() );
              $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
              $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }


            $startAktivnosti++;

          }

          $row++;


        }

        $dimension = $sheet->calculateWorksheetDimension();
        $sheet->getStyle($dimension)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:N3')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A1:N3')->getFill()->getStartColor()->setRGB('CCCCCC');

        // Postavite font za opseg od A1 do M2
        $style = $sheet->getStyle('A2:N3');
        $font = $style->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font->setBold(true); // Postavite font kao boldiran
//      $sheet->getStyle('A4:M14')->applyFromArray($styleArray);
//      $sheet->getStyle('A15:M16')->applyFromArray($styleArray);
        $start = 4;
        foreach ($report[1] as $item) {
//        dd($item);
          $offset = $item - 1;
          $offset = $offset + $start;
//        dd($offset);

          $sheet->getStyle('A' . $start . ':N' . $offset)->applyFromArray($styleArray);

          $start = $offset + 1;

        }

      }
    }
    $sheet->setTitle("Izvestaj");

    // Create your Office 2007 Excel (XLSX Format)
    $writer = new Xls($spreadsheet);

    // In this case, we want to write the file in the public directory
//    $publicDirectory = $this->getParameter('kernel.project_dir') . '/var/excel';
//    // e.g /var/www/project/public/my_first_excel_symfony4.xlsx
//    $excelFilepath =  $publicDirectory . '/'.$user->getFullName() . '_'. $datum .'.xls';

    // Create the file
    try {
      $writer->save($excelFilepath);
    } catch (Exception $e) {
      dd( 'Caught exception: ',  $e->getMessage(), "\n");
    }


    // Omogućite preuzimanje na strani korisnika
//    header('Content-Type: application/openxmlformats-officedocument.spreadsheetml.sheet');
//    header('Content-Disposition: attachment;filename="'.time() . '_'. $slugify->slugify($user->getFullName(), '_') . $type . '.xls"');
// Čitanje fajla i slanje na izlaz
    readfile($excelFilepath);

// Obrišite fajl nakon slanja
    unlink($excelFilepath);

// Obrišite fajl nakon što je preuzet
//    register_shutdown_function(function () use ($excelFilepath) {
//      if (file_exists($excelFilepath)) {
//        unlink($excelFilepath);
//      }
//    });

    $args = [];
//
//    $args['projects'] = $this->em->getRepository(Project::class)->findAll();
    $args['categories'] = $this->em->getRepository(Category::class)->getCategoriesTask();

    return $this->render('report_project/control.html.twig', $args);
  }

  #[Route('/edit-info/{id}', name: 'app_employee_edit_info_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editInfo(User $usr, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $history = null;
    if($usr->getId()) {
      $history = $this->json($usr, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $form = $this->createForm(UserEditInfoFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_employee_edit_info_form', ['id' => $usr->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(User::class)->save($usr, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_SUCCESS);

        return $this->redirectToRoute('app_employee_profile_view', ['id' => $usr->getId()]);
      }
    }
    $args['form'] = $form->createView();

    $args['user'] = $usr;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/edit_info.html.twig', $args);
      }
      return $this->render('employee/phone/edit_info.html.twig', $args);
    }
    return $this->render('employee/edit_info.html.twig', $args);
  }
  #[Route('/edit-account/{id}', name: 'app_employee_edit_account_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editAccount(User $usr, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $type = $request->query->getInt('type');
    $usr->setPlainUserType($this->getUser()->getUserType());
    $history = null;
    if($usr->getId()) {
      $history = $this->json($usr, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      $form = $this->createForm(UserEditSelfAccountFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_employee_edit_account_form', ['id' => $usr->getId()])]]);
    } else {
      $form = $this->createForm(UserEditAccountFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_employee_edit_account_form', ['id' => $usr->getId()])]]);
    }

    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(User::class)->save($usr, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_SUCCESS);


        return $this->redirectToRoute('app_employee_profile_view', ['id' => $usr->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $usr;
    $args['type'] = $type;


    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/manager_edit_account.html.twig', $args);
      }
      return $this->render('employee/phone/edit_account.html.twig', $args);
    }
    if($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      return $this->render('employee/edit_account.html.twig', $args);
    }
    return $this->render('employee/manager_edit_account.html.twig', $args);

  }
  #[Route('/edit-image/{id}', name: 'app_employee_edit_image_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editImage(User $usr, Request $request, UploadService $uploadService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $type = $request->query->getInt('type');

    $usr->setEditBy($this->getUser());

    $form = $this->createForm(UserEditImageFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_employee_edit_image_form', ['id' => $usr->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $history = null;
      if($usr->getId()) {
        $history = $this->json($usr, Response::HTTP_OK, [], [
            ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
              return $object->getId();
            }
          ]
        );
        $history = $history->getContent();
      }

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $file = $request->files->all()['user_edit_image_form']['slika'];
        $file = $uploadService->upload($file, $usr->getImageUploadPath());

        $image = $this->em->getRepository(Image::class)->addImage($file, $usr->getThumbUploadPath(), $this->getParameter('kernel.project_dir'));
        $usr->setImage($image);

        $this->em->getRepository(User::class)->save($usr, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_IMAGE_SUCCESS);

        return $this->redirectToRoute('app_employee_profile_view', ['id' => $usr->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $usr;
    $args['type'] = $type;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('employee/edit_image.html.twig', $args);
      }
      return $this->render('employee/phone/edit_image.html.twig', $args);
    }
    return $this->render('employee/edit_image.html.twig', $args);

  }

  #[Route('/reports-availability', name: 'app_employee_availability_reports')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formReportAvailability(Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    if ($request->isMethod('POST')) {
      $data = $request->request->all();
      $args['reports'] = $this->em->getRepository(Availability::class)->getReport($data['report_form'], $this->getUser()->getCompany());
      $args['period'] = $data['report_form']['period'];
      $args['user'] = $this->em->getRepository(User::class)->find($data['report_form']['zaposleni']);

      if (isset ($data['report_form']['category'])) {
        if (in_array(CalendarData::SLOBODAN_DAN, $data['report_form']['category'])) {
          $args['dan'] = true;
        }
        if (in_array(CalendarData::ODMOR, $data['report_form']['category'])) {
          $args['odmor'] = true;
        }
        if (in_array(CalendarData::BOLOVANJE, $data['report_form']['category'])) {
          $args['bolovanje'] = true;
        }
        if (in_array(CalendarData::SLAVA, $data['report_form']['category'])) {
          $args['slava'] = true;
        }
      }
      return $this->render('report_employee/view_availability.html.twig', $args);

    }

    $args = [];

    $args['users'] =  $this->em->getRepository(User::class)->findBy(['company' => $this->getUser()->getCompany(), 'userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false], ['prezime' => 'ASC']);
    $args['categories'] = CalendarData::TIP;
    return $this->render('report_employee/control_availability.html.twig', $args);
  }

  #[Route('/view-calendar-days/{id}', name: 'app_employee_calendar_days')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function daysCalendar(User $usr, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    if ($korisnik->getCompany() != $usr->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    $args = [];
    $search = [];
    $search['tip'] = $request->query->get('tip');
    $search['period'] = $request->query->get('period');

    $days = $this->em->getRepository(Availability::class)->getDays($search, $usr);

    $pagination = $paginator->paginate(
      $days, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['search'] = $search;
    $args['user'] = $usr;
    $args['tipovi'] = AvailabilityData::TIPOVI;


    return $this->render('employee/days_calendar.html.twig', $args);
  }

//  #[Route('/settings/{id}', name: 'app_employee_settings_form')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function settings(User $usr, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//    $korisnik = $this->getUser();
//    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
//      if ($korisnik->getId() != $usr->getId()) {
//        return $this->redirect($this->generateUrl('app_home'));
//      }
//    }
//
//    $history = null;
//    if($usr->getId()) {
//      $history = $this->json($usr, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }
//    $args['user'] = $usr;
//
//    $form = $this->createForm(UserSuspendedFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_settings_form', ['id' => $usr->getId()])]]);
//    if ($request->isMethod('POST')) {
//
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        $this->em->getRepository(User::class)->suspend($usr, $history);
//
//        if ($usr->isSuspended()) {
//          notyf()
//            ->position('x', 'right')
//            ->position('y', 'top')
//            ->duration(5000)
//            ->dismissible(true)
//            ->addSuccess(NotifyMessagesData::USER_SUSPENDED_TRUE);
//        } else {
//          notyf()
//            ->position('x', 'right')
//            ->position('y', 'top')
//            ->duration(5000)
//            ->dismissible(true)
//            ->addSuccess(NotifyMessagesData::USER_SUSPENDED_FALSE);
//        }
//
//        return $this->redirectToRoute('app_user_profile_view', ['id' => $usr->getId()]);
//      }
//    }
//
//    $args['form'] = $form->createView();
//    $args['user'] = $usr;
//    return $this->render('user/settings.html.twig', $args);
//  }




}
