<?php

namespace App\Controller;

use App\Entity\StopwatchTime;
use App\Entity\TaskLog;
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
    $stopwatch->setStart(new \DateTimeImmutable());
    $stopwatch->setLon($request->query->get('lon'));
    $stopwatch->setLat($request->query->get('lat'));

    $this->em->getRepository(StopwatchTime::class)->save($stopwatch);

    return $this->redirectToRoute('app_task_view_user', ['id' => $taskLog->getTask()->getId()]);
  }

  #[Route('/stop/{id}', name: 'app_stopwatch_stop')]
  public function stop(TaskLog $taskLog, Request $request): Response {
    $args = [];

    $stopwatch = new StopwatchTime();
    $stopwatch->setTaskLog($taskLog);
    $stopwatch->setStart(new \DateTimeImmutable());
    $stopwatch->setLon($request->query->get('lon'));
    $stopwatch->setLat($request->query->get('lat'));

    $this->em->getRepository(StopwatchTime::class)->save($stopwatch);

    return $this->redirectToRoute('app_task_view_user', ['id' => $taskLog->getTask()->getId()]);
  }
}
