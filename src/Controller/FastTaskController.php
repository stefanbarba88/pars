<?php

namespace App\Controller;

use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\FastTask;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/quick_tasks')]
class FastTaskController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }
  #[Route('/list', name: 'app_quick_tasks')]
  public function list()    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $args['fastTasks'] = $this->em->getRepository(FastTask::class)->findAll();

    return $this->render('fast_task/list.html.twig', $args);
  }

  #[Route('/form-quick/{id}', name: 'app_quick_tasks_form', defaults: ['id' => 0])]
  #[Entity('fastTask', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(FastTask $fastTask, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }


    if ($request->isMethod('POST')) {

      $data = $request->request->all();

      $fastTask = $this->em->getRepository(FastTask::class)->saveFastTask($fastTask, $data);

//      dd($fastTask);

//        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

      return $this->redirectToRoute('app_quick_tasks');

    }

    $args = [];

    $args['users'] = $this->em->getRepository(User::class)->getUsersCars();
    $args['activities'] = $this->em->getRepository(Activity::class)->findBy(['isSuspended' => false]);
    $args['projects'] = $this->em->getRepository(Project::class)->findBy(['isSuspended' => false]);

    return $this->render('fast_task/form.html.twig', $args);

  }

  #[Route('/edit-quick/{id}', name: 'app_quick_task_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function edit(FastTask $fastTask, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    if ($request->isMethod('POST')) {

      $data = $request->request->all();
//
//      dd($data, $fastTask);

      $fastTask = $this->em->getRepository(FastTask::class)->saveFastTask($fastTask, $data);


//        $this->em->getRepository(Task::class)->saveTask($task, $user, $history);
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

      return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);

    }

    $args = [];

    $args['users'] = $this->em->getRepository(User::class)->getUsersCars();
    $args['activities'] = $this->em->getRepository(Activity::class)->findBy(['isSuspended' => false]);
    $args['projects'] = $this->em->getRepository(Project::class)->findBy(['isSuspended' => false]);
    $args['fastTask'] = $fastTask;

    return $this->render('fast_task/edit.html.twig', $args);


//    dd($request);
//    $user = $this->getUser();
//    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
//      $task->addAssignedUser($user);
//    }
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



  }

  #[Route('/create-tasks/{id}', name: 'app_create_tasks')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function createTasks(FastTask $fastTask, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();
    $this->em->getRepository(Task::class)->createTasksFromList($fastTask, $user);

    return $this->redirectToRoute('app_tasks');

  }
}