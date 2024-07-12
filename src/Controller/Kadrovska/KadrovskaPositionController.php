<?php

namespace App\Controller\Kadrovska;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\ZaposleniPozicija;
use App\Form\PositionFormType;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/kadrovska/positions')]
class KadrovskaPositionController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }
  #[Route('/list', name: 'app_positions_kadrovska')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }
    $args = [];
    $search = [];

    $search['naziv'] = $request->query->get('naziv');

    $positions = $this->em->getRepository(ZaposleniPozicija::class)->getPositionsKadrovskaPaginator($search);

    $pagination = $paginator->paginate(
      $positions, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;


    return $this->render('_kadrovska/position/list.html.twig', $args);
}

  #[Route('/form/{id}', name: 'app_position_kadrovska_form', defaults: ['id' => 0])]
  #[Entity('pozicija', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, ZaposleniPozicija $pozicija)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }
    $pozicija->setEditBy($this->getUser());

    $form = $this->createForm(PositionFormType::class, $pozicija, ['attr' => ['action' => $this->generateUrl('app_position_kadrovska_form', ['id' => $pozicija->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(ZaposleniPozicija::class)->save($pozicija);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_positions_kadrovska');
      }
    }
    $args['form'] = $form->createView();
    $args['position'] = $pozicija;

    return $this->render('_kadrovska/position/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_position_kadrovska_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(ZaposleniPozicija $pozicija)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }
    $args['position'] = $pozicija;

    return $this->render('_kadrovska/position/view.html.twig', $args);
  }

}
