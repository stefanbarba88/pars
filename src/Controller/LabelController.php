<?php

namespace App\Controller;

use App\Classes\Data\ColorsData;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\ResponseMessages;
use App\Entity\Label;
use App\Form\LabelFormType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
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
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $labels = $this->em->getRepository(Label::class)->getLabelsPaginator();

    $pagination = $paginator->paginate(
      $labels, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;

    return $this->render('label/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_label_form', defaults: ['id' => 0])]
  #[Entity('label', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Label $label)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $label->setEditBy($this->getUser());

    $form = $this->createForm(LabelFormType::class, $label, ['attr' => ['action' => $this->generateUrl('app_label_form', ['id' => $label->getId()])]]);

// ajax
//    if ($request->isMethod('POST')) {
//      $jsonArgs = [];
//
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//        $label = $this->em->getRepository(label::class)->save($label);
//        $jsonArgs['label'] = $label;
//        $jsonArgs['success'] = ResponseMessages::SUCCESS;
//      }
//
//      if (!$form->isValid()) {
//        $jsonArgs['success'] = 0;
//        $jsonArgs['error'] = ResponseMessages::ERROR_FORM_NOT_VALID;
//
//        $jsonArgs['errors'] = [];
//        foreach ($form->getErrors(true) as $error) {
//          $jsonArgs['errors'][] = $error->getMessage();
//        }
//      }
//
//      return $this->json($jsonArgs);
//  }

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        $color = $request->request->get('color');
        $label->setColor($color);
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
    $args['colors'] = ColorsData::form();

    return $this->render('label/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_label_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Label $label)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['label'] = $label;

    return $this->render('label/view.html.twig', $args);
  }

}
