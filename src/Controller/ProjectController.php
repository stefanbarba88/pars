<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\ResponseMessages;
use App\Entity\Project;
use App\Entity\ProjectHistory;
use App\Entity\User;
use App\Form\ProjectFormType;
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

#[Route('/projects')]
class ProjectController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_projects')]
  public function list(): Response {
    $args = [];
    $args['projects'] = $this->em->getRepository(Project::class)->findAll();

    return $this->render('project/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_project_form', defaults: ['id' => 0])]
  #[Entity('project', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Project $project): Response {
    $history = null;
    //ovde izvlacimo ulogovanog usera
//    $user = $this->getUser();
    $user = $this->em->getRepository(User::class)->find(1);
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

        return $this->redirectToRoute('app_projects');
      }
    }
    $args['form'] = $form->createView();
    $args['project'] = $project;

    return $this->render('project/form.html.twig', $args);
  }

  #[Route('/view-profile/{id}', name: 'app_project_profile_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile(Project $project): Response {
    $args['project'] = $project;

    return $this->render('project/view_profile.html.twig', $args);
  }

  #[Route('/history-project-list/{id}', name: 'app_project_profile_history_list')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function listProjectHistory(Project $project): Response {
    $args['project'] = $project;
    $args['historyProjects'] = $this->em->getRepository(ProjectHistory::class)->findBy(['project' => $project], ['id' => 'DESC']);

    return $this->render('project/project_history_list.html.twig', $args);
  }

  #[Route('/history-project-view/{id}', name: 'app_project_profile_history_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProjectHistory(ProjectHistory $projectHistory, SerializerInterface $serializer): Response {

    $args['projectH'] = $serializer->deserialize($projectHistory->getHistory(), Project::class, 'json');
    $args['projectHistory'] = $projectHistory;

    return $this->render('project/view_history_profile.html.twig', $args);
  }

  #[Route('/view-tasks/{id}', name: 'app_project_tasks_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewTasks(Project $project): Response {
    $args['project'] = $project;
    $args['tasks'] = [];

    return $this->render('project/view_tasks.html.twig', $args);
  }

  #[Route('/view-activity/{id}', name: 'app_project_activity_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewActivity(Project $project): Response {
    $args['project'] = $project;

    return $this->render('project/view_activity.html.twig', $args);
  }

  #[Route('/view-calendar/{id}', name: 'app_project_calendar_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewCalendar(Project $project): Response {
    $args['project'] = $project;

    return $this->render('project/view_calendar.html.twig', $args);
  }

  #[Route('/view-time/{id}', name: 'app_project_time_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewTime(Project $project): Response {
    $args['project'] = $project;

    return $this->render('project/view_time.html.twig', $args);
  }

  #[Route('/view-expenses/{id}', name: 'app_project_expenses_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewExpenses(Project $project): Response {
    $args['project'] = $project;

    return $this->render('project/view_expenses.html.twig', $args);
  }

  #[Route('/view-users/{id}', name: 'app_project_users_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewUsers(Project $project): Response {
    $args['project'] = $project;

    return $this->render('project/view_users.html.twig', $args);
  }

}
