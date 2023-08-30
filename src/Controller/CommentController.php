<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\Comment;
use App\Entity\Task;
use App\Form\CommentFormType;
use App\Form\PhoneTaskFormType;
use App\Form\TaskFormType;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comments')]
class CommentController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_comments')]
  public function list()    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $args['comments'] = $this->em->getRepository(Comment::class)->findAll();

    return $this->render('comment/list.html.twig', $args);
  }

  #[Route('/form/{task}', name: 'app_comment_form')]
  #[Entity('task', expr: 'repository.find(task)')]
  #[Entity('comment', expr: 'repository.findForFormTask(task)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Comment $comment, Task $task)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $comment->setUser($this->getUser());
    $form = $this->createForm(CommentFormType::class, $comment, ['attr' => ['action' => $this->generateUrl('app_comment_form', ['task' => $task->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Comment::class)->save($comment);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::COMMENT_ADD);
//u zavisnosti odakle se dodaje komentar redirekcija

        if ($comment->getTask()->getAssignedUsers()->contains($this->getUser())) {
          return $this->redirectToRoute('app_task_view_user', ['id' => $task->getId()]);
        } else {
          return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
        }


      }
    }
    $args['form'] = $form->createView();
    $args['comment'] = $comment;

    return $this->render('comment/modal_form.html.twig', $args);
  }

  #[Route('/edit/{id}', name: 'app_comment_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function edit(Request $request, Comment $comment)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }


    $form = $this->createForm(CommentFormType::class, $comment, ['attr' => ['action' => $this->generateUrl('app_comment_edit', ['id' => $comment->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Comment::class)->save($comment);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::COMMENT_EDIT);

        if ($comment->getTask()->getAssignedUsers()->contains($this->getUser())) {
          return $this->redirectToRoute('app_task_view_user', ['id' => $comment->getTask()->getId()]);
        } else {
          return $this->redirectToRoute('app_task_view', ['id' => $comment->getTask()->getId()]);
        }

      }
    }
    $args['form'] = $form->createView();
    $args['comment'] = $comment;
    $args['user'] = $this->getUser();

    $mobileDetect = new MobileDetect();
    if ($mobileDetect->isMobile()) {
      return $this->render('comment/phone/form.html.twig', $args);
    }
    return $this->render('comment/form.html.twig', $args);
  }

  #[Route('/comment-delete/{id}', name: 'app_comment_delete')]
  public function delete(Comment $comment)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $comment->setIsSuspended(true);
    $this->em->getRepository(Comment::class)->save($comment);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::COMMENT_DELETE);

    if ($comment->getTask()->getAssignedUsers()->contains($this->getUser())) {
      return $this->redirectToRoute('app_task_view_user', ['id' => $comment->getTask()->getId()]);
    } else {
      return $this->redirectToRoute('app_task_view', ['id' => $comment->getTask()->getId()]);
    }
  }
}
