<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\Team;
use App\Form\TeamFormType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/teams')]
class TeamController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_teams')]
  public function list(Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $type = $request->query->getInt('type');
    $args = [];
    $args['teams'] = $this->em->getRepository(Team::class)->getTeams($type);
    $args['type'] = $type;

    return $this->render('team/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_team_form', defaults: ['id' => 0])]
  #[Entity('team', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Team $team)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $form = $this->createForm(TeamFormType::class, $team, ['attr' => ['action' => $this->generateUrl('app_team_form', ['id' => $team->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Team::class)->save($team);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_teams');
      }
    }
    $args['form'] = $form->createView();
    $args['team'] = $team;

    return $this->render('team/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_team_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Team $team)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['team'] = $team;

    return $this->render('team/view.html.twig', $args);
  }

}
