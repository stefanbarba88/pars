<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Elaborat;
use App\Entity\ElaboratInput;
use App\Form\ElaboratEditFormType;
use App\Form\ElaboratFormType;
use App\Form\ElaboratInputFormType;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/elaborats')]
class ElaboratController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_elaborats')]
  public function list(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();

    $elaborats = $this->em->getRepository(Elaborat::class)->getElaboratsByUserPaginator($korisnik);

    $pagination = $paginator->paginate(
      $elaborats, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('elaborat/phone/list.html.twig', $args);
    }

    return $this->render('elaborat/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_elaborat_form', defaults: ['id' => 0])]
  #[Entity('elaborat', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Elaborat $elaborat)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    $korisnik = $this->getUser();
//    if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
//      return $this->redirect($this->generateUrl('app_home'));
//    }

    $elaborat->setCreatedBy($korisnik);

    $form = $this->createForm(ElaboratFormType::class, $elaborat, ['attr' => ['action' => $this->generateUrl('app_elaborat_form', ['elaborat' => $elaborat->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $input = new ElaboratInput();
        $input->setCreatedBy($elaborat->getCreatedBy());
        $input->setStatus(1);
        $input->setDeadline($elaborat->getDeadline());
        $input->setEstimate($elaborat->getEstimate());
        $input->setPercent($elaborat->getPercent());
        $input->setSend($elaborat->getSend());

        $input->setElaborat($elaborat);

        $elaborat->addInput($input);

        $this->em->getRepository(Elaborat::class)->save($elaborat);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::ELABORAT_ADD);

        return $this->redirectToRoute('app_elaborat_view', ['id' => $elaborat->getId()]);

      }
    }
    $args['form'] = $form->createView();
    $args['elaborat'] = $elaborat;

    return $this->render('elaborat/form.html.twig', $args);
  }

  #[Route('/input/{id}', name: 'app_elaborat_input_form', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formEmployee(Request $request, Elaborat $elaborat)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $input = $elaborat->getInput()->last();

    if (!is_null($input->getId())) {
      $entityManager = $this->em->getManager();
      $entityManager->detach($input);
      $inputNew = clone $input;
    }

    $form = $this->createForm(ElaboratInputFormType::class, $inputNew, ['attr' => ['action' => $this->generateUrl('app_elaborat_input_form', ['id' => $elaborat->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
//dd($input, $inputNew);


        $format = "d.m.Y H:i:s";
        $data = $request->request->all('elaborat_input_form');


        $inputNew->setCreatedBy($this->getUser());
        $inputNew->setStatus($data['status']);
        $inputNew->setPercent($data['percent']);
        $inputNew->setDescription($data['description']);

        if (isset($data['deadline']) && $data['deadline'] !== '') {
          $inputNew->setDeadline(DateTimeImmutable::createFromFormat($format, $data['deadline'] . '00:00:00'));
        }
        if (isset($data['estimate']) && $data['estimate'] !== '') {
          $inputNew->setEstimate(DateTimeImmutable::createFromFormat($format, $data['estimate'] . '00:00:00'));
        }
        if (isset($data['send']) && $data['send'] !== '') {
          $inputNew->setSend(DateTimeImmutable::createFromFormat($format, $data['send'] . '00:00:00'));
        }

        $elaborat->setPercent($inputNew->getPercent());
        $elaborat->setDeadline($inputNew->getDeadline());
        $elaborat->setEstimate($inputNew->getEstimate());
        $elaborat->setSend($inputNew->getSend());
        $elaborat->setStatus($inputNew->getStatus());

        $inputNew->setElaborat($elaborat);

        $elaborat->addInput($inputNew);
        $elaborat->removeInput($input);

        $this->em->getRepository(Elaborat::class)->save($elaborat);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::ELABORAT_ADD);

        return $this->redirectToRoute('app_elaborat_view', ['id' => $elaborat->getId()]);

      }
    }
    $args['form'] = $form->createView();
    $args['elaborat'] = $elaborat;

    return $this->render('elaborat/form_input.html.twig', $args);
  }

  #[Route('/edit/{id}', name: 'app_elaborat_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function edit(Request $request, Elaborat $elaborat)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $korisnik = $this->getUser();
    if ($korisnik != $elaborat->getCreatedBy()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $form = $this->createForm(ElaboratEditFormType::class, $elaborat, ['attr' => ['action' => $this->generateUrl('app_elaborat_edit', ['id' => $elaborat->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Elaborat::class)->save($elaborat);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::ELABORAT_ADD);


          return $this->redirectToRoute('app_elaborat_view', ['id' => $elaborat->getId()]);


      }
    }
    $args['form'] = $form->createView();
    $args['elaborat'] = $elaborat;
    $args['user'] = $this->getUser();

    return $this->render('elaborat/form.html.twig', $args);
  }

  #[Route('/delete/{id}', name: 'app_elaborat_delete')]
  public function delete(Elaborat $elaborat)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik != $elaborat->getCreatedBy()) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    $this->em->getRepository(Elaborat::class)->remove($elaborat);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::ELABORAT_DELETE);

    return $this->redirectToRoute('app_elaborats');

  }

//  #[Route('/admin-comment-delete/{id}', name: 'app_comment_admin_delete')]
//  public function deleteAdmin(Comment $comment)    : Response {
//    if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $comment->setIsSuspended(true);
//    $this->em->getRepository(Comment::class)->save($comment);
//
//    notyf()
//      ->position('x', 'right')
//      ->position('y', 'top')
//      ->duration(5000)
//      ->dismissible(true)
//      ->addSuccess(NotifyMessagesData::COMMENT_DELETE);
//
//    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
//      return $this->redirectToRoute('app_employee_comments_view', ['id' => $this->getUser()->getId()]);
//    }
//    return $this->redirectToRoute('app_comments');
//
//  }
//
  #[Route('/view/{id}', name: 'app_elaborat_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Elaborat $elaborat): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['elaborat'] = $elaborat;
    $args['inputs'] = array_reverse($elaborat->getInput()->toArray());



    return $this->render('elaborat/view.html.twig', $args);
  }
//
//
//  #[Route('/form-intern/{task}', name: 'app_comment_form_int')]
//  #[Entity('task', expr: 'repository.find(task)')]
//  #[Entity('comment', expr: 'repository.findForFormTaskInt(task)')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function formInt(Request $request, Comment $comment, ManagerChecklist $task, MailService $mailService)    : Response {
//    if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//
//    $comment->setUser($this->getUser());
//    $form = $this->createForm(CommentFormType::class, $comment, ['attr' => ['action' => $this->generateUrl('app_comment_form_int', ['task' => $task->getId()])]]);
//    if ($request->isMethod('POST')) {
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        $this->em->getRepository(Comment::class)->save($comment);
//
//        $mailService->checklistCommentTask($task, $comment);
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::COMMENT_ADD);
//
//        return $this->redirectToRoute('app_checklist_view', ['id' => $task->getId()]);
//
//      }
//    }
//    $args['form'] = $form->createView();
//    $args['comment'] = $comment;
//
//    return $this->render('comment/modal_form.html.twig', $args);
//  }
//
//  #[Route('/employee-form-intern/{task}', name: 'app_comment_employee_form_int')]
//  #[Entity('task', expr: 'repository.find(task)')]
//  #[Entity('comment', expr: 'repository.findForFormTaskInt(task)')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function formEmployeeInt(Request $request, Comment $comment, ManagerChecklist $task, MailService $mailService)    : Response {
//    if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//
//    $comment->setUser($this->getUser());
//    $form = $this->createForm(CommentFormType::class, $comment, ['attr' => ['action' => $this->generateUrl('app_comment_employee_form_int', ['task' => $task->getId()])]]);
//    if ($request->isMethod('POST')) {
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        $this->em->getRepository(Comment::class)->save($comment);
//        $mailService->checklistCommentTask($task, $comment);
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::COMMENT_ADD);
//
//        return $this->redirectToRoute('app_checklist_view', ['id' => $task->getId()]);
//
//      }
//    }
//    $args['form'] = $form->createView();
//    $args['comment'] = $comment;
//
//    return $this->render('comment/modal_form.html.twig', $args);
//  }
//
//  #[Route('/edit-intern/{id}', name: 'app_comment_edit_int')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function editInt(Request $request, Comment $comment, MailService $mailService)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//
//    $form = $this->createForm(CommentFormType::class, $comment, ['attr' => ['action' => $this->generateUrl('app_comment_edit_int', ['id' => $comment->getId()])]]);
//    if ($request->isMethod('POST')) {
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        $this->em->getRepository(Comment::class)->save($comment);
//        $mailService->checklistCommentTask($comment->getManagerChecklist(), $comment);
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::COMMENT_EDIT);
//
//        return $this->redirectToRoute('app_checklist_view', ['id' => $comment->getManagerChecklist()->getId()]);
//
//      }
//    }
//    $args['form'] = $form->createView();
//    $args['comment'] = $comment;
//    $args['user'] = $this->getUser();
//
//    $mobileDetect = new MobileDetect();
//    if ($mobileDetect->isMobile()) {
//      if ($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
//        return $this->render('comment/form.html.twig', $args);
//      }
//      return $this->render('comment/phone/form.html.twig', $args);
//    }
//    return $this->render('comment/form.html.twig', $args);
//  }
//
//
//  #[Route('/comment-delete-intern/{id}', name: 'app_comment_delete_int')]
//  public function deleteInt(Comment $comment)    : Response {
//    if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $comment->setIsSuspended(true);
//    $this->em->getRepository(Comment::class)->save($comment);
//
//    notyf()
//      ->position('x', 'right')
//      ->position('y', 'top')
//      ->duration(5000)
//      ->dismissible(true)
//      ->addSuccess(NotifyMessagesData::COMMENT_DELETE);
//
//    return $this->redirectToRoute('app_checklist_view', ['id' => $comment->getManagerChecklist()->getId()]);
//
//  }



}
