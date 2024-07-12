<?php

namespace App\Controller\Kadrovska;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Titula;
use App\Entity\ZaposleniPozicija;
use App\Form\Kadrovska\KadrovskaTitulaFormType;
use App\Form\PositionFormType;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/kadrovska/zvanja')]
class KadrovskaZvanjaController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }
  #[Route('/list', name: 'app_zvanja_kadrovska')]
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
    $search['id'] = $request->query->get('id');
    $zvanja = $this->em->getRepository(Titula::class)->getZvanjaPaginator($search);

    $pagination = $paginator->paginate(
      $zvanja, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;


    return $this->render('_kadrovska/zvanja/list.html.twig', $args);
}

  #[Route('/form/{id}', name: 'app_zvanja_kadrovska_form', defaults: ['id' => 0])]
  #[Entity('zvanje', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Titula $zvanje)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }

    $form = $this->createForm(KadrovskaTitulaFormType::class, $zvanje, ['attr' => ['action' => $this->generateUrl('app_zvanja_kadrovska_form', ['id' => $zvanje->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Titula::class)->save($zvanje);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_zvanja_kadrovska');
      }
    }
    $args['form'] = $form->createView();
    $args['zvanje'] = $zvanje;

    return $this->render('_kadrovska/zvanja/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_zvanje_kadrovska_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Titula $zvanje)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }
    $args['zvanje'] = $zvanje;

    return $this->render('_kadrovska/zvanja/view.html.twig', $args);
  }

}
