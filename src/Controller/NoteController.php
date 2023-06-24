<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\Notes;
use App\Form\NoteFormType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notes')]
class NoteController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/form/{id}', name: 'app_note_form', defaults: ['id' => 0])]
  #[Entity('notes', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Notes $notes)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $notes->setUser($this->getUser());

    $form = $this->createForm(NoteFormType::class, $notes, ['attr' => ['action' => $this->generateUrl('app_note_form', ['id' => $notes->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Notes::class)->save($notes);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::NOTE_ADD);

        return $this->redirectToRoute('app_employee_notes_view', ['id' => $this->getUser()->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['note'] = $notes;
    $args['user'] = $this->getUser();

    return $this->render('note/modal_form.html.twig', $args);
  }

  #[Route('/edit/{id}', name: 'app_note_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function edit(Request $request, Notes $notes)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }


    $form = $this->createForm(NoteFormType::class, $notes, ['attr' => ['action' => $this->generateUrl('app_note_edit', ['id' => $notes->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Notes::class)->save($notes);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::NOTE_EDIT);

        return $this->redirectToRoute('app_employee_notes_view', ['id' => $this->getUser()->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['note'] = $notes;
    $args['user'] = $this->getUser();

    return $this->render('note/form.html.twig', $args);
  }


  #[Route('/note-delete/{id}', name: 'app_note_delete')]
  public function delete(Notes $notes)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $notes->setIsSuspended(true);
    $this->em->getRepository(Notes::class)->save($notes);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::NOTE_DELETE);

    return $this->redirectToRoute('app_employee_notes_view', ['id' => $this->getUser()->getId()]);
  }

}
