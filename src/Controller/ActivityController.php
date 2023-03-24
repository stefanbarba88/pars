<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\Activity;
use App\Form\ActivityFormType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/activities')]
class ActivityController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_activities')]
  public function list(): Response {
    $args = [];
    $args['activities'] = $this->em->getRepository(Activity::class)->findAll();

    return $this->render('activity/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_activity_form', defaults: ['id' => 0])]
  #[Entity('activity', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Activity $activity): Response {
    $activity->setEditBy($this->getUser());

    $form = $this->createForm(ActivityFormType::class, $activity, ['attr' => ['action' => $this->generateUrl('app_activity_form', ['id' => $activity->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Activity::class)->save($activity);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_activities');
      }
    }
    $args['form'] = $form->createView();
    $args['activity'] = $activity;

    return $this->render('activity/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_activity_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Activity $activity): Response {
    $args['activity'] = $activity;

    return $this->render('activity/view.html.twig', $args);
  }

}
