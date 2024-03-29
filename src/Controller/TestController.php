<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Car;
use App\Entity\Comment;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use App\Form\PhoneTaskFormType;
use App\Form\ReassignTaskFormType;
use App\Form\TaskAddDocsType;
use App\Form\TaskEditInfoType;
use App\Form\TaskEditType;
use App\Form\TaskFormType;
use App\Service\MailService;
use App\Service\UploadService;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[Route('/email')]
class TestController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/send-email/', name: 'send_email')]
  public function sendEmail(MailService $mailService): Response {

    $mailService->test('stefanmaksimovic88@hotmail.com');
    $mailService->test('stefanmaksimovic88@gmail.com');
    $mailService->test('sm@epars.rs');

    return $this->redirectToRoute('app_home');
  }
//
//  #[Route('/form/{id}', name: 'app_task_form', defaults: ['id' => 0])]
//  #[Entity('task', expr: 'repository.findForForm(id)')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function form(Task $task, Request $request, UploadService $uploadService)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//
//    $mobileDetect = new MobileDetect();
//
//    $user = $this->getUser();
//    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
//      $task->addAssignedUser($user);
//    }
//
//    $history = null;
//
//    if ($task->getId()) {
//      $history = $this->json($task, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }
//    if (!$mobileDetect->isMobile()) {
//      $form = $this->createForm(TaskFormType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_form', ['id' => $task->getId()])]]);
//    } else {
//      $form = $this->createForm(PhoneTaskFormType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_form', ['id' => $task->getId()])]]);
//    }
//
//    if ($request->isMethod('POST')) {
//
//      $form->handleRequest($request);
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        if (!$mobileDetect->isMobile()) {
//          $uploadFiles = $request->files->all()['task_form']['pdf'];
//          if(!empty ($uploadFiles)) {
//            foreach ($uploadFiles as $uploadFile) {
//              $pdf = new Pdf();
//              $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
//              $pdf->setTitle($file->getFileName());
//              $pdf->setPath($file->getUrl());
//              if (!is_null($task->getProject())) {
//                $pdf->setProject($task->getProject());
//              }
//              $task->addPdf($pdf);
//            }
//          }
//          if (!empty($request->request->all('task_form')['car'])) {
//            $task->setCar($request->request->all('task_form')['car']);
//            $task->setDriver($request->request->all('task_form')['driver']);
//          }
//          $date = $task->getDatumKreiranja();
//          if (!empty($request->request->all('task_form')['vreme'])) {
//            $time = $request->request->all('task_form')['vreme'];
//            $time1 = $date->modify($time);
//            $task->setTime($time1);
//          }
//          else {
//            $task->setTime($date);
//          }
//          foreach ($request->request->all('task_form')['assignedUsers'] as $key => $assignedUser) {
//            if (!empty($assignedUser)){
//              $member = $this->em->getRepository(User::class)->find($assignedUser);
//              $task->addAssignedUser($member);
//
//              if ($key === 0) {
//                $task->setPriorityUserLog($assignedUser);
//              }
//            }
//          }
//        } else {
//          $uploadFiles = $request->files->all()['phone_task_form']['pdf'];
//          if(!empty ($uploadFiles)) {
//            foreach ($uploadFiles as $uploadFile) {
//              $pdf = new Pdf();
//              $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
//              $pdf->setTitle($file->getFileName());
//              $pdf->setPath($file->getUrl());
//              if (!is_null($task->getProject())) {
//                $pdf->setProject($task->getProject());
//              }
//              $task->addPdf($pdf);
//            }
//          }
//          if (!empty($request->request->all('phone_task_form')['car'])) {
//            $task->setCar($request->request->all('phone_task_form')['car']);
//            $task->setDriver($request->request->all('phone_task_form')['driver']);
//          }
//          $date = $task->getDatumKreiranja();
//          if (!empty($request->request->all('phone_task_form')['vreme'])) {
//            $time = $request->request->all('phone_task_form')['vreme'];
//            $time1 = $date->modify($time);
//            $task->setTime($time1);
//          }
//          else {
//            $task->setTime($date);
//          }
//          foreach ($request->request->all('phone_task_form')['assignedUsers'] as $key => $assignedUser) {
//            if (!empty($assignedUser)){
//              $member = $this->em->getRepository(User::class)->find($assignedUser);
//              $task->addAssignedUser($member);
//
//              if ($key === 0) {
//                $task->setPriorityUserLog($assignedUser);
//              }
//            }
//          }
//        }
//
//
//        dd($task);
//        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
//
//        return $this->redirectToRoute('app_tasks');
//      }
//    }
//
//
//    $args['form'] = $form->createView();
//    $args['korisnik'] = $user;
//    $args['task'] = $task;
//    $args['users'] =  $this->em->getRepository(User::class)->findBy(['isSuspended' => false, 'userType' => UserRolesData::ROLE_EMPLOYEE], ['id' => 'ASC']);
//    $args['cars'] =  $this->em->getRepository(Car::class)->findBy(['isSuspended' => false], ['id' => 'ASC']);
//
////    if ($mobileDetect->isMobile()) {
////      return $this->render('task/mobile_form.html.twig', $args);
////    }
////    return $this->render('task/form.html.twig', $args);
//
//    if (!$mobileDetect->isMobile()) {
//      return $this->render('task/form.html.twig', $args);
//    }
//    return $this->render('task/mobile_form.html.twig', $args);
//  }
//
//  #[Route('/form-project/{project}', name: 'app_task_project_form')]
//  #[Entity('project', expr: 'repository.find(project)')]
//  #[Entity('task', expr: 'repository.findForFormProject(project)')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function formByProject(Task $task, Project $project, Request $request, UploadService $uploadService)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//
//    $mobileDetect = new MobileDetect();
//    if ($mobileDetect->isMobile()) {
//      $args['device'] = 1;
//    } else {
//      $args['device'] = 2;
//    }
//
//    $user = $this->getUser();
////    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
////      $teams = [];
////      foreach ($user->getTeams() as $tm) {
////        $teams[] = $tm;
////      }
////      if (!empty($teams)) {
////        foreach ($teams as $tim) {
////          foreach ($tim->getMember() as $member) {
////            $task->addAssignedUser($member);
////          }
////        }
////      } else {
////        $task->addAssignedUser($user);
////      }
////    }
//
//    $history = null;
//
//
//    if ($task->getId()) {
//      $history = $this->json($task, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }
//
//    $form = $this->createForm(TaskFormType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_project_form', ['project' => $project->getId()])]]);
//
//    if ($request->isMethod('POST')) {
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        $uploadFiles = $request->files->all()['task_form']['pdf'];
//        if(!empty ($uploadFiles)) {
//          foreach ($uploadFiles as $uploadFile) {
//            $pdf = new Pdf();
//            $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
//            $pdf->setTitle($file->getFileName());
//            $pdf->setPath($file->getUrl());
//            if (!is_null($task->getProject())) {
//              $pdf->setProject($task->getProject());
//            }
//            $task->addPdf($pdf);
//          }
//        }
//
//        if (!empty($request->request->all('task_form')['car'])) {
//          $task->setCar($request->request->all('task_form')['car']);
//          $task->setDriver($request->request->all('task_form')['driver']);
//        }
//
//        $date = $task->getDatumKreiranja();
//        if (!empty($request->request->all('task_form')['vreme'])) {
//          $time = $request->request->all('task_form')['vreme'];
//          $time1 = $date->modify($time);
//          $task->setTime($time1);
//        } else {
//          $task->setTime($date);
//        }
//
//        foreach ($request->request->all('task_form')['assignedUsers'] as $key => $assignedUser) {
//          if (!empty($assignedUser)){
//            $member = $this->em->getRepository(User::class)->find($assignedUser);
//            $task->addAssignedUser($member);
//
//            if ($key === 0) {
//              $task->setPriorityUserLog($assignedUser);
//            }
//          }
//        }
//
//        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
//
//        return $this->redirectToRoute('app_project_tasks_view', ['id' => $project->getId()]);
//      }
//    }
//    $args['form'] = $form->createView();
//    $args['task'] = $task;
//    $args['users'] =  $this->em->getRepository(User::class)->findBy(['isSuspended' => false, 'userType' => UserRolesData::ROLE_EMPLOYEE], ['id' => 'ASC']);
//    $args['cars'] =  $this->em->getRepository(Car::class)->findBy(['isSuspended' => false], ['id' => 'ASC']);
//
//    return $this->render('task/form.html.twig', $args);
//  }
//
//  #[Route('/edit-info/{id}', name: 'app_task_edit_info')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function editInfo(Task $task, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//    $history = null;
//    //ovde izvlacimo ulogovanog usera
//    $user = $this->getUser();
//
//    if ($task->getId()) {
//      $history = $this->json($task, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }
//
//    $form = $this->createForm(TaskEditInfoType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_edit_info', ['id' => $task->getId()])]]);
//
//    if ($request->isMethod('POST')) {
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        $this->em->getRepository(Task::class)->saveTaskInfo($task, $user, $history);
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
//
//        return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
//      }
//    }
//    $args['form'] = $form->createView();
//    $args['task'] = $task;
//
//    return $this->render('task/edit_info.html.twig', $args);
//  }
//
//  #[Route('/add-docs/{id}', name: 'app_task_add_docs')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function addDocs(Task $task,UploadService $uploadService, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//    $history = null;
//    //ovde izvlacimo ulogovanog usera
//    $user = $this->getUser();
//
//    if ($task->getId()) {
//      $history = $this->json($task, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }
//
//    $form = $this->createForm(TaskAddDocsType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_add_docs', ['id' => $task->getId()])]]);
//
//    if ($request->isMethod('POST')) {
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        $uploadFiles = $request->files->all()['task_add_docs']['pdf'];
//        if(!empty ($uploadFiles)) {
//          foreach ($uploadFiles as $uploadFile) {
//            $pdf = new Pdf();
//            $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
//            $pdf->setTitle($file->getFileName());
//            $pdf->setPath($file->getUrl());
//            if (!is_null($task->getProject())) {
//              $pdf->setProject($task->getProject());
//            }
//            $task->addPdf($pdf);
//          }
//        }
//
//        $this->em->getRepository(Task::class)->saveTaskInfo($task, $user, $history);
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
//
//        return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
//      }
//    }
//    $args['form'] = $form->createView();
//    $args['task'] = $task;
//
//    return $this->render('task/add_pdf.html.twig', $args);
//  }
//
//  #[Route('/reassign/{id}', name: 'app_task_reassign')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function reassign(Task $task, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//    $history = null;
//    //ovde izvlacimo ulogovanog usera
//    $user = $this->getUser();
//
//    if ($task->getId()) {
//      $history = $this->json($task, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }
//
//    $form = $this->createForm(ReassignTaskFormType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_reassign', ['id' => $task->getId()])]]);
//
//    if ($request->isMethod('POST')) {
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
//
//        return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
//      }
//    }
//    $args['form'] = $form->createView();
//    $args['task'] = $task;
//
//    return $this->render('task/reassign.html.twig', $args);
//  }
//
//  #[Route('/edit-task/{id}', name: 'app_task_edit')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function edit(Task $task, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//    $history = null;
//    //ovde izvlacimo ulogovanog usera
//    $user = $this->getUser();
//
//    if ($task->getId()) {
//      $history = $this->json($task, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }
//
//    $form = $this->createForm(TaskEditType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_edit', ['id' => $task->getId()])]]);
//
//    if ($request->isMethod('POST')) {
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
//
//        return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
//      }
//    }
//    $args['form'] = $form->createView();
//    $args['task'] = $task;
//    return $this->render('task/edit.html.twig', $args);
//  }
//
//  #[Route('/view/{id}', name: 'app_task_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function view(Task $task)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//
//    $args['task'] = $task;
//    $args['revision'] = $task->getTaskHistories()->count();
//    $args['status'] = $this->em->getRepository(Task::class)->taskStatus($task);
//
//    $args['taskLogs'] = $this->em->getRepository(TaskLog::class)->findLogs($task);
//    $args['images'] = $this->em->getRepository(Task::class)->getImagesByTask($task);
//    $args['pdfs'] = $this->em->getRepository(Task::class)->getPdfsByTask($task);
//    $args['car'] = $this->em->getRepository(Car::class)->findOneBy(['id' => $task->getCar()]);
//    $args['comments'] = $this->em->getRepository(Comment::class)->findBy(['task' => $task, 'isSuspended' => false], ['id' => 'DESC']);
//
////    $args['cars'] = [];
////    foreach ($task->getAssignedUsers() as $driver) {
////      if (!is_null($driver->getCar())) {
////        $args['cars'][] = $this->em->getRepository(Car::class)->find($driver->getCar());
////      }
////    }
//
//
//    $args['time'] = $this->em->getRepository(StopwatchTime::class)->getStopwatchTimeByTask($task);
//
//    return $this->render('task/view.html.twig', $args);
//  }
//
//  #[Route('/view-user/{id}', name: 'app_task_view_user')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewUser(Task $task)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//
//    $args['status'] = $this->em->getRepository(Task::class)->taskStatus($task);
//
//    $args['task'] = $task;
//    $args['revision'] = $task->getTaskHistories()->count();
//
//    $user = $this->getUser();
//
//    $args['taskLog'] = $this->em->getRepository(TaskLog::class)->findOneBy(['user' => $user, 'task' => $task]);
//    $args['stopwatch'] = $this->em->getRepository(StopwatchTime::class)->findOneBy(['taskLog' => $args['taskLog'], 'diff' => null]);
//    $args['countStopwatches'] = $this->em->getRepository(StopwatchTime::class)->countStopwatches($args['taskLog']);
//    $args['images'] = $this->em->getRepository(Task::class)->getImagesByTask($task);
//    $args['pdfs'] = $this->em->getRepository(Task::class)->getPdfsByTask($task);
//    $args['comments'] = $this->em->getRepository(Comment::class)->findBy(['task' => $task, 'isSuspended' => false], ['id' => 'DESC']);
//    $args['car'] = $this->em->getRepository(Car::class)->findOneBy(['id' => $task->getCar()]);
////    $args['cars'] = [];
////    foreach ($task->getAssignedUsers() as $driver) {
////      if (!is_null($driver->getCar())) {
////        $args['cars'][] = $this->em->getRepository(Car::class)->find($driver->getCar());
////      }
////    }
//
//    $args['lastEdit'] = $this->em->getRepository(StopwatchTime::class)->lastEdit($args['taskLog']);
//
//
//    return $this->render('task/view_user.html.twig', $args);
//  }
//
//  #[Route('/close/{id}', name: 'app_task_close')]
//  public function close(Task $task)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//
//    $this->em->getRepository(Task::class)->close($task);
//
//    notyf()
//      ->position('x', 'right')
//      ->position('y', 'top')
//      ->duration(5000)
//      ->dismissible(true)
//      ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
//
//    return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
//  }
//
//  #[Route('/delete/{id}', name: 'app_task_delete')]
//  public function delete(Task $task)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//    $user = $this->getUser();
//    $this->em->getRepository(Task::class)->deleteTask($task, $user);
//
//    notyf()
//      ->position('x', 'right')
//      ->position('y', 'top')
//      ->duration(5000)
//      ->dismissible(true)
//      ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
//
//    return $this->redirectToRoute('app_tasks');
//  }
//
//
//  /**
//   * @Route("/app_ajax", name="app_ajax")
//   */
//  public function getAllKorisnici(Request $request): JsonResponse {
//    $korisnici = $this->em->getRepository(User::class)->findAll();
//
//    // Konvertuj korisnike u asocijativni niz radi lakše manipulacije
//    $korisniciArray = [];
//    foreach ($korisnici as $korisnik) {
//      $korisniciArray[] = [
//        'id' => $korisnik->getId(),
//        'name' => $korisnik->getFullName(),
//
//      ];
//    }
//
//    return new JsonResponse(['users' => $korisniciArray]);
//  }
}
