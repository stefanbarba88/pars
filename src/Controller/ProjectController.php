<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\ResponseMessages;
use App\Entity\Project;
use App\Form\ProjectFormType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    $project->setEditBy($this->getUser());

    $form = $this->createForm(ProjectFormType::class, $project, ['attr' => ['action' => $this->generateUrl('app_project_form', ['id' => $project->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        dd($project);

        $this->em->getRepository(Project::class)->save($project);

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

  #[Route('/view/{id}', name: 'app_project_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Project $project): Response {
    $args['project'] = $project;

    return $this->render('project/view.html.twig', $args);
  }

}
