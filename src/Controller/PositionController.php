<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\ZaposleniPozicija;
use App\Form\PositionFormType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/positions')]
class PositionController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }
  #[Route('/list', name: 'app_positions')]
  public function list(): Response {
    $args = [];
    $args['positions'] = $this->em->getRepository(ZaposleniPozicija::class)->findAll();

    return $this->render('position/list.html.twig', $args);
}

  #[Route('/form/{id}', name: 'app_position_form', defaults: ['id' => 0])]
  #[Entity('pozicija', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, ZaposleniPozicija $pozicija): Response {
    $pozicija->setEditBy($this->getUser());

    $form = $this->createForm(PositionFormType::class, $pozicija, ['attr' => ['action' => $this->generateUrl('app_position_form', ['id' => $pozicija->getId()])]]);
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

        return $this->redirectToRoute('app_positions');
      }
    }
    $args['form'] = $form->createView();

    return $this->render('position/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_position_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(ZaposleniPozicija $pozicija): Response {
    $args['position'] = $pozicija;

    return $this->render('position/view.html.twig', $args);
  }

}