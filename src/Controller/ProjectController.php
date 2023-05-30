<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Classes\ProjectHelper;
use App\Classes\ProjectHistoryHelper;
use App\Classes\ResponseMessages;
use App\Entity\Project;
use App\Entity\ProjectHistory;
use App\Entity\Task;
use App\Entity\Team;
use App\Entity\User;
use App\Form\ProjectFormType;
use App\Form\ProjectTeamListFormType;
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
  public function list(Request $request): Response {
    $args = [];
    $user = $this->getUser();

    $permanent = $request->query->getInt('permanent');

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $args['projects'] = $this->em->getRepository(Project::class)->getProjectsByUser($user);
    } else {
      if($permanent == 1) {
        $args['projects'] = $this->em->getRepository(Project::class)->getAllProjectsPermanent();
        $args['type'] = 1;
      } else {
        $args['projects'] = $this->em->getRepository(Project::class)->getAllProjects();
        $args['type'] = 0;
      }


    }



    return $this->render('project/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_project_form', defaults: ['id' => 0])]
  #[Entity('project', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Project $project, Request $request): Response {
    $history = null;
    //ovde izvlacimo ulogovanog usera
    $user = $this->getUser();

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

        return $this->redirectToRoute('app_project_profile_view', ['id' => $project->getId()]);
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

    $args['projectH'] = $serializer->deserialize($projectHistory->getHistory(), ProjectHistoryHelper::class, 'json');

    $args['projectHistory'] = $projectHistory;

    return $this->render('project/view_history_profile.html.twig', $args);
  }

  #[Route('/view-tasks/{id}', name: 'app_project_tasks_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewTasks(Project $project): Response {
    $args['project'] = $project;
    $args['tasks'] = $this->em->getRepository(Task::class)->getTasksByProject($project);

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

  #[Route('/view-teams/{id}', name: 'app_project_teams_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewTeams(Project $project): Response {
    $args['project'] = $project;
    $args['teams'] = $project->getTeam();

    return $this->render('project/view_teams.html.twig', $args);
  }

  #[Route('/team-list/{id}', name: 'app_project_team_list')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function teamList(Project $project, Request $request): Response {
    $history = null;
    //ovde izvlacimo ulogovanog usera
    $user = $this->getUser();

    if($project->getId()) {
      $history = $this->json($project, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $form = $this->createForm(ProjectTeamListFormType::class, $project, ['attr' => ['action' => $this->generateUrl('app_project_team_list', ['id' => $project->getId()])]]);

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

        return $this->redirectToRoute('app_project_teams_view', ['id' => $project->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['project'] = $project;

    return $this->render('project/edit_team_list.html.twig', $args);
  }

  #[Route('/view-images/{id}', name: 'app_project_images_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewImages(Project $project): Response {
    $args['project'] = $project;
    $args['images'] = $this->em->getRepository(Project::class)->getImagesByProject($project);

    return $this->render('project/view_images.html.twig', $args);
  }

  #[Route('/view-docs/{id}', name: 'app_project_docs_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewDocs(Project $project): Response {
    $args['project'] = $project;
    $args['pdfs'] = $this->em->getRepository(Project::class)->getPdfsByProject($project);

    return $this->render('project/view_docs.html.twig', $args);
  }

}
