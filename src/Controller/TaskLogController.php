<?php

namespace App\Controller;

use App\Classes\Data\UserRolesData;
use App\Entity\StopwatchTime;
use App\Entity\TaskLog;
use App\Form\StopwatchTimeFormType;
use App\Service\UploadService;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/task-log')]
class TaskLogController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/view/{id}', name: 'app_task_log_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(TaskLog $taskLog, SessionInterface $session)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();
    $args['admin'] = false;
    if ($session->has('admin')) {
      $args['admin'] = true;
    }

    if ($user->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $user->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$args['admin']) {
        if ($user != $taskLog->getUser()) {
          return $this->redirect($this->generateUrl('app_home'));
        }
      }
    }


    $args['task'] = $taskLog->getTask();
    $args['taskLog'] = $taskLog;
    $args['stopwatches'] = $this->em->getRepository(StopwatchTime::class)->getStopwatches($args['taskLog']);
    $args['stopwatchesActive'] = $this->em->getRepository(StopwatchTime::class)->getStopwatchesActive($args['taskLog']);
    $args['time'] = $this->em->getRepository(StopwatchTime::class)->getStopwatchTime($args['taskLog']);

    return $this->render('task_log/view.html.twig', $args);
  }
}
