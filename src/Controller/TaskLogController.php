<?php

namespace App\Controller;

use App\Entity\TaskLog;
use App\Form\StopwatchTimeFormType;
use App\Service\UploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/task-log')]
class TaskLogController extends AbstractController {
  #[Route('/form/{id}', name: 'app_task_log_form')]
  public function form(TaskLog $taskLog, Request $request, UploadService $uploadService): Response {
    $history = null;
    //ovde izvlacimo ulogovanog usera
//    $user = $this->getUser();
//    $user = $this->em->getRepository(User::class)->find(1);
//    if ($task->getId()) {
//      $history = $this->json($task, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }

    $form = $this->createForm(StopwatchTimeFormType::class, $taskLog, ['attr' => ['action' => $this->generateUrl('app_task_log_form', ['id' => $taskLog->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);
dd($request);
      if ($form->isSubmitted() && $form->isValid()) {
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
      }
    }
    $args['form'] = $form->createView();
    $args['taskLog'] = $taskLog;
    return $this->render('task/log_form.html.twig', $args);
  }
}
