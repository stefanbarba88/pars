<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\Image;
use App\Entity\Pdf;
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
//    $stopwatch->setLonStop($request->query->get('lon'));
//    $stopwatch->setLatStop($request->query->get('lat'));
//    $stopwatch = $this->em->getRepository(StopwatchTime::class)->setTime($stopwatch);

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
//    dd($stopwatch);
    $form = $this->createForm(StopwatchTimeFormType::class, $stopwatch, ['attr' => ['action' => $this->generateUrl('app_stopwatch_form', ['id' => $stopwatch->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $uploadFiles = $request->files->all()['stopwatch_time_form']['pdf'];
        if(!empty ($uploadFiles)) {
          foreach ($uploadFiles as $uploadFile) {
            $pdf = new Pdf();
            $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());

            $pdf->setTitle($file->getFileName());
            $pdf->setPath($file->getAssetPath());
            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $pdf->setProject($stopwatch->getTaskLog()->getTask()->getProject());
            }
            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $pdf->setProject($stopwatch->getTaskLog()->getTask()->getProject());
            }
            $pdf->setTask($stopwatch->getTaskLog()->getTask());

            $stopwatch->addPdf($pdf);
          }
        }

        $uploadImages = $request->files->all()['stopwatch_time_form']['image'];
        if(!empty ($uploadImages)) {
          foreach ($uploadImages as $uploadFile) {

            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $path = $stopwatch->getTaskLog()->getUploadPath();
              $pathThumb = $stopwatch->getTaskLog()->getThumbUploadPath();
            } else {
              $path = $stopwatch->getTaskLog()->getNoProjectUploadPath();
              $pathThumb = $stopwatch->getTaskLog()->getNoProjectThumbUploadPath();
            }
            $file = $uploadService->upload($uploadFile, $path);
            $image = $this->em->getRepository(Image::class)->add($file, $pathThumb, $this->getParameter('kernel.project_dir'));
            $stopwatch->addImage($image);
          }
        }

        $this->em->getRepository(StopwatchTime::class)->save($stopwatch);

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
    $args['stopwatch'] = $stopwatch;
    $args['hours'] = intdiv($stopwatch->getDiffRounded(), 60);
    $args['minutes'] = $stopwatch->getDiffRounded() % 60;


    return $this->render('task/stopwatch_form.html.twig', $args);
  }
}
