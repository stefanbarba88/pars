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

  #[Route('/list/', name: 'app_activities')]
  public function list(): Response {
    $args = [];
    $args['activities'] = $this->em->getRepository(Comment::class)->findAll();

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

        return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['comment'] = $comment;

    return $this->render('comment/form.html.twig', $args);
  }

  #[Route('/comment/{id}', name: 'app_comment_delete')]
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
