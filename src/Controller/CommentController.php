<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\Comment;
use App\Entity\Task;
use App\Form\CommentFormType;
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
  public function list(): Response {
    $args = [];
    $args['comments'] = $this->em->getRepository(Comment::class)->findAll();

    return $this->render('comment/list.html.twig', $args);
  }

  #[Route('/form/{task}', name: 'app_comment_form')]
  #[Entity('task', expr: 'repository.find(task)')]
  #[Entity('comment', expr: 'repository.findForFormTask(task)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Comment $comment, Task $task): Response {

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
        return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['comment'] = $comment;

    return $this->render('comment/modal_form.html.twig', $args);
  }

  #[Route('/edit/{id}', name: 'app_comment_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function edit(Request $request, Comment $comment): Response {


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
//u zavisnosti odakle se ulazi proslediti
        return $this->redirectToRoute('app_employee_comments_view', ['id' => $this->getUser()->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['comment'] = $comment;
    $args['user'] = $this->getUser();

    return $this->render('comment/form.html.twig', $args);
  }

  #[Route('/comment-delete/{id}', name: 'app_comment_delete')]
  public function delete(Comment $comment): Response {
    $comment->setIsSuspended(true);
    $this->em->getRepository(Comment::class)->save($comment);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::COMMENT_DELETE);

    return $this->redirectToRoute('app_task_view', ['id' => $comment->getTask()->getId()]);
  }
}
