<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\PrioritetData;
use App\Classes\Data\UserRolesData;
use App\Entity\City;
use App\Entity\ManagerChecklist;
use App\Entity\User;
use App\Form\CityFormType;
use App\Form\ManagerChecklistFormType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
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
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $user = $this->getUser();

    $dostupnosti = $this->em->getRepository(ManagerChecklist::class)->getChecklistPaginator($user);

    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;

    return $this->render('check_list/list.html.twig', $args);
  }

  #[Route('/to-do/', name: 'app_checklist_to_do')]
  public function toDo(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $user = $this->getUser();

    $dostupnosti = $this->em->getRepository(ManagerChecklist::class)->getChecklistToDoPaginator($user);

    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;

    return $this->render('check_list/list_to_do.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_checklist_form', defaults: ['id' => 0])]
  #[Entity('checklist', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, ManagerChecklist $checklist)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    if (!is_null($request->get('user'))) {
      $korisnik = $this->em->getRepository(User::class)->find($request->get('user'));
      $args['korisnik'] = $korisnik;
    }

    if ($this->getUser()->getUserType() != UserRolesData::ROLE_SUPER_ADMIN
      && $this->getUser()->getUserType() != UserRolesData::ROLE_ADMIN
      && $this->getUser()->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    if ($request->isMethod('POST')) {

      $data = $request->request->all();

      foreach ($data['checklist']['zaduzeni'] as $zaduzeni) {
        $task = new ManagerChecklist();
        $task->setTask($data['checklist']['zadatak']);
        $task->setPriority($data['checklist']['prioritet']);
        $task->setCreatedBy($this->getUser());
        $task->setUser($this->em->getRepository(User::class)->find($zaduzeni));
        $task->setCompany($this->getUser()->getCompany());
        $this->em->getRepository(ManagerChecklist::class)->save($task);
      }

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

      return $this->redirectToRoute('app_checklist_list');

    }

    $args['users'] = $this->em->getRepository(User::class)->getUsersForChecklist();
    $args['priority'] = PrioritetData::form();

    return $this->render('check_list/form.html.twig', $args);
  }


  #[Route('/edit/{id}', name: 'app_checklist_edit', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function edit(Request $request, ManagerChecklist $checklist)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    if ($this->getUser()->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $this->getUser()->getUserType() != UserRolesData::ROLE_ADMIN) {
      return $this->redirect($this->generateUrl('app_home'));
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


    return $this->render('check_list/edit.html.twig', $args);
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

    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      return $this->redirectToRoute('app_home');
    }

    return $this->redirectToRoute('app_checklist_to_do');
  }

  #[Route('/start/{id}', name: 'app_checklist_start')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function start(ManagerChecklist $checklist)    : Response { if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    $this->em->getRepository(ManagerChecklist::class)->start($checklist);

    return $this->redirectToRoute('app_checklist_list');
  }

  #[Route('/delete/{id}', name: 'app_checklist_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function delete(ManagerChecklist $checklist)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $this->em->getRepository(ManagerChecklist::class)->delete($checklist);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::DELETE_SUCCESS);

    return $this->redirectToRoute('app_checklist_list');
  }

}
