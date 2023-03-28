<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\Label;
use App\Form\LabelFormType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/labels')]
class LabelController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_labels')]
  public function list(): Response {
    $args = [];
    $args['labels'] = $this->em->getRepository(Label::class)->findAll();

    return $this->render('label/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_label_form', defaults: ['id' => 0])]
  #[Entity('label', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Label $label): Response {
    $label->setEditBy($this->getUser());

    $form = $this->createForm(LabelFormType::class, $label, ['attr' => ['action' => $this->generateUrl('app_label_form', ['id' => $label->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(label::class)->save($label);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_labels');
      }
    }
    $args['form'] = $form->createView();
    $args['label'] = $label;

    return $this->render('label/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_label_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Label $label): Response {
    $args['label'] = $label;

    return $this->render('label/view.html.twig', $args);
  }

}
