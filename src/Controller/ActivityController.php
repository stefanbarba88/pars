<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;

use App\Form\ActivityFormType;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/activities')]
class ActivityController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_activities')]
  public function list(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    $search = [];

    $search['title'] = $request->query->get('title');

    $activities = $this->em->getRepository(Activity::class)->getActivitiesPaginator($search);

    $pagination = $paginator->paginate(
      $activities, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if ($mobileDetect->isMobile()) {
      return $this->render('activity/phone/list.html.twig', $args);
    }

    return $this->render('activity/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_activity_form', defaults: ['id' => 0])]
  #[Entity('activity', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Activity $activity): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $activity->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
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
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $activity->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['activity'] = $activity;

    return $this->render('activity/view.html.twig', $args);
  }

}
