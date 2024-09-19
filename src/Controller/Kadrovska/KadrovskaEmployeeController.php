<?php

namespace App\Controller\Kadrovska;

use App\Classes\AppConfig;
use App\Classes\Avatar;
use App\Classes\Data\AddonData;
use App\Classes\Data\AvailabilityData;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Classes\Slugify;
use App\Entity\Addon;
use App\Entity\Availability;
use App\Entity\Calendar;
use App\Entity\Calendarkadr;
use App\Entity\CarReservation;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Company;
use App\Entity\Expense;
use App\Entity\Holiday;
use App\Entity\Image;
use App\Entity\ManagerChecklist;
use App\Entity\Notes;
use App\Entity\Overtime;
use App\Entity\Task;
use App\Entity\ToolReservation;
use App\Entity\User;
use App\Entity\Vacation;
use App\Entity\ZaposleniPozicija;
use App\Form\Kadrovska\EmployeeKadrovskaEditAccountFormType;
use App\Form\Kadrovska\EmployeeKadrovskaEditInfoFormType;
use App\Form\Kadrovska\EmployeeKadrovskaRegistrationFormType;
use App\Form\Kadrovska\KadrovskaAddonFormType;
use App\Form\Kadrovska\KadrovskaCalendarFormType;
use App\Form\Kadrovska\UserKadrovskaEditAccountFormType;
use App\Form\Kadrovska\UserKadrovskaEditInfoFormType;
use App\Form\Kadrovska\UserKadrovskaRegistrationFormType;
use App\Form\Kadrovska\VacationKadrovskaFormType;
use App\Form\UserEditImageFormType;
use App\Form\UserSuspendedFormType;
use App\Form\VacationFormType;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[Route('/kadrovska/employee')]
class KadrovskaEmployeeController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list', name: 'app_employees_kadrovska')]
  public function list(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }

    $args = [];
    $search = [];

    $search['ime'] = $request->query->get('ime');
    $search['prezime'] = $request->query->get('prezime');
    $search['company'] = $request->query->get('company');

    $users = $this->em->getRepository(User::class)->getEmployeesPaginatorKadrovska($korisnik, $search, 0);
//    $users = $this->em->getRepository(User::class)->getEmployeesPaginator($type);

    $pagination = $paginator->paginate(
      $users, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());


    $args['pagination'] = $pagination;
    $args['kompanije'] = $this->em->getRepository(Company::class)->findBy(['firma' => $korisnik->getCompany()->getId(), 'isSuspended' => false]);


//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      return $this->render('employee/phone/list.html.twig', $args);
//    }

    return $this->render('_kadrovska/employee/list.html.twig', $args);
  }

  #[Route('/list-archive/', name: 'app_employees_archive_kadrovska')]
  public function archive(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }

    $args = [];
    $search = [];

    $search['ime'] = $request->query->get('ime');
    $search['prezime'] = $request->query->get('prezime');
    $search['company'] = $request->query->get('company');

    $users = $this->em->getRepository(User::class)->getEmployeesPaginatorKadrovska($korisnik, $search, 1);
//    $users = $this->em->getRepository(User::class)->getEmployeesPaginator($type);

    $pagination = $paginator->paginate(
      $users, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['kompanije'] = $this->em->getRepository(Company::class)->findBy(['firma' => $korisnik->getCompany()->getId()],['isSuspended' => 'ASC']);

//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      return $this->render('employee/phone/archive.html.twig', $args);
//    }

    return $this->render('_kadrovska/employee/archive.html.twig', $args);
  }

  #[Route('/view-profile/{id}', name: 'app_employee_profile_view_kadrovska')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile(User $usr): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $usr->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args['user'] = $usr;
//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
//        return $this->render('employee/view_profile.html.twig', $args);
//      }
//      return $this->render('employee/phone/view_profile.html.twig', $args);
//    }
    return $this->render('_kadrovska/employee/view_profile.html.twig', $args);
  }

  #[Route('/form/{company}/{id}', name: 'app_kadrovska_employee_form', defaults: ['id' => 0])]
  #[Entity('usr', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, User $usr, Company $company, UploadService $uploadService)    : Response {
    if (!$this->isGranted('ROLE_USER')|| !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $usr->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $usr->setCompany($company);
    $usr->setPlainUserType($this->getUser()->getUserType());

    $form = $this->createForm(EmployeeKadrovskaRegistrationFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_kadrovska_employee_form', ['company' => $company->getId(), 'id' => $usr->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        if ($usr->getTrack() != 1) {
          $usr->setPlainPassword(AppConfig::DEFAULT_PASS);
        }

        $file = $request->files->all()['employee_kadrovska_registration_form']['slika'];

        if(is_null($file)) {
          $file = Avatar::getAvatar($this->getParameter('kernel.project_dir') . $usr->getAvatarUploadPath(), $usr);
        } else {
          $file = $uploadService->upload($file, $usr->getImageUploadPath());
        }

        $this->em->getRepository(User::class)->register($usr, $file, $this->getParameter('kernel.project_dir'));

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::REGISTRATION_USER_SUCCESS);

        return $this->redirectToRoute('app_kadrovska_company_employees', ['id' => $company->getId()]);

      }
    }
    $args['form'] = $form->createView();

    return $this->render('_kadrovska/employee/registration_form.html.twig', $args);
  }

  #[Route('/edit-info/{id}', name: 'app_employee_edit_info_form_kadrovska')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editInfo(User $usr, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $usr->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $form = $this->createForm(EmployeeKadrovskaEditInfoFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_employee_edit_info_form', ['id' => $usr->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(User::class)->save($usr);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_SUCCESS);

        return $this->redirectToRoute('app_employee_profile_view_kadrovska', ['id' => $usr->getId()]);
      }
    }
    $args['form'] = $form->createView();

    $args['user'] = $usr;

//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
//        return $this->render('employee/edit_info.html.twig', $args);
//      }
//      return $this->render('employee/phone/edit_info.html.twig', $args);
//    }
    return $this->render('_kadrovska/employee/edit_info.html.twig', $args);
  }

  #[Route('/edit-account/{id}', name: 'app_employee_edit_account_form_kadrovska')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editAccount(User $usr, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $usr->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }
    $usr->setPlainUserType($this->getUser()->getUserType());

    $form = $this->createForm(EmployeeKadrovskaEditAccountFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_employee_edit_account_form_kadrovska', ['id' => $usr->getId()])]]);
//    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
//      $form = $this->createForm(UserKadrovskaEditSelfAccountFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_employee_edit_account_form', ['id' => $usr->getId()])]]);
//    } else {
//      $form = $this->createForm(UserKadrovskaEditAccountFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_employee_edit_account_form', ['id' => $usr->getId()])]]);
//    }

    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(User::class)->save($usr);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_SUCCESS);


        return $this->redirectToRoute('app_employee_profile_view_kadrovska', ['id' => $usr->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $usr;

//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
//        return $this->render('employee/manager_edit_account.html.twig', $args);
//      }
//      return $this->render('employee/phone/edit_account.html.twig', $args);
//    }
//    if($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
//      return $this->render('employee/edit_account.html.twig', $args);
//    }
    return $this->render('_kadrovska/employee/edit_account.html.twig', $args);

  }

  #[Route('/edit-image/{id}', name: 'app_employee_edit_image_form_kadrovska')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editImage(User $usr, Request $request, UploadService $uploadService)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $usr->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }


    $form = $this->createForm(UserEditImageFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_employee_edit_image_form_kadrovska', ['id' => $usr->getId()])]]);
    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $file = $request->files->all()['user_edit_image_form']['slika'];
        $file = $uploadService->upload($file, $usr->getImageUploadPath());

        $image = $this->em->getRepository(Image::class)->addImage($file, $usr->getThumbUploadPath(), $this->getParameter('kernel.project_dir'));
        $usr->setImage($image);

        $this->em->getRepository(User::class)->save($usr);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_IMAGE_SUCCESS);

        return $this->redirectToRoute('app_employee_profile_view_kadrovska', ['id' => $usr->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $usr;

//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
//        return $this->render('employee/edit_image.html.twig', $args);
//      }
//      return $this->render('employee/phone/edit_image.html.twig', $args);
//    }
    return $this->render('_kadrovska/employee/edit_image.html.twig', $args);

  }

  #[Route('/settings/{id}', name: 'app_employee_settings_form_kadrovska')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function settings(User $usr, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $usr->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args['user'] = $usr;

    $form = $this->createForm(UserSuspendedFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_employee_settings_form_kadrovska', ['id' => $usr->getId()])]]);
    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(User::class)->suspendKadrovksa($usr);

        if ($usr->isSuspended()) {
          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addSuccess(NotifyMessagesData::USER_SUSPENDED_TRUE);
        } else {
          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addSuccess(NotifyMessagesData::USER_SUSPENDED_FALSE);
        }

        return $this->redirectToRoute('app_employee_profile_view_kadrovska', ['id' => $usr->getId()]);
      }
    }

    $args['form'] = $form->createView();
    $args['user'] = $usr;
    return $this->render('_kadrovska/employee/settings.html.twig', $args);
  }


  #[Route('/view-calendar/{id}', name: 'app_employee_calendar_view_kadrovska')]
  public function viewCalendar(Vacation $vacation, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $vacation->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $form = $this->createForm(VacationKadrovskaFormType::class, $vacation, ['attr' => ['action' => $this->generateUrl('app_employee_calendar_view_kadrovska', ['id' => $vacation->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        $vacation->setUsed1($vacation->getVacation1() + $vacation->getVacationk1() + $vacation->getVacationd1() + $vacation->getOther1() + $vacation->getStopwatch1());
        $vacation->setUsed2($vacation->getVacation2() + $vacation->getVacationk2() + $vacation->getVacationd2() + $vacation->getOther2() + $vacation->getStopwatch2());

//        $test1 = $serializer->deserialize($test->getContent(), Project::class, 'json');

        $this->em->getRepository(vacation::class)->save($vacation);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_employee_calendar_view_kadrovska', ['id' => $vacation->getId()]);
      }
    }
    $args = [];
    $search = [];

    $search['aneks'] = $request->query->get('aneks');

    $users = $this->em->getRepository(Calendarkadr::class)->getCalendarkadrPaginator($vacation->getUser(), $search);
//    $users = $this->em->getRepository(User::class)->getEmployeesPaginator($type);

    $pagination = $paginator->paginate(
      $users, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['form'] = $form->createView();
    $args['vacation'] = $vacation;
    $args['user'] = $vacation->getUser();

    return $this->render('_kadrovska/employee/calendar_form.html.twig', $args);
  }


  #[Route('/create-calendar/{user}/{id}', name: 'app_employee_calendar_create_kadrovska', defaults: ['id' => 0])]
  #[Entity('calendar', expr: 'repository.findForForm(user, id)')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function createCalendarkadr(Calendarkadr $calendar, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $calendar->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args = [];
    $args['user'] = $calendar->getUser();
    $args['calendar'] = $calendar;

    $form = $this->createForm(KadrovskaCalendarFormType::class, $calendar, ['attr' => ['action' => $this->generateUrl('app_employee_calendar_create_kadrovska', ['user' => $calendar->getUser()->getId() ,'id' => $calendar->getId()])]]);


    if ($request->isMethod('POST')) {
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Calendarkadr::class)->save($calendar);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_employee_calendar_view_kadrovska', ['id' => $calendar->getUser()->getVacation()->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $calendar->getUser();
    $args['calendar'] = $calendar;

    return $this->render('_kadrovska/employee/create_calendar.html.twig', $args);
  }

  #[Route('/view-calendarkadr/{id}', name: 'app_employee_calendarkadr_view_kadrovska')]
  public function viewCalendarkadr(Calendarkadr $calendar, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $calendar->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args['user'] = $calendar->getUser();
    $args['calendar'] = $calendar;

    return $this->render('_kadrovska/employee/view_calendar.html.twig', $args);
  }

  #[Route('/delete-calendarkadr/{id}', name: 'app_employee_calendarkadr_delete_kadrovska')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function deleteCalendarkadr(Calendarkadr $calendar, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $calendar->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    if ($calendar->getStatus() == 0) {
      $calendar->setStatus(1);
    } else {
      $calendar->setStatus(0);
    }

    $this->em->getRepository(Calendarkadr::class)->save($calendar);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::DELETE_SUCCESS);

    return $this->redirectToRoute('app_employee_calendar_view_kadrovska', ['id' => $calendar->getUser()->getVacation()->getId()]);
  }


  #[Route('/list-addon/{id}', name: 'app_employee_addon_list_kadrovska')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function listAddon(User $usr, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $usr->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args = [];
    $search = [];

    $search['addon'] = $request->query->get('addon');
    $search['period'] = $request->query->get('period');

    $activities = $this->em->getRepository(Addon::class)->getAddonsByUser($usr, $search);
//    $users = $this->em->getRepository(User::class)->getEmployeesPaginator($type);

    $pagination = $paginator->paginate(
      $activities, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['user'] = $usr;
    $args['addons'] = AddonData::ADDON;

    return $this->render('_kadrovska/employee/view_activity.html.twig', $args);
  }

  #[Route('/create-addon/{user}/{id}', name: 'app_employee_addon_create_kadrovska', defaults: ['id' => 0])]
  #[Entity('addon', expr: 'repository.findForForm(user, id)')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function createAddon(Addon $addon, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $addon->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args = [];
    $args['user'] = $addon->getUser();
    $args['addon'] = $addon;

    $form = $this->createForm(KadrovskaAddonFormType::class, $addon, ['attr' => ['action' => $this->generateUrl('app_employee_addon_create_kadrovska', ['user' => $addon->getUser()->getId() ,'id' => $addon->getId()])]]);


    if ($request->isMethod('POST')) {
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Addon::class)->save($addon);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_employee_addon_view_kadrovska', ['id' => $addon->getId()]);
      }
    }
    $args['form'] = $form->createView();

    return $this->render('_kadrovska/employee/create_addon.html.twig', $args);
  }

  #[Route('/view-addon/{id}', name: 'app_employee_addon_view_kadrovska')]
  public function viewAddon(Addon $addon, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $addon->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args['user'] = $addon->getUser();
    $args['addon'] = $addon;

    return $this->render('_kadrovska/employee/view_addon.html.twig', $args);
  }

  #[Route('/delete-addon/{id}', name: 'app_employee_addon_delete_kadrovska')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function deleteAddon(Addon $addon, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $addon->getCompany()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    if ($addon->isSuspended()) {
      $addon->setIsSuspended(false);
    } else {
      $addon->setIsSuspended(true);
    }

    $this->em->getRepository(Addon::class)->save($addon);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::DELETE_SUCCESS);

    return $this->redirectToRoute('app_employee_addon_list_kadrovska', ['id' => $addon->getUser()->getId()]);
  }


}
