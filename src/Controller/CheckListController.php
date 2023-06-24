<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\City;
use App\Entity\ManagerChecklist;
use App\Form\CityFormType;
use App\Form\ManagerChecklistFormType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checklists')]
class CheckListController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_checklist_list')]
  public function list()    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $args['checklist'] = $this->em->getRepository(ManagerChecklist::class)->findAll();

    return $this->render('check_list/list.html.twig', $args);
  }


  #[Route('/form/{id}', name: 'app_checklist_form', defaults: ['id' => 0])]
  #[Entity('checklist', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, ManagerChecklist $checklist)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }


    $form = $this->createForm(ManagerChecklistFormType::class, $checklist, ['attr' => ['action' => $this->generateUrl('app_checklist_form', ['id' => $checklist->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(ManagerChecklist::class)->save($checklist);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_checklist_list');
      }
    }
    $args['form'] = $form->createView();
    $args['checklist'] = $checklist;


    return $this->render('check_list/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_checklist_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(ManagerChecklist $checklist)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['checklist'] = $checklist;
    return $this->render('check_list/view.html.twig', $args);
  }

  #[Route('/finish/{id}', name: 'app_checklist_finish')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function finish(ManagerChecklist $checklist)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $this->em->getRepository(ManagerChecklist::class)->finish($checklist);

    return $this->redirectToRoute('app_home');
  }

  #[Route('/delete/{id}', name: 'app_checklist_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function delete(ManagerChecklist $checklist)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $this->em->getRepository(ManagerChecklist::class)->delete($checklist);

    return $this->redirectToRoute('app_home');
  }

}
