<?php

namespace App\Controller;

use App\Classes\TimeRounding;
use App\Entity\StopwatchTime;
use App\Entity\TaskLog;
use App\Form\StopwatchTimeFormType;
use App\Service\UploadService;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stopwatch')]
class StopwatchController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/start/{id}', name: 'app_stopwatch_start')]
  public function start(TaskLog $taskLog, Request $request): Response {
    $args = [];

    $stopwatch = new StopwatchTime();
    $stopwatch->setTaskLog($taskLog);
    $stopwatch->setStart(new DateTimeImmutable());
    $stopwatch->setLon($request->query->get('lon'));
    $stopwatch->setLat($request->query->get('lat'));

    $this->em->getRepository(StopwatchTime::class)->save($stopwatch);

    return $this->redirectToRoute('app_task_view_user', ['id' => $taskLog->getTask()->getId()]);
  }

  #[Route('/form/{id}', name: 'app_stopwatch_form')]
  public function form(StopwatchTime $stopwatch, Request $request, UploadService $uploadService): Response {
    $args = [];

//    $stopwatch->setStop(new DateTimeImmutable());
    $stopwatch->setLonStop($request->query->get('lon'));
    $stopwatch->setLatStop($request->query->get('lat'));


    $stopwatch = $this->em->getRepository(StopwatchTime::class)->setTime($stopwatch);

dd($stopwatch);

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

    $form = $this->createForm(StopwatchTimeFormType::class, $stopwatch, ['attr' => ['action' => $this->generateUrl('app_stopwatch_form', ['id' => $stopwatch->getId()])]]);

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
    $args['stopwatch'] = $stopwatch;
    $args['hours'] = $stopwatch;
    $args['minutes'] = $stopwatch;
    return $this->render('task/stopwatch_form.html.twig', $args);
  }
}
