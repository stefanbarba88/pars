<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Classes\ProjectHelper;
use App\Classes\ProjectHistoryHelper;
use App\Classes\ResponseMessages;
use App\Entity\Activity;
use App\Entity\Car;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\FastTask;
use App\Entity\Image;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\ProjectHistory;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use App\Form\PhoneTaskFormType;
use App\Form\ProjectFormType;
use App\Form\ReassignTaskFormType;
use App\Form\TaskAddDocsType;
use App\Form\TaskEditInfoType;
use App\Form\TaskEditType;
use App\Form\TaskFormType;
use App\Service\UploadService;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/tasks')]
class TaskController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_tasks')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $args = [];
    $user = $this->getUser();
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $tasks = $this->em->getRepository(Task::class)->getTasksByUserPaginator($user);
    } else {
      $tasks = $this->em->getRepository(Task::class)->getAllTasksPaginator();
    }

    $pagination = $paginator->paginate(
      $tasks, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;


    $mobileDetect = new MobileDetect();
    if ($mobileDetect->isMobile()) {
      return $this->render('task/phone/list.html.twig', $args);
    }
    return $this->render('task/list.html.twig', $args);
  }

//  #[Route('/archive/', name: 'app_tasks_arhiva')]
//  public function arhiva(PaginatorInterface $paginator, Request $request)    : Response {
//    if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
////    $args = [];
////    $user = $this->getUser();
////    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
////      $args['tasks'] = $this->em->getRepository(Task::class)->getTasksArchiveByUser($user);
////    } else {
////      $args['tasks'] = $this->em->getRepository(Task::class)->getTasksArchive();
////    }
////    $mobileDetect = new MobileDetect();
////    if ($mobileDetect->isMobile()) {
////      return $this->render('task/phone/archive.html.twig', $args);
////    }
//
//    $args = [];
//
//    $tasks = $this->em->getRepository(Task::class)->getTasksArchive();
//
//    $pagination = $paginator->paginate(
//      $tasks,
//      $request->query->getInt('page', 1),
//    );
//
////    $mobileDetect = new MobileDetect();
////    if ($mobileDetect->isMobile()) {
////      return $this->render('task/phone/archive.html.twig', $args);
////    }
//    $args['pagination'] = $pagination;
//    return $this->render('task/archive_paginator.html.twig', $args);
//  }

  #[Route('/archive/', name: 'app_tasks_arhiva')]
  public function arhiva(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $args = [];
    $search = [];
    $search['projekat'] = $request->query->get('projekat');
    $search['kategorija'] = $request->query->get('kategorija');
    $search['period'] = $request->query->get('period');

    $user = $this->getUser();
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $tasks = $this->em->getRepository(Task::class)->getTasksPaginator($search, $user);
    } else {
      $search['zaposleni'] = $request->query->get('zaposleni');
      $tasks = $this->em->getRepository(Task::class)->getTasksPaginator($search, $user);
    }

    $pagination = $paginator->paginate(
      $tasks, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['search'] = $search;
    $args['projekti'] = $this->em->getRepository(Project::class)->findBy([],['title' => 'ASC']);
    $args['kategorije'] = $this->em->getRepository(Category::class)->findBy(['isTaskCategory' => true],['title' => 'ASC']);
    $args['users'] = $this->em->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE],['prezime' => 'ASC']);

    $mobileDetect = new MobileDetect();
    if ($mobileDetect->isMobile()) {
      return $this->render('task/phone/archive_paginator.html.twig', $args);
    }

    return $this->render('task/archive_paginator.html.twig', $args);
  }

  #[Route('/unclosed/', name: 'app_tasks_unclosed')]
  public function unclosed()    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $args = [];
    $user = $this->getUser();
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $args['tasks'] = $this->em->getRepository(Task::class)->getTasksUnclosedByUser($user);
    } else {
      $args['tasks'] = $this->em->getRepository(Task::class)->getTasksUnclosed();
    }
    $mobileDetect = new MobileDetect();
    if ($mobileDetect->isMobile()) {
      return $this->render('task/phone/unclosed.html.twig', $args);
    }
    return $this->render('task/unclosed.html.twig', $args);
  }

  #[Route('/unclosed-logs/', name: 'app_tasks_unclosed_logs')]
  public function unclosedLogs()    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $args = [];
    $user = $this->getUser();
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $args['tasks'] = $this->em->getRepository(Task::class)->getTasksUnclosedLogsByUser($user);
    } else {
      $args['tasks'] = $this->em->getRepository(Task::class)->getTasksUnclosedLogs();
    }
    $mobileDetect = new MobileDetect();
    if ($mobileDetect->isMobile()) {
      return $this->render('task/phone/unclosed_logs.html.twig', $args);
    }
    return $this->render('task/unclosed_logs.html.twig', $args);
  }


  #[Route('/form-task-date/', name: 'app_task_form_date', defaults: ['id' => 0])]
  #[Entity('task', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formDate(Task $task, Request $request,)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $mobileDetect = new MobileDetect();
    $args = [];

    $data = $request->get('project');
    if (!is_null($data)) {
      $args['project'] = $data;
    }

    $args['disabledDates'] = $this->em->getRepository(FastTask::class)->getDisabledDates();
    $args['task'] = $task;

    if ($mobileDetect->isMobile()) {
      return $this->render('task/phone/form_date.html.twig', $args);

    }
    return $this->render('task/form_date.html.twig', $args);

  }

  #[Route('/form/{id}', name: 'app_task_form', defaults: ['id' => 0])]
  #[Entity('task', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Task $task, Request $request, UploadService $uploadService)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    $data = $request->request->all();

    if (isset($data['datumCheck'])) {
      $datumKreiranja = DateTimeImmutable::createFromFormat('d.m.Y', $data['datumCheck'])->setTime(0, 0);
      $task->setDatumKreiranja($datumKreiranja);
    }

    if (isset($data['datumCheckPhone'])) {
      $datumKreiranja = DateTimeImmutable::createFromFormat('Y-m-d', $data['datumCheckPhone'])->setTime(0, 0);
      $task->setDatumKreiranja($datumKreiranja);
    }


    $mobileDetect = new MobileDetect();

    $user = $this->getUser();
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $task->addAssignedUser($user);
    }

    $history = null;

    if ($task->getId()) {
      $history = $this->json($task, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }
    if (!$mobileDetect->isMobile()) {
      $form = $this->createForm(TaskFormType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_form', ['id' => $task->getId()])]]);
    } else {
      $form = $this->createForm(PhoneTaskFormType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_form', ['id' => $task->getId()])]]);
    }



    if ($request->isMethod('POST')) {

      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {

        $datumKreiranja = DateTimeImmutable::createFromFormat('d.m.Y', $data['datumK'])->setTime(0, 0);
        $task->setDatumKreiranja($datumKreiranja);
        if (!$mobileDetect->isMobile()) {
          $uploadFiles = $request->files->all()['task_form']['pdf'];
          if(!empty ($uploadFiles)) {
            foreach ($uploadFiles as $uploadFile) {
              $pdf = new Pdf();
              $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
              $pdf->setTitle($file->getFileName());
              $pdf->setPath($file->getUrl());
              if (!is_null($task->getProject())) {
                $pdf->setProject($task->getProject());
              }
              $task->addPdf($pdf);
            }
          }
          if (!empty($request->request->all('task_form')['car'])) {
            $task->setCar($request->request->all('task_form')['car']);
            if (!empty($request->request->all('task_form')['driver'])) {
              $task->setDriver($request->request->all('task_form')['driver']);
            }
            $task->setDriver($request->request->all('task_form')['assignedUsers'][0]);
          }
          $date = $task->getDatumKreiranja();
          if (!empty($request->request->all('task_form')['vreme'])) {
            $time = $request->request->all('task_form')['vreme'];
            $time1 = $date->modify($time);
            $task->setTime($time1);
          }
          else {
            $task->setTime($date);
          }
          foreach ($request->request->all('task_form')['assignedUsers'] as $key => $assignedUser) {

            if (!empty($assignedUser)){
              $member = $this->em->getRepository(User::class)->findOneBy(['id' => intval($assignedUser)]);
              if(!is_null($member)) {
                $task->addAssignedUser($member);

                if ($key === 0) {
                  $task->setPriorityUserLog($assignedUser);
                }
              }
            }
          }
        } else {
          $uploadFiles = $request->files->all()['phone_task_form']['pdf'];
          if(!empty ($uploadFiles)) {
            foreach ($uploadFiles as $uploadFile) {
              $pdf = new Pdf();
              $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
              $pdf->setTitle($file->getFileName());
              $pdf->setPath($file->getUrl());
              if (!is_null($task->getProject())) {
                $pdf->setProject($task->getProject());
              }
              $task->addPdf($pdf);
            }
          }
          if (!empty($request->request->all('phone_task_form')['car'])) {
            $task->setCar($request->request->all('phone_task_form')['car']);
            if (!empty($request->request->all('phone_task_form')['driver'])) {
              $task->setDriver($request->request->all('phone_task_form')['driver']);
            }
            $task->setDriver($request->request->all('phone_task_form')['assignedUsers'][0]);
          }
          $date = $task->getDatumKreiranja();
          if (!empty($request->request->all('phone_task_form')['vreme'])) {
            $time = $request->request->all('phone_task_form')['vreme'];
            $time1 = $date->modify($time);
            $task->setTime($time1);
          }
          else {
            $task->setTime($date);
          }
          foreach ($request->request->all('phone_task_form')['assignedUsers'] as $key => $assignedUser) {
            if (!empty($assignedUser)){
              $member = $this->em->getRepository(User::class)->findOneBy(['id' => intval($assignedUser)]);
              if(!is_null($member)) {
                $task->addAssignedUser($member);

                if ($key === 0) {
                  $task->setPriorityUserLog($assignedUser);
                }
              }
            }
          }
        }

        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
        if($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
          return $this->redirectToRoute('app_home');
        }
        return $this->redirectToRoute('app_tasks');
      }
    }

    $args['form'] = $form->createView();
    $args['users'] = $this->em->getRepository(User::class)->getUsersAvailable($task->getDatumKreiranja());
    $args['korisnik'] = $user;
    $args['task'] = $task;

//    $args['users'] =  $this->em->getRepository(User::class)->findBy(['isSuspended' => false, 'userType' => UserRolesData::ROLE_EMPLOYEE], ['prezime' => 'ASC']);
    $args['cars'] =  $this->em->getRepository(Car::class)->findBy(['isSuspended' => false], ['id' => 'ASC']);

    if ($mobileDetect->isMobile()) {
      return $this->render('task/phone/mobile_form.html.twig', $args);

    }
    return $this->render('task/form.html.twig', $args);
  }
  public function editDate(Task $task, int $project = 0): Response {

    $args['task'] = $task;

    if ($project != 0) {
      $args['project'] = $project;
    }
    $mobileDetect = new MobileDetect();
    if ($mobileDetect->isMobile()) {
      return $this->render('task/phone/edit_date.html.twig', $args);

    }
    return $this->render('task/edit_date.html.twig', $args);
  }

  #[Route('/form-project/{project}', name: 'app_task_project_form')]
  #[Entity('project', expr: 'repository.find(project)')]
  #[Entity('task', expr: 'repository.findForFormProject(project)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formByProject(Task $task, Project $project, Request $request, UploadService $uploadService)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    $data = $request->request->all();

    if (isset($data['datumCheck'])) {
      $datumKreiranja = DateTimeImmutable::createFromFormat('d.m.Y', $data['datumCheck'])->setTime(0, 0);
      $task->setDatumKreiranja($datumKreiranja);
    }

    if (isset($data['datumCheckPhone'])) {
      $datumKreiranja = DateTimeImmutable::createFromFormat('Y-m-d', $data['datumCheckPhone'])->setTime(0, 0);
      $task->setDatumKreiranja($datumKreiranja);
    }

    $mobileDetect = new MobileDetect();

    $user = $this->getUser();
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $task->addAssignedUser($user);
    }

    $history = null;


    if ($task->getId()) {
      $history = $this->json($task, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    if (!$mobileDetect->isMobile()) {
      $form = $this->createForm(TaskFormType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_project_form', ['project' => $project->getId()])]]);
    } else {
      $form = $this->createForm(PhoneTaskFormType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_project_form', ['project' => $project->getId()])]]);
    }

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $datumKreiranja = DateTimeImmutable::createFromFormat('d.m.Y', $data['datumK'])->setTime(0, 0);
        $task->setDatumKreiranja($datumKreiranja);

        if (!$mobileDetect->isMobile()) {
          $uploadFiles = $request->files->all()['task_form']['pdf'];
          if(!empty ($uploadFiles)) {
            foreach ($uploadFiles as $uploadFile) {
              $pdf = new Pdf();
              $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
              $pdf->setTitle($file->getFileName());
              $pdf->setPath($file->getUrl());
              if (!is_null($task->getProject())) {
                $pdf->setProject($task->getProject());
              }
              $task->addPdf($pdf);
            }
          }
          if (!empty($request->request->all('task_form')['car'])) {
            $task->setCar($request->request->all('task_form')['car']);
            if (!empty($request->request->all('task_form')['driver'])) {
              $task->setDriver($request->request->all('task_form')['driver']);
            }
            $task->setDriver($request->request->all('task_form')['assignedUsers'][0]);
          }
          $date = $task->getDatumKreiranja();
          if (!empty($request->request->all('task_form')['vreme'])) {
            $time = $request->request->all('task_form')['vreme'];
            $time1 = $date->modify($time);
            $task->setTime($time1);
          }
          else {
            $task->setTime($date);
          }
          foreach ($request->request->all('task_form')['assignedUsers'] as $key => $assignedUser) {
            if (!empty($assignedUser)){
              $member = $this->em->getRepository(User::class)->findOneBy(['id' => intval($assignedUser)]);
              if(!is_null($member)) {
                $task->addAssignedUser($member);

                if ($key === 0) {
                  $task->setPriorityUserLog($assignedUser);
                }
              }
            }
          }
        } else {
          $uploadFiles = $request->files->all()['phone_task_form']['pdf'];
          if(!empty ($uploadFiles)) {
            foreach ($uploadFiles as $uploadFile) {
              $pdf = new Pdf();
              $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
              $pdf->setTitle($file->getFileName());
              $pdf->setPath($file->getUrl());
              if (!is_null($task->getProject())) {
                $pdf->setProject($task->getProject());
              }
              $task->addPdf($pdf);
            }
          }
          if (!empty($request->request->all('phone_task_form')['car'])) {
            $task->setCar($request->request->all('phone_task_form')['car']);
            if (!empty($request->request->all('phone_task_form')['driver'])) {
              $task->setDriver($request->request->all('phone_task_form')['driver']);
            }
            $task->setDriver($request->request->all('phone_task_form')['assignedUsers'][0]);
          }
          $date = $task->getDatumKreiranja();
          if (!empty($request->request->all('phone_task_form')['vreme'])) {
            $time = $request->request->all('phone_task_form')['vreme'];
            $time1 = $date->modify($time);
            $task->setTime($time1);
          }
          else {
            $task->setTime($date);
          }
          foreach ($request->request->all('phone_task_form')['assignedUsers'] as $key => $assignedUser) {
            if (!empty($assignedUser)){
              $member = $this->em->getRepository(User::class)->findOneBy(['id' => intval($assignedUser)]);
              if(!is_null($member)) {
                $task->addAssignedUser($member);

                if ($key === 0) {
                  $task->setPriorityUserLog($assignedUser);
                }
              }
            }
          }
        }

        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        if($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
          return $this->redirectToRoute('app_home');
        }
        return $this->redirectToRoute('app_tasks');
      }
    }

    $args['form'] = $form->createView();
    $args['task'] = $task;
    $args['project'] = $project->getId();
    $args['korisnik'] = $user;
    $args['users'] = $this->em->getRepository(User::class)->getUsersAvailable($task->getDatumKreiranja());
    $args['cars'] =  $this->em->getRepository(Car::class)->findBy(['isSuspended' => false], ['id' => 'ASC']);

    if ($mobileDetect->isMobile()) {
      return $this->render('task/phone/mobile_form.html.twig', $args);
    }
    return $this->render('task/form.html.twig', $args);
  }

  #[Route('/edit-info/{id}', name: 'app_task_edit_info')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editInfo(Task $task, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $history = null;
    //ovde izvlacimo ulogovanog usera
    $user = $this->getUser();

    if ($task->getId()) {
      $history = $this->json($task, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }
    $mobileDetect = new MobileDetect();

    $form = $this->createForm(TaskEditInfoType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_edit_info', ['id' => $task->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $taskIn = $this->em->getRepository(FastTask::class)->findTaskInPlan($task);
        if (!$taskIn) {
          $this->em->getRepository(Task::class)->saveTaskInfo($task, $user, $history);
          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
        } else {
          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addError(NotifyMessagesData::EDIT_ERROR);
        }

        if($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
          return $this->redirectToRoute('app_home');
        }
        return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['task'] = $task;

    if($mobileDetect->isMobile()) {
      return $this->render('task/phone/edit_info.html.twig', $args);
    }
    return $this->render('task/edit_info.html.twig', $args);
  }

  #[Route('/add-docs/{id}', name: 'app_task_add_docs')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function addDocs(Task $task,UploadService $uploadService, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $history = null;
    //ovde izvlacimo ulogovanog usera
    $user = $this->getUser();

    if ($task->getId()) {
      $history = $this->json($task, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $form = $this->createForm(TaskAddDocsType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_add_docs', ['id' => $task->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $uploadFiles = $request->files->all()['task_add_docs']['pdf'];
        if(!empty ($uploadFiles)) {
          foreach ($uploadFiles as $uploadFile) {
            $pdf = new Pdf();
            $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
            $pdf->setTitle($file->getFileName());
            $pdf->setPath($file->getUrl());
            if (!is_null($task->getProject())) {
              $pdf->setProject($task->getProject());
            }
            $task->addPdf($pdf);
          }
        }

        $this->em->getRepository(Task::class)->saveTaskInfo($task, $user, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        if($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
          return $this->redirectToRoute('app_home');
        }
        return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);

      }
    }
    $args['form'] = $form->createView();
    $args['task'] = $task;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('task/phone/add_pdf.html.twig', $args);
    }

    return $this->render('task/add_pdf.html.twig', $args);
  }

  #[Route('/reassign/{id}', name: 'app_task_reassign')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function reassign(Task $task, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $history = null;
    //ovde izvlacimo ulogovanog usera
    $user = $this->getUser();

    if ($task->getId()) {
      $history = $this->json($task, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

//    $form = $this->createForm(ReassignTaskFormType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_reassign', ['id' => $task->getId()])]]);

    if ($request->isMethod('POST')) {

      $taskIn = $this->em->getRepository(FastTask::class)->findTaskInPlan($task);
      if (!$taskIn) {

        if (!empty($request->request->all('task_form')['car'])) {
          $task->setCar($request->request->all('task_form')['car']);
          if (!empty($request->request->all('task_form')['driver'])) {
            $task->setDriver($request->request->all('task_form')['driver']);
          }
          $task->setDriver($request->request->all('task_form')['assignedUsers'][0]);
        }

        foreach ($task->getAssignedUsers() as $member) {
          $task->removeAssignedUser($member);
        }

        foreach ($request->request->all('task_form')['assignedUsers'] as $key => $assignedUser) {
          if (!empty($assignedUser)){
            $member = $this->em->getRepository(User::class)->findOneBy(['id' => intval($assignedUser)]);
            if(!is_null($member)) {
              $task->addAssignedUser($member);

              if ($key === 0) {
                $task->setPriorityUserLog($assignedUser);
              }
            }
          }
        }
        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
      } else {
        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addError(NotifyMessagesData::EDIT_ERROR);
      }


      if($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
        return $this->redirectToRoute('app_home');
      }
      return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);

    }

    $args['task'] = $task;
    $args['assignedUsers'] = $this->em->getRepository(Task::class)->getAssignedUsersByTask($task);
    $args['users'] = $this->em->getRepository(User::class)->getUsersAvailable($task->getDatumKreiranja());
//    $args['users'] =  $this->em->getRepository(User::class)->findBy(['isSuspended' => false, 'userType' => UserRolesData::ROLE_EMPLOYEE], ['prezime' => 'ASC']);
    $args['cars'] =  $this->em->getRepository(Car::class)->findBy(['isSuspended' => false], ['id' => 'ASC']);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('task/phone/reassign.html.twig', $args);
    }
    return $this->render('task/reassign.html.twig', $args);
  }

  #[Route('/reassign-primary-log/{id}', name: 'app_task_reassign_primary_log')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function reassignPrimaryLog(Task $task, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $history = null;
    //ovde izvlacimo ulogovanog usera
    $user = $this->getUser();

    if ($task->getId()) {
      $history = $this->json($task, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    if ($request->isMethod('POST')) {

      $taskIn = $this->em->getRepository(FastTask::class)->findTaskInPlan($task);
      if (!$taskIn) {

        $task->setPriorityUserLog($request->request->all('task_form')['primaryLog']);
        $this->em->getRepository(Task::class)->save($task);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
      } else {
        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addError(NotifyMessagesData::DELETE_ERROR);
      }

      if($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
        return $this->redirectToRoute('app_home');
      }
      return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);

    }

    $args['task'] = $task;
    $args['users'] = $this->em->getRepository(Task::class)->getAssignedUsersByTask($task);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('task/phone/reassign_primary_log.html.twig', $args);
    }
    return $this->render('task/reassign_primary_log.html.twig', $args);
  }

  #[Route('/assign-remove/{id}', name: 'app_task_assign_remove_user')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function assignRemove(TaskLog $taskLog, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $history = null;


    $taskIn = $this->em->getRepository(FastTask::class)->findTaskInPlan($taskLog->getTask());
    if (!$taskIn) {

      $user = $this->getUser();
      $task = $taskLog->getTask();
      $zaduzeni = $taskLog->getUser();
      $countStopwatches = $this->em->getRepository(StopwatchTime::class)->countStopwatches($taskLog);

      if ($task->getId()) {
        $history = $this->json($task, Response::HTTP_OK, [], [
            ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
              return $object->getId();
            }
          ]
        );
        $history = $history->getContent();
      }

      if ($countStopwatches == 0) {
        $task->removeAssignedUser($zaduzeni);
        $task->removeTaskLog($taskLog);
        $this->em->getRepository(Task::class)->save($task);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
      }

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
    } else {
      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addError(NotifyMessagesData::EDIT_ERROR);
    }

    return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);

  }

  #[Route('/assign-add/{id}', name: 'app_task_assign_add_user')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function assignAdd(Task $task, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $history = null;
    //ovde izvlacimo ulogovanog usera
    $user = $this->getUser();

    if ($task->getId()) {
      $history = $this->json($task, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    if ($request->isMethod('POST')) {

      $taskIn = $this->em->getRepository(FastTask::class)->findTaskInPlan($task);
      if (!$taskIn) {
        foreach ($request->request->all('task_form')['assignedUsers'] as $key => $assignedUser) {
          if (!empty($assignedUser)){
            $member = $this->em->getRepository(User::class)->findOneBy(['id' => intval($assignedUser)]);
            if(!is_null($member)) {
              $task->addAssignedUser($member);
              $taskLog = new TaskLog();
              $taskLog->setUser($member);
              $task->addTaskLog($taskLog);
            }
          }
        }
        $this->em->getRepository(Task::class)->save($task);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
      } else {
        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addError(NotifyMessagesData::EDIT_ERROR);
      }

      return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);

    }

    $args['task'] = $task;
//    $args['users'] =  $this->em->getRepository(User::class)->findBy(['isSuspended' => false, 'userType' => UserRolesData::ROLE_EMPLOYEE], ['prezime' => 'ASC']);
    $args['users'] = $this->em->getRepository(User::class)->getUsersAvailable($task->getDatumKreiranja());
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('task/phone/reassign_task.html.twig', $args);
    }
    return $this->render('task/reassign_task.html.twig', $args);
  }

  #[Route('/edit-task/{id}', name: 'app_task_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function edit(Task $task, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $history = null;
    //ovde izvlacimo ulogovanog usera
    $user = $this->getUser();

    if ($task->getId()) {
      $history = $this->json($task, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $form = $this->createForm(TaskEditType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_edit', ['id' => $task->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $taskIn = $this->em->getRepository(FastTask::class)->findTaskInPlan($task);
        if (!$taskIn) {
          $this->em->getRepository(Task::class)->saveTask($task, $user, $history);

          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
        } else {
          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addError(NotifyMessagesData::EDIT_ERROR);
        }



        return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['task'] = $task;
    return $this->render('task/edit.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_task_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Task $task)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    $args['task'] = $task;
    $args['revision'] = $task->getTaskHistories()->count();
    $args['status'] = $this->em->getRepository(Task::class)->taskStatus($task);

    $args['taskLogs'] = $this->em->getRepository(TaskLog::class)->findLogs($task);
    $args['images'] = $this->em->getRepository(Task::class)->getImagesByTask($task);
    $args['pdfs'] = $this->em->getRepository(Task::class)->getPdfsByTask($task);
    $args['car'] = $this->em->getRepository(Car::class)->findOneBy(['id' => $task->getCar()]);
    $args['comments'] = $this->em->getRepository(Comment::class)->findBy(['task' => $task, 'isSuspended' => false], ['id' => 'DESC']);
    $primaryUser = $this->em->getRepository(User::class)->find($task->getPriorityUserLog());
    $args['taskLog'] = $this->em->getRepository(TaskLog::class)->findOneBy(['task' => $task, 'user' => $primaryUser]);

    $args['stopwatches'] = $this->em->getRepository(StopwatchTime::class)->getStopwatches($args['taskLog']);
    $args['stopwatchesActive'] = $this->em->getRepository(StopwatchTime::class)->getStopwatchesActive($args['taskLog']);
    $args['time1'] = $this->em->getRepository(StopwatchTime::class)->getStopwatchTime($args['taskLog']);

//    $args['cars'] = [];
//    foreach ($task->getAssignedUsers() as $driver) {
//      if (!is_null($driver->getCar())) {
//        $args['cars'][] = $this->em->getRepository(Car::class)->find($driver->getCar());
//      }
//    }


    $args['time'] = $this->em->getRepository(StopwatchTime::class)->getStopwatchTimeByTask($task);

    return $this->render('task/view.html.twig', $args);
  }

  #[Route('/view-user/{id}', name: 'app_task_view_user')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewUser(Task $task)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    $args['status'] = $this->em->getRepository(Task::class)->taskStatus($task);

    $args['task'] = $task;
    $args['revision'] = $task->getTaskHistories()->count();

    $user = $this->getUser();

    $args['taskLog'] = $this->em->getRepository(TaskLog::class)->findOneBy(['user' => $user, 'task' => $task]);
    $args['stopwatchesActive'] = $this->em->getRepository(StopwatchTime::class)->getStopwatchesActive($args['taskLog']);
    $args['time'] = $this->em->getRepository(StopwatchTime::class)->getStopwatchTime($args['taskLog']);
    $args['stopwatch'] = $this->em->getRepository(StopwatchTime::class)->findOneBy(['taskLog' => $args['taskLog'], 'diff' => null]);
    $args['countStopwatches'] = $this->em->getRepository(StopwatchTime::class)->countStopwatches($args['taskLog']);
    $args['images'] = $this->em->getRepository(Task::class)->getImagesByTask($task);
    $args['pdfs'] = $this->em->getRepository(Task::class)->getPdfsByTask($task);
    $args['comments'] = $this->em->getRepository(Comment::class)->findBy(['task' => $task, 'isSuspended' => false], ['id' => 'DESC']);
    $args['car'] = $this->em->getRepository(Car::class)->findOneBy(['id' => $task->getCar()]);
//    $args['cars'] = [];
//    foreach ($task->getAssignedUsers() as $driver) {
//      if (!is_null($driver->getCar())) {
//        $args['cars'][] = $this->em->getRepository(Car::class)->find($driver->getCar());
//      }
//    }

    $args['lastEdit'] = $this->em->getRepository(StopwatchTime::class)->lastEdit($args['taskLog']);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('task/phone/view_user.html.twig', $args);
    }
    return $this->render('task/view_user.html.twig', $args);
  }

  #[Route('/close/{id}', name: 'app_task_close')]
  public function close(Task $task)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    $this->em->getRepository(Task::class)->close($task);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

    return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
  }

  #[Route('/delete/{id}', name: 'app_task_delete')]
  public function delete(Task $task)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $user = $this->getUser();
    $taskIn = $this->em->getRepository(FastTask::class)->findTaskInPlan($task);
    if (!$taskIn) {
      $this->em->getRepository(Task::class)->deleteTask($task, $user);
      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
    } else {
      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addError(NotifyMessagesData::DELETE_ERROR);
    }
    return $this->redirectToRoute('app_tasks');
  }


  /**
   * @Route("/app_ajax", name="app_ajax")
   */
  public function getAllKorisnici(Request $request): JsonResponse {
    $korisnici = $this->em->getRepository(User::class)->findAll();

    // Konvertuj korisnike u asocijativni niz radi lakše manipulacije
    $korisniciArray = [];
    foreach ($korisnici as $korisnik) {
      $korisniciArray[] = [
        'id' => $korisnik->getId(),
        'name' => $korisnik->getFullName(),

      ];
    }

    return new JsonResponse(['users' => $korisniciArray]);
  }

}
