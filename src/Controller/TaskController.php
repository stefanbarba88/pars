<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\ProjectHelper;
use App\Classes\ProjectHistoryHelper;
use App\Classes\ResponseMessages;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\ProjectHistory;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use App\Form\ProjectFormType;
use App\Form\TaskEditInfoType;
use App\Form\TaskEditType;
use App\Form\TaskFormType;
use App\Service\UploadService;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
  public function list(): Response {
    $args = [];
    $args['tasks'] = $this->em->getRepository(Task::class)->findAll();

    return $this->render('task/list.html.twig', $args);
  }

//  #[Route('/form/{project}/{id}', name: 'app_task_project_form', defaults: ['id' => 0])]
//  #[Entity('project', expr: 'repository.find(project)')]
//  #[Entity('task', expr: 'repository.findForFormProject(project, id)')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function formProject(Task $task, Request $request, Project $project): Response {
////    $history = null;
////    //ovde izvlacimo ulogovanog usera
//////    $user = $this->getUser();
////    $user = $this->em->getRepository(User::class)->find(1);
////    if ($project->getId()) {
////      $history = $this->json($project, Response::HTTP_OK, [], [
////          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
////            return $object->getId();
////          }
////        ]
////      );
////      $history = $history->getContent();
////    }
////
////    $form = $this->createForm(ProjectFormType::class, $project, ['attr' => ['action' => $this->generateUrl('app_project_form', ['id' => $project->getId()])]]);
////
////    if ($request->isMethod('POST')) {
//////      $form->handleRequest($request);
//////
//////      if ($form->isSubmitted() && $form->isValid()) {
//////
////////        $test1 = $serializer->deserialize($test->getContent(), Project::class, 'json');
//////
//////        $this->em->getRepository(Project::class)->saveProject($project, $user, $history);
//////
//////        notyf()
//////          ->position('x', 'right')
//////          ->position('y', 'top')
//////          ->duration(5000)
//////          ->dismissible(true)
//////          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);
//////
//////        return $this->redirectToRoute('app_projects');
//////      }
////    }
////    $args['form'] = $form->createView();
////    $args['project'] = $project;
//
//    return $this->render('task/form.html.twig', $args);
//  }

  #[Route('/form/{id}', name: 'app_task_form', defaults: ['id' => 0])]
  #[Entity('task', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Task $task, Request $request, UploadService $uploadService): Response {
    $history = null;
    //ovde izvlacimo ulogovanog usera
//    $user = $this->getUser();
    $user = $this->em->getRepository(User::class)->find(1);
    if ($task->getId()) {
      $history = $this->json($task, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $form = $this->createForm(TaskFormType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_form', ['id' => $task->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

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

        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_tasks');
      }
    }
    $args['form'] = $form->createView();
    $args['task'] = $task;

    return $this->render('task/form.html.twig', $args);
  }

  #[Route('/edit-info/{id}', name: 'app_task_edit_info')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editInfo(Task $task, Request $request): Response {
    $history = null;
    //ovde izvlacimo ulogovanog usera
//    $user = $this->getUser();
    $user = $this->em->getRepository(User::class)->find(1);
    if ($task->getId()) {
      $history = $this->json($task, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $form = $this->createForm(TaskEditInfoType::class, $task, ['attr' => ['action' => $this->generateUrl('app_task_edit_info', ['id' => $task->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['task'] = $task;

    return $this->render('task/edit_info.html.twig', $args);
  }

  #[Route('/edit-task/{id}', name: 'app_task_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function edit(Task $task, Request $request): Response {
    $history = null;
    //ovde izvlacimo ulogovanog usera
//    $user = $this->getUser();
    $user = $this->em->getRepository(User::class)->find(1);
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

        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['task'] = $task;
    return $this->render('task/edit.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_task_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Task $task): Response {
    $args['task'] = $task;
    $args['revision'] = $task->getTaskHistories()->count();

    return $this->render('task/view.html.twig', $args);
  }

  #[Route('/view-user/{id}', name: 'app_task_view_user')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewUser(Task $task): Response {
    $args['task'] = $task;
    $args['revision'] = $task->getTaskHistories()->count();
    $user = $this->em->getRepository(User::class)->find(2);
    $args['taskLog'] = $this->em->getRepository(TaskLog::class)->findOneBy(['user' => $user]);
    $args['stopwatch'] = $this->em->getRepository(StopwatchTime::class)->findOneBy(['taskLog' => $args['taskLog']]);

    return $this->render('task/view.html.twig', $args);
  }

}
