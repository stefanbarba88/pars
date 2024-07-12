<?php

namespace App\Controller;

use App\Classes\Data\InternTaskStatusData;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\ManagerChecklist;
use App\Entity\Ticket;
use App\Form\TicketFormType;
use App\Service\MailService;
use App\Service\UploadService;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tickets')]
class TicketController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/form/{id}', name: 'app_ticket_form', defaults: ['id' => 0])]
  #[Entity('ticket', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Ticket $ticket, MailService $mailService, UploadService $uploadService)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->getIsClientView()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $mobileDetect = new MobileDetect();

    $company = $this->getUser()->getCompany();

    $form = $this->createForm(TicketFormType::class, $ticket, ['attr' => ['action' => $this->generateUrl('app_ticket_form', ['id' => $ticket->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

       if (is_null($ticket->getId())) {
         $this->em->getRepository(Ticket::class)->save($ticket);
         $mailService->createTicket($ticket);
       } else {
         $this->em->getRepository(Ticket::class)->save($ticket);
         $mailService->editTicket($ticket);
       }

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::TICKET_ADD);

        return $this->redirectToRoute('app_ticket_list');
      }
    }
    $args['form'] = $form->createView();
    $args['ticket'] = $ticket;

    return $this->render('ticket/form.html.twig', $args);

  }

  #[Route('/list/', name: 'app_ticket_list')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->getIsClientView()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];

    $user = $this->getUser();

    $tickets = $this->em->getRepository(Ticket::class)->getTicketsPaginator($user, InternTaskStatusData::NIJE_ZAPOCETO);

    $pagination = $paginator->paginate(
      $tickets, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('ticket/phone/list.html.twig', $args);
    }
    return $this->render('ticket/list.html.twig', $args);
  }

  #[Route('/archive/', name: 'app_ticket_archive_list')]
  public function archive(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->getIsClientView()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];

    $user = $this->getUser();

    $tickets = $this->em->getRepository(Ticket::class)->getTicketsPaginator($user, InternTaskStatusData::ZAVRSENO);

    $pagination = $paginator->paginate(
      $tickets, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('ticket/phone/archive.html.twig', $args);
    }
    return $this->render('ticket/archive.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_ticket_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Ticket $ticket)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->getIsClientView()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['ticket'] = $ticket;
    return $this->render('ticket/view.html.twig', $args);
  }

//  #[Route('/convert/{id}', name: 'app_ticket_convert')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function convert(Ticket $ticket, MailService $mailService)    : Response {
//    if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//
//    if ($this->getUser()->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $this->getUser()->getUserType() != UserRolesData::ROLE_ADMIN && $this->getUser()->getUserType() != UserRolesData::ROLE_MANAGER) {
//      if ($this->getUser() != $checklist->getCreatedBy() || $this->getUser() != $checklist->getUser()) {
//        return $this->redirect($this->generateUrl('app_home'));
//      }
//    }
//
//    if ($checklist->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
//      return $this->redirect($this->generateUrl('app_home'));
//    }
//
//    if ($checklist->getStatus() != InternTaskStatusData::NIJE_ZAPOCETO) {
//      notyf()
//        ->position('x', 'right')
//        ->position('y', 'top')
//        ->duration(5000)
//        ->dismissible(true)
//        ->addError(NotifyMessagesData::CHECKLIST_CONVERT_ERROR);
//      return $this->redirectToRoute('app_checklist_list');
//    }
//
//    $koris = $this->getUser();
//
//    $task = $this->em->getRepository(Task::class)->createTaskFromChecklist($checklist);
//    $checklist->setStatus(InternTaskStatusData::KONVERTOVANO);
//    $this->em->getRepository(ManagerChecklist::class)->save($checklist);
//
//    $mailService->checklistConvertTask($checklist, $task);
//
//    notyf()
//      ->position('x', 'right')
//      ->position('y', 'top')
//      ->duration(5000)
//      ->dismissible(true)
//      ->addSuccess(NotifyMessagesData::CHECKLIST_CONVERT);
//
//    return $this->redirectToRoute('app_checklist_list');
//  }

  #[Route('/delete/{id}', name: 'app_ticket_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function delete(Ticket $ticket, MailService $mailService)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->getIsClientView()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $this->em->getRepository(Ticket::class)->remove($ticket);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::TICKET_DELETE);

    return $this->redirectToRoute('app_ticket_list');
  }

  #[Route('/finish/{id}', name: 'app_ticket_finish')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function finish(Ticket $ticket, Request $request, MailService $mailService)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->getIsClientView()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $ticket->setStatus(InternTaskStatusData::ZAVRSENO);
    $this->em->getRepository(Ticket::class)->save($ticket);

    $mailService->finishTicket($ticket);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::TICKET_FINISH);

    return $this->redirectToRoute('app_ticket_list');
  }


}
