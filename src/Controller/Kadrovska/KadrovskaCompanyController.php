<?php

namespace App\Controller\Kadrovska;

use App\Classes\Data\AddonData;
use App\Classes\Data\NivoObrazovanjaData;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaZaposlenjaData;
use App\Classes\Soap;
use App\Entity\Addon;
use App\Entity\Calendarkadr;
use App\Entity\City;
use App\Entity\Company;
use App\Entity\Image;
use App\Entity\User;
use App\Form\CompanyFormType;
use App\Service\UploadService;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/kadrovska/company')]

class KadrovskaCompanyController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }
  #[Route('/list/', name: 'app_kadrovska_companies')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {

    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }
    $args=[];

    $search = [];

    $search['title'] = $request->query->get('title');
    $search['pib'] = $request->query->get('pib');

    $companies = $this->em->getRepository(Company::class)->getAllCompaniesPaginatorKadrovska($search, $korisnik->getCompany()->getId());

    $pagination = $paginator->paginate(
      $companies, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;

    return $this->render('_kadrovska/company/list.html.twig', $args);
  }

  #[Route('/archive/', name: 'app_kadrovska_companies_archive')]
  public function archive(PaginatorInterface $paginator, Request $request)    : Response {

    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }
    $args=[];

    $search = [];

    $search['title'] = $request->query->get('title');
    $search['pib'] = $request->query->get('pib');

    $companies = $this->em->getRepository(Company::class)->getAllCompaniesPaginatorKadrovskaArchive($search, $korisnik->getCompany()->getId());

    $pagination = $paginator->paginate(
      $companies, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;

    return $this->render('_kadrovska/company/archive.html.twig', $args);
  }

  #[Route('/pib', name: 'app_kadrovska_company_pib')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formPib(Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }

    if ($request->isMethod('POST')) {

        $args['pib'] = $request->request->all()['company_form']['pib'];

        return $this->redirectToRoute('app_kadrovska_company_form', $args);

    }

    return $this->render('_kadrovska/company/pib.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_kadrovska_company_form', defaults: ['id' => 0])]
  #[Entity('company', expr: 'repository.findForFormKadrovska(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Company $company, UploadService $uploadService)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }

    if ($request->get('pib') !== null) {
      $pib = $request->get('pib');
      $data = Soap::getSoap($pib);
      $company->setTitle($data['naziv']);
      $company->setAdresa($data['adresa']);
      $company->setPib($data['pib']);
      $grad = $this->em->getRepository(City::class)->findOneBy(['ptt' => $data['ptt']]);
      $company->setGrad($grad);
    }

    $form = $this->createForm(CompanyFormType::class, $company, ['attr' => ['action' => $this->generateUrl('app_kadrovska_company_form', ['id' => $company->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $file = $request->files->all()['company_form']['image'];

        if(!is_null($file)) {
//          $file = Avatar::getAvatar($this->getParameter('kernel.project_dir') . $usr->getAvatarUploadPath(), $usr);
          $file = $uploadService->upload($file, $company->getImageUploadPath());
          $image = $this->em->getRepository(Image::class)->addImage($file, $company->getThumbUploadPath(), $this->getParameter('kernel.project_dir'));
          $company->setImage($image);
        } else {
          $company->setImage($this->em->getRepository(Image::class)->find(2));
        }

        $this->em->getRepository(Company::class)->save($company);


        return $this->redirectToRoute('app_kadrovska_companies');
      }
    }
    $args['form'] = $form->createView();
    $args['company'] = $company;

    return $this->render('_kadrovska/company/form.html.twig', $args);
  }

  #[Route('/suspend/{id}', name: 'app_kadrovska_company_suspend')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function suspend(Company $company, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }

    if ($company->isSuspended()) {
      $company->setIsSuspended(false);
    } else {
      $company->setIsSuspended(true);
    }

    $this->em->getRepository(Company::class)->save($company);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

    if ($company->isSuspended()) {
      return $this->redirectToRoute('app_kadrovska_company_suspend');
    }

    return $this->redirectToRoute('app_kadrovska_companies');

  }

  #[Route('/view/{id}', name: 'app_kadrovska_company_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile(Company $company)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }
    $args['company'] = $company;

    return $this->render('_kadrovska/company/view.html.twig', $args);
  }

  #[Route('/employees/{id}', name: 'app_kadrovska_company_employees')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function employees(Company $company, PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $company) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }


    $args = [];
    $search = [];

    $search['ime'] = $request->query->get('ime');
    $search['prezime'] = $request->query->get('prezime');
    $search['obrazovanje'] = $request->query->get('obrazovanje');
    $search['vrsta'] = $request->query->get('vrsta');
    $search['zvanje'] = $request->query->get('zvanje');
    $search['pozicija'] = $request->query->get('pozicija');


    $users = $this->em->getRepository(User::class)->getEmployeesCompanyPaginator($company, $search, 0);

    $pagination = $paginator->paginate(
      $users, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['company'] = $company;
    $args['pagination'] = $pagination;
    $args['vrste'] = VrstaZaposlenjaData::VRSTA_ZAPOSLENJA;
    $args['obrazovanje'] = NivoObrazovanjaData::NIVO_OBRAZOVANJA;

    $users = $this->em->getRepository(User::class)->findBy(['company' => $company, 'userType' => UserRolesData::ROLE_EMPLOYEE]);

    $uniquePositions = [];
    $uniqueTitules = [];
    foreach ($users as $user) {
      $position = $user->getPozicija();
      if ($position !== null && !in_array($position, $uniquePositions, true)) {
        $uniquePositions[] = $position;
      }
      $titula = $user->getZvanje();
      if ($titula !== null && !in_array($titula, $uniqueTitules, true)) {
        $uniqueTitules[] = $titula;
      }
    }

    $args['pozicije'] = $uniquePositions;
    $args['zvanja'] = $uniqueTitules;

    return $this->render('_kadrovska/company/view_employees.html.twig', $args);
  }

  #[Route('/employees-archive/{id}', name: 'app_kadrovska_company_employees_archive')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function employeesArchive(Company $company, PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $company) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args = [];
    $search = [];

    $search['ime'] = $request->query->get('ime');
    $search['prezime'] = $request->query->get('prezime');
    $search['obrazovanje'] = $request->query->get('obrazovanje');
    $search['vrsta'] = $request->query->get('vrsta');
    $search['zvanje'] = $request->query->get('zvanje');
    $search['pozicija'] = $request->query->get('pozicija');

    $users = $this->em->getRepository(User::class)->getEmployeesCompanyPaginator($company, $search, 1);

    $pagination = $paginator->paginate(
      $users, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['vrste'] = VrstaZaposlenjaData::VRSTA_ZAPOSLENJA;
    $args['obrazovanje'] = NivoObrazovanjaData::NIVO_OBRAZOVANJA;

    $users = $this->em->getRepository(User::class)->findBy(['company' => $company, 'userType' => UserRolesData::ROLE_EMPLOYEE]);

    $uniquePositions = [];
    $uniqueTitules = [];
    foreach ($users as $user) {
      $position = $user->getPozicija();
      if ($position !== null && !in_array($position, $uniquePositions, true)) {
        $uniquePositions[] = $position;
      }
      $titula = $user->getZvanje();
      if ($titula !== null && !in_array($titula, $uniqueTitules, true)) {
        $uniqueTitules[] = $titula;
      }
    }

    $args['pozicije'] = $uniquePositions;
    $args['zvanja'] = $uniqueTitules;
    $args['company'] = $company;
    return $this->render('_kadrovska/company/view_employees_archive.html.twig', $args);
  }

  #[Route('/employees-calendar/{id}', name: 'app_kadrovska_company_employees_calendar')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function employeesCalendar(Company $company, PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $company) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args = [];
    $args['users'] = $this->em->getRepository(User::class)->findBy(['isSuspended' => false, 'company' => $company], ['prezime' => 'ASC']);
    $args['company'] = $company;

    return $this->render('_kadrovska/company/view_employees_calendar.html.twig', $args);
  }

  #[Route('/employees-addon/{id}', name: 'app_kadrovska_company_employees_addon')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function employeesAddon(Company $company, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $company) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args = [];
    $search = [];

    $search['addon'] = $request->query->get('addon');
    $search['period'] = $request->query->get('period');
    $search['user'] = $request->query->get('user');

    $activities = $this->em->getRepository(Addon::class)->getAddonsByCompany($company, $search);
//    $users = $this->em->getRepository(User::class)->getEmployeesPaginator($type);

    $pagination = $paginator->paginate(
      $activities, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['company'] = $company;
    $args['addons'] = AddonData::ADDON;
    $args['users'] = $this->em->getRepository(User::class)->findBy(['isSuspended' => false, 'company' => $company, 'userType' => UserRolesData::ROLE_EMPLOYEE],['prezime' => 'ASC']);

    return $this->render('_kadrovska/company/view_employees_addon.html.twig', $args);
  }

  #[Route('/employees-aneks/{id}', name: 'app_kadrovska_company_employees_aneks')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function employeesAneks(Company $company, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getCompany() != $company) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args = [];
    $search = [];

    $search['aneks'] = $request->query->get('aneks');
    $search['period'] = $request->query->get('period');
    $search['user'] = $request->query->get('user');

    $activities = $this->em->getRepository(Calendarkadr::class)->getAneksByCompany($company, $search);
//    $users = $this->em->getRepository(User::class)->getEmployeesPaginator($type);

    $pagination = $paginator->paginate(
      $activities, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['company'] = $company;
    $args['users'] = $this->em->getRepository(User::class)->findBy(['isSuspended' => false, 'company' => $company, 'userType' => UserRolesData::ROLE_EMPLOYEE],['prezime' => 'ASC']);

    return $this->render('_kadrovska/company/view_employees_aneks.html.twig', $args);
  }


}
