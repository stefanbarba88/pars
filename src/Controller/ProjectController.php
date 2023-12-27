<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Classes\ProjectHelper;
use App\Classes\ProjectHistoryHelper;
use App\Classes\ResponseMessages;
use App\Classes\Slugify;
use App\Entity\Category;
use App\Entity\Project;
use App\Entity\ProjectHistory;
use App\Entity\Task;
use App\Entity\Team;
use App\Entity\User;
use App\Form\ProjectFormType;
use App\Form\ProjectTeamListFormType;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Snappy\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/projects')]
class ProjectController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_projects')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $user = $this->getUser();

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $projects = $this->em->getRepository(Project::class)->getProjectsByUserPaginator($user);
    } else {
      $projects = $this->em->getRepository(Project::class)->getAllProjectsPaginator();
    }

    $pagination = $paginator->paginate(
      $projects, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('project/list_paginator.html.twig', $args);
      }
      return $this->render('project/phone/list_paginator.html.twig', $args);
    }
    return $this->render('project/list_paginator.html.twig', $args);
  }

  #[Route('/list-change/', name: 'app_projects_change')]
  public function listChange(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $projects = $this->em->getRepository(Project::class)->getAllProjectsChangePaginator();

    $pagination = $paginator->paginate(
      $projects, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('project/list_paginator_change.html.twig', $args);
      }
      return $this->render('project/phone/list_paginator_change.html.twig', $args);
    }
    return $this->render('project/list_paginator_change.html.twig', $args);
  }

  #[Route('/list-permanent/', name: 'app_projects_permanent')]
  public function listPermanent(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $projects = $this->em->getRepository(Project::class)->getAllProjectsPermanentPaginator();

    $pagination = $paginator->paginate(
      $projects, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('project/list_paginator_permanent.html.twig', $args);
      }
      return $this->render('project/phone/list_paginator_permanent.html.twig', $args);
    }
    return $this->render('project/list_paginator_permanent.html.twig', $args);
  }

  #[Route('/list-mix/', name: 'app_projects_mix')]
  public function listMix(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $projects = $this->em->getRepository(Project::class)->getAllProjectsMixPaginator();

    $pagination = $paginator->paginate(
      $projects, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('project/list_paginator_mix.html.twig', $args);
      }
      return $this->render('project/phone/list_paginator_mix.html.twig', $args);
    }
    return $this->render('project/list_paginator_mix.html.twig', $args);
  }

  #[Route('/list-archive/', name: 'app_projects_archive')]
  public function listArchive(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $projects = $this->em->getRepository(Project::class)->getAllProjectsSuspendedPaginator();

    $pagination = $paginator->paginate(
      $projects, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('project/list_paginator_archive.html.twig', $args);
      }
      return $this->render('project/phone/list_paginator_archive.html.twig', $args);
    }
    return $this->render('project/list_paginator_archive.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_project_form', defaults: ['id' => 0])]
  #[Entity('project', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Project $project, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $history = null;
    //ovde izvlacimo ulogovanog usera
    $user = $this->getUser();
    if ($user->getCompany() != $project->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    if($project->getId()) {
      $history = $this->json($project, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $form = $this->createForm(ProjectFormType::class, $project, ['attr' => ['action' => $this->generateUrl('app_project_form', ['id' => $project->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

//        $test1 = $serializer->deserialize($test->getContent(), Project::class, 'json');

        $this->em->getRepository(Project::class)->saveProject($project, $user, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_project_profile_view', ['id' => $project->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['project'] = $project;

    return $this->render('project/form.html.twig', $args);
  }

  #[Route('/suspend/{id}', name: 'app_project_suspend')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function suspend(Project $project, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $history = null;
    //ovde izvlacimo ulogovanog usera
    $user = $this->getUser();
    if ($user->getCompany() != $project->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    if($project->getId()) {
      $history = $this->json($project, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $this->em->getRepository(Project::class)->suspendProject($project, $user, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

    return $this->redirectToRoute('app_projects_archive');
  }

  #[Route('/view-profile/{id}', name: 'app_project_profile_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile(Project $project)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $user = $this->getUser();
    if ($user->getCompany() != $project->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['project'] = $project;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('project/view_profile.html.twig', $args);
      }
      return $this->render('project/phone/view_profile.html.twig', $args);
    }
    return $this->render('project/view_profile.html.twig', $args);
  }

  #[Route('/history-project-list/{id}', name: 'app_project_profile_history_list')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function listProjectHistory(Project $project, PaginatorInterface $paginator, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $user = $this->getUser();
    if ($user->getCompany() != $project->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['project'] = $project;
    $histories = $this->em->getRepository(ProjectHistory::class)->getAllPaginator($project);

    $pagination = $paginator->paginate(
      $histories, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;
    return $this->render('project/project_history_list.html.twig', $args);
  }

  #[Route('/history-project-view/{id}', name: 'app_project_profile_history_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProjectHistory(ProjectHistory $projectHistory, SerializerInterface $serializer)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args['projectH'] = $serializer->deserialize($projectHistory->getHistory(), ProjectHistoryHelper::class, 'json');

    $args['projectHistory'] = $projectHistory;

    return $this->render('project/view_history_profile.html.twig', $args);
  }

  #[Route('/view-tasks/{id}', name: 'app_project_tasks_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewTasks(Project $project, PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $user = $this->getUser();
    if ($user->getCompany() != $project->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['project'] = $project;

    $tasks = $this->em->getRepository(Task::class)->getTasksByProjectPaginator($project);
    $pagination = $paginator->paginate(
      $tasks, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;


    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('project/view_tasks.html.twig', $args);
      }
      return $this->render('project/phone/view_tasks.html.twig', $args);
    }

    return $this->render('project/view_tasks.html.twig', $args);
  }

  #[Route('/view-activity/{id}', name: 'app_project_activity_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewActivity(Project $project)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $user = $this->getUser();
    if ($user->getCompany() != $project->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['project'] = $project;


//    $args['teren'] = [];
    $tasks = $this->em->getRepository(Project::class)->processMonthlyTasks($project);
    $args['zadaci'] = $tasks[0];
    $args['tereni'] = $tasks[1];

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('project/view_activity.html.twig', $args);
      }
      return $this->render('project/phone/view_activity.html.twig', $args);
    }

    return $this->render('project/view_activity.html.twig', $args);
  }

//  #[Route('/view-calendar/{id}', name: 'app_project_calendar_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewCalendar(Project $project)    : Response { if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $args['project'] = $project;
//
//    return $this->render('project/view_calendar.html.twig', $args);
//  }
//
//  #[Route('/view-time/{id}', name: 'app_project_time_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewTime(Project $project)    : Response { if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $args['project'] = $project;
//
//    return $this->render('project/view_time.html.twig', $args);
//  }
//
//  #[Route('/view-expenses/{id}', name: 'app_project_expenses_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewExpenses(Project $project)    : Response { if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $args['project'] = $project;
//
//    return $this->render('project/view_expenses.html.twig', $args);
//  }

//  #[Route('/view-users/{id}', name: 'app_project_users_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewUsers(Project $project)    : Response { if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $args['project'] = $project;
//
//    return $this->render('project/view_users.html.twig', $args);
//  }

//  #[Route('/view-teams/{id}', name: 'app_project_teams_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewTeams(Project $project)    : Response { if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $args['project'] = $project;
//    $args['teams'] = $project->getTeam();
//
//    return $this->render('project/view_teams.html.twig', $args);
//  }

//  #[Route('/team-list/{id}', name: 'app_project_team_list')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function teamList(Project $project, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $history = null;
//    //ovde izvlacimo ulogovanog usera
//    $user = $this->getUser();
//
//    if($project->getId()) {
//      $history = $this->json($project, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }
//
//    $form = $this->createForm(ProjectTeamListFormType::class, $project, ['attr' => ['action' => $this->generateUrl('app_project_team_list', ['id' => $project->getId()])]]);
//
//    if ($request->isMethod('POST')) {
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
////        $test1 = $serializer->deserialize($test->getContent(), Project::class, 'json');
//
//        $this->em->getRepository(Project::class)->saveProject($project, $user, $history);
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
//
//        return $this->redirectToRoute('app_project_teams_view', ['id' => $project->getId()]);
//      }
//    }
//    $args['form'] = $form->createView();
//    $args['project'] = $project;
//
//    return $this->render('project/edit_team_list.html.twig', $args);
//  }

  #[Route('/view-images/{id}', name: 'app_project_images_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewImages(Project $project)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $user = $this->getUser();
    if ($user->getCompany() != $project->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['project'] = $project;
    $args['images'] = $this->em->getRepository(Project::class)->getImagesByProject($project);

    return $this->render('project/view_images.html.twig', $args);
  }

  #[Route('/view-docs/{id}', name: 'app_project_docs_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewDocs(Project $project)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $user = $this->getUser();
    if ($user->getCompany() != $project->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['project'] = $project;
    $args['pdfs'] = $this->em->getRepository(Project::class)->getPdfsByProject($project);

    return $this->render('project/view_docs.html.twig', $args);
  }

  #[Route('/reports', name: 'app_project_reports')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formReport(Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    if ($request->isMethod('POST')) {

      $data = $request->request->all();

      if (empty($data['report_form']['project'])) {
        $args['reportsAll'] = $this->em->getRepository(Project::class)->getReportAll($data['report_form']);
      } else {
        $args['reports'] = $this->em->getRepository(Project::class)->getReport($data['report_form']);
        $args['project'] = $this->em->getRepository(Project::class)->find($data['report_form']['project']);
      }

      $args['period'] = $data['report_form']['period'];

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

      if (empty($data['report_form']['project'])) {
        return $this->render('report_project/view_all.html.twig', $args);
      }

      return $this->render('report_project/view.html.twig', $args);


    }

    $args = [];

    $args['projects'] = $this->em->getRepository(Project::class)->findBy(['company' => $this->getUser()->getCompany()]);
    $args['categories'] = $this->em->getRepository(Category::class)->getCategoriesProject();

    return $this->render('report_project/control.html.twig', $args);
  }

  #[Route('/report-xls', name: 'app_project_report_xls')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function xlsReport(Request $request, Slugify $slugify)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $datum = $request->query->get('date');
    $type = $request->query->get('type');

    $dates = explode(' - ', $datum);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);

    $projekat = $this->em->getRepository(Project::class)->find($request->query->get('projekat'));

    $report = $this->em->getRepository(Project::class)->getReportXls($datum, $projekat);


    $klijent = $projekat->getClientsJson();

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
        $sheet->getColumnDimension('C')->setAutoSize(true);
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


        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->setCellValue('A1', $klijent[0] . ': ' . $projekat->getTitle() . ' - ' . $datum);
        $style = $sheet->getStyle('A1:M1');
        $font = $style->getFont();
        $font->setSize(18); // Postavite veličinu fonta na 14
        $font->setBold(true); // Postavite font kao boldiran

        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:L2');

        $sheet->mergeCells('M2:M3');

        $sheet->setCellValue('A2', 'Datum');
        $sheet->setCellValue('B2', 'Opis izvedenog posla');
        $sheet->setCellValue('M2', 'Izvršioci');

        $sheet->getStyle('A2:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('B2:J2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:J2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


        $sheet->getStyle('M2:M3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M2:M3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


        $sheet->setCellValue('B3', 'Kategorija');
        $sheet->setCellValue('C3', 'Aktivnosti');
        $sheet->setCellValue('D3', 'Dodatne aktivnosti');
        $sheet->setCellValue('E3', 'Klijent*');
        $sheet->setCellValue('F3', 'Start');
        $sheet->setCellValue('G3', 'Kraj');
        $sheet->setCellValue('H3', 'Razlika');
        $sheet->setCellValue('I3', 'Razlika*');
        $sheet->setCellValue('J3', 'Ukupno');
        $sheet->setCellValue('K3', 'Ukupno*');
        $sheet->setCellValue('L3', 'Napomena');


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

        $font = $sheet->getStyle('J')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('K')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('A')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('F')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('G')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('H')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('I')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('L')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('M')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('E')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('B')->getFont();
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
          $start = $rows[$row];
          $sheet->setCellValue('A' . $start, $key);
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

            $aktivnosti = [];
            foreach ($stopwatch['activity'] as $akt) {
              $aktivnosti [] = $akt->getTitle();
            }
            $combinedActivities = implode("\n", $aktivnosti);
            $sheet->setCellValue('C' . $startAktivnosti, $combinedActivities);
            $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $font = $sheet->getStyle('C' . $startAktivnosti)->getFont();
            $font->setSize(14); // Postavite veličinu fonta na 14


            $sheet->setCellValue('D' . $startAktivnosti, $stopwatch['additionalActivity']);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $font = $sheet->getStyle('D' . $startAktivnosti)->getFont();
            $font->setSize(14); // Postavite veličinu fonta na 14

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

            $users = '';
            $usersCount = count($stopwatch['users']);

            foreach ($stopwatch['users'] as $key => $user) {
              $users .= $user->getFullName();

              // Ako nije poslednji član u nizu, dodaj "\n"
              if ($key !== $usersCount - 1) {
                $users .= "\n";
              }
            }

            $sheet->setCellValue('M' . $startAktivnosti, $users);
            $sheet->getStyle('M' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('M' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('M' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


            if (!is_null($stopwatch['client'])) {
              $sheet->setCellValue('E' . $startAktivnosti, $stopwatch['client']->getTitle());
              $sheet->getStyle('E' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('E' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }
            if (!is_null($stopwatch['category'])) {
              $sheet->setCellValue('B' . $startAktivnosti, $stopwatch['category']->getTitle());
              $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }


            $startAktivnosti++;

          }

          $row++;


        }

        $dimension = $sheet->calculateWorksheetDimension();
        $sheet->getStyle($dimension)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:M3')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A1:M3')->getFill()->getStartColor()->setRGB('CCCCCC');

        // Postavite font za opseg od A1 do M2
        $style = $sheet->getStyle('A2:M3');
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

          $sheet->getStyle('A' . $start . ':M' . $offset)->applyFromArray($styleArray);

          $start = $offset + 1;

        }

      }
      if ($type == 2) {
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setWidth(45);
        $sheet->getColumnDimension('I')->setAutoSize(true);


        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->setCellValue('A1', $klijent[0] . ': ' . $projekat->getTitle() . ' - ' . $datum);
        $style = $sheet->getStyle('A1:I1');
        $font = $style->getFont();
        $font->setSize(18); // Postavite veličinu fonta na 14
        $font->setBold(true); // Postavite font kao boldiran

        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:H2');
        $sheet->mergeCells('I2:I3');

        $sheet->setCellValue('A2', 'Datum');
        $sheet->setCellValue('B2', 'Opis izvedenog posla');
        $sheet->setCellValue('I2', 'Izvršioci');

        $sheet->getStyle('A2:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('B2:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:H2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('I2:I3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I2:I3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

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

        $start = 4;
        $start1 = 4;
        $rows = [];
        foreach ($report[1] as $item) {
          if ($item != 1) {
            $offset = $item - 1;
            $sheet->mergeCells('A' . $start . ':A' . $start + $offset);
            $sheet->mergeCells('G' . $start . ':G' . $start + $offset);
//          $sheet->mergeCells('H' . $start . ':H' . $start + $offset);
//          $sheet->mergeCells('I' . $start . ':I' . $start + $offset);
          }
          $rows[] = $start;
          $start = $start + $item;
        }
        $row = 0;
        $row1 = 0;
        $startAktivnosti = 4;


        foreach ($report[2] as $key => $item) {
          $start1 = $rows[$row1];

          $sheet->setCellValue('G' . $start1, $item['vreme']);
          $sheet->getStyle('G' . $start1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('G' . $start1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
          $row1++;
        }

        foreach ($report[0] as $key => $item) {
          $start = $rows[$row];
          $sheet->setCellValue('A' . $start, $key);
          $sheet->getStyle('A' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('A' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
          $row++;
        }

        foreach ($report[3] as $item) {
          foreach ($item as $stopwatch) {

            $aktivnosti = [];
            foreach ($stopwatch['activity'] as $akt) {
              $aktivnosti [] = $akt->getTitle();
            }
            $combinedActivities = implode("\n", $aktivnosti);
            if (is_null($stopwatch['additionalActivity'])) {
              $sheet->setCellValue('B' . $startAktivnosti, $combinedActivities);
            } else {
              $sheet->setCellValue('B' . $startAktivnosti, $combinedActivities . "\n" . $stopwatch['additionalActivity']);
            }
            $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('D' . $startAktivnosti, $stopwatch['start']->format('H:i'));
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('E' . $startAktivnosti, $stopwatch['stop']->format('H:i'));
            $sheet->getStyle('E' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('F' . $startAktivnosti, $stopwatch['hours'] . ':' . $stopwatch['minutes']);
            $sheet->getStyle('F' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('H' . $startAktivnosti, $stopwatch['description']);
            $sheet->getStyle('H' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('H' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('H' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $users = '';
            $usersCount = count($stopwatch['users']);

            foreach ($stopwatch['users'] as $key => $user) {
              $users .= $user->getFullName();

              // Ako nije poslednji član u nizu, dodaj "\n"
              if ($key !== $usersCount - 1) {
                $users .= "\n";
              }
            }

            $sheet->setCellValue('I' . $startAktivnosti, $users);
            $sheet->getStyle('I' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('I' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('I' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            if (!is_null($stopwatch['client'])) {
              $sheet->setCellValue('C' . $startAktivnosti, $stopwatch['client']->getTitle());
              $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }

            $startAktivnosti++;
          }
        }
        $dimension = $sheet->calculateWorksheetDimension();
        $sheet->getStyle($dimension)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:I3')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A1:I3')->getFill()->getStartColor()->setRGB('CCCCCC');

        // Postavite font za opseg od A1 do M2
        $style = $sheet->getStyle('A2:I3');
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

          $sheet->getStyle('A' . $start . ':I' . $offset)->applyFromArray($styleArray);

          $start = $offset + 1;

        }

//      $dimension = $sheet->calculateWorksheetDimension();
//      $sheet->getStyle($dimension)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
//      $sheet->getStyle('A1:I3')->getFill()->setFillType(Fill::FILL_SOLID);
//      $sheet->getStyle('A1:I3')->getFill()->getStartColor()->setRGB('CCCCCC');
//
//
//      $style = $sheet->getStyle('A2:I3');
//      $font = $style->getFont();
//
//      $font->setSize(14);
//      $font->setBold(true);
//
        $sheet->setCellValue('B' . $startAktivnosti + 1, 'Datum: ' . $stop->format('d.m.Y'));

        $sheet->getStyle('B' . $startAktivnosti + 1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B' . $startAktivnosti + 1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('B' . $startAktivnosti + 3, 'Za ' . $klijent[0] . ':');

        $sheet->getStyle('B' . $startAktivnosti + 3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $startAktivnosti + 3)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('B' . $startAktivnosti + 5)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        $sheet->mergeCells('F' . $startAktivnosti + 3 . ':H' . $startAktivnosti + 3);
        $sheet->mergeCells('F' . $startAktivnosti + 4 . ':H' . $startAktivnosti + 4);
        $sheet->setCellValue('F' . $startAktivnosti + 3, 'Za PARS DOO:');

        $sheet->getStyle('F' . $startAktivnosti + 3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F' . $startAktivnosti + 3)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('F' . $startAktivnosti + 5 . ':H' . $startAktivnosti + 5)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
      }
      if ($type == 3) {

        $sheet->getColumnDimension('A')->setAutoSize(20);
        $sheet->getColumnDimension('B')->setWidth(80);

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Pars logo');
        $drawing->setPath('assets/images/logo/logoXls.png'); /* put your path and image here */

        $drawing->setCoordinates2('A1:B1'); // Postavite visinu slike
        $drawing->setWidth(100);

        $drawing->setWorksheet($spreadsheet->getActiveSheet());


//      $sheet->mergeCells('F1:I4');

        $sheet->setCellValue('B1', "PARS DOO\nAGENCIJA  ZA KONSALTING\nPROJEKTOVANJE I\nGEODETSKE  POSLOVE");
        $sheet->getStyle('B1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('B1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('A5:B8');
        $sheet->setCellValue('A5', "11211 Beograd   Put za Crvenku broj 56e\nTel/fax+381 (0)63 289026. +381 (0)64 1598100\ne-mail:vladapars@gmail.com\nPIB: SR110533773  Matični broj:21360031  Šifra del:7112");
        $sheet->getStyle('A5')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A5:B8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A5:B8')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('A10:B11');
        $sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A10')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->setCellValue('A10', $klijent[0] . ': ' . $projekat->getTitle() . ' - ' . $datum);
        $style = $sheet->getStyle('A10');
        $font = $style->getFont();
        $font->setSize(18); // Postavite veličinu fonta na 14
        $font->setBold(true); // Postavite font kao boldiran

        $sheet->setCellValue('A12', 'Datum');
        $sheet->getStyle('A12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A12')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->setCellValue('B12', 'Opis izvedenog posla');
        $sheet->getStyle('B12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B12')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A12')->applyFromArray($styleArray);
        $sheet->getStyle('B12')->applyFromArray($styleArray);
        $sheet->getStyle('A12:B12')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A12:B12')->getFill()->getStartColor()->setRGB('CCCCCC');
        $style = $sheet->getStyle('A12:B12');
        $font = $style->getFont();
        $font->setSize(14);
        $font->setBold(true);

        $start = 13;
//      $start1 = 4;
        $rows = [];
        foreach ($report[1] as $item) {
          if ($item != 1) {
            $offset = $item - 1;
            $sheet->mergeCells('A' . $start . ':A' . $start + $offset);
          }
          $rows[] = $start;
          $start = $start + $item;
        }
        $row = 0;

        $startAktivnosti = 13;


        foreach ($report[0] as $key => $item) {
          $start = $rows[$row];
          $sheet->setCellValue('A' . $start, $key);
          $sheet->getStyle('A' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('A' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
          $font = $sheet->getStyle('A' . $start)->getFont();
          $font->setSize(14); // Postavite veličinu fonta na 14

          foreach ($item as $vreme) {

            foreach ($vreme['stopwatches'] as $stopwatch) {


              $aktivnosti = [];
              foreach ($stopwatch['activity'] as $akt) {
                $aktivnosti [] = $akt->getTitle();
              }

              $combinedActivities = implode("\n", $aktivnosti);
              if (is_null($stopwatch['additionalActivity'])) {
                $sheet->setCellValue('B' . $startAktivnosti, $combinedActivities);
              } else {
                $sheet->setCellValue('B' . $startAktivnosti, $combinedActivities . "\n" . $stopwatch['additionalActivity']);
              }

              $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setWrapText(true);
              $font = $sheet->getStyle('B' . $startAktivnosti)->getFont();
              $font->setSize(14); // Postavite veličinu fonta na 14

              $startAktivnosti++;

            }

          }

          $row++;
        }

        $sheet->getStyle('A13:B' . $startAktivnosti - 1)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $start = 13;
        foreach ($report[1] as $item) {
//        dd($item);
          $offset = $item - 1;
          $offset = $offset + $start;
//        dd($offset);

          $sheet->getStyle('A' . $start . ':B' . $offset)->applyFromArray($styleArray);

          $start = $offset + 1;

        }
      }
    }
      $sheet->setTitle("Izvestaj");

      // Create your Office 2007 Excel (XLSX Format)
      $writer = new Xls($spreadsheet);

      // In this case, we want to write the file in the public directory
      $publicDirectory = $this->getParameter('kernel.project_dir') . '/var/excel';
      // e.g /var/www/project/public/my_first_excel_symfony4.xlsx
      $excelFilepath =  $publicDirectory . '/'.$projekat->getTitle() . '_'. $datum .'.xls';

      // Create the file
    try {
      $writer->save($excelFilepath);
    } catch (Exception $e) {
      dd( 'Caught exception: ',  $e->getMessage(), "\n");
    }


    // Omogućite preuzimanje na strani korisnika
    header('Content-Type: application/openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$slugify->slugify($projekat->getTitle(), '_') . '_'. $slugify->slugify($datum, '_') . '.xls"');

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

    $args['projects'] = $this->em->getRepository(Project::class)->findBy(['company' => $this->getUser()->getCompany()]);
    $args['categories'] = $this->em->getRepository(Category::class)->getCategoriesProject();

    return $this->render('report_project/control.html.twig', $args);
  }

  #[Route('/view-report', name: 'app_project_report_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewReport(Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

dd($request);
    $args['project'] = '123';

    return $this->render('report_project/view.html.twig', $args);
  }



}
