<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\TipOpremeData;
use App\Classes\Data\UserRolesData;
use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\Tool;
use App\Entity\ToolHistory;
use App\Entity\ToolReservation;
use App\Form\CarFormType;
use App\Form\CarReservationFormType;
use App\Form\CarStopReservationFormType;
use App\Form\ToolFormType;
use App\Form\ToolReservationFormDetailsType;
use App\Form\ToolReservationFormType;
use App\Form\ToolStopReservationFormDetailsType;
use App\Form\ToolStopReservationFormType;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/tools')]
class ToolController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_tools')]
  public function list(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $search = [];

    $search['naziv'] = $request->query->get('naziv');
    $search['tip'] = $request->query->get('tip');

    $cars = $this->em->getRepository(Tool::class)->getToolsPaginator($search, 0);

    $pagination = $paginator->paginate(
      $cars, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['tip'] = TipOpremeData::TIP;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/list.html.twig', $args);
    }

    return $this->render('tool/list.html.twig', $args);
  }

  #[Route('/list-reserved/', name: 'app_tools_reserved')]
  public function reserved(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $search = [];

    $search['naziv'] = $request->query->get('naziv');
    $search['tip'] = $request->query->get('tip');

    $cars = $this->em->getRepository(Tool::class)->getToolsReservedPaginator($search, 1);

    $pagination = $paginator->paginate(
      $cars, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['tip'] = TipOpremeData::TIP;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/reserved.html.twig', $args);
    }

    return $this->render('tool/reserved.html.twig', $args);
  }

  #[Route('/list-available/', name: 'app_tools_available')]
  public function available(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $search = [];

    $search['naziv'] = $request->query->get('naziv');
    $search['tip'] = $request->query->get('tip');

    $cars = $this->em->getRepository(Tool::class)->getToolsReservedPaginator($search, 0);

    $pagination = $paginator->paginate(
      $cars, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['tip'] = TipOpremeData::TIP;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/available.html.twig', $args);
    }

    return $this->render('tool/available.html.twig', $args);
  }

  #[Route('/list-archive/', name: 'app_tools_archive')]
  public function archive(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $search = [];

    $search['naziv'] = $request->query->get('naziv');
    $search['tip'] = $request->query->get('tip');

    $cars = $this->em->getRepository(Tool::class)->getToolsPaginator($search, 1);

    $pagination = $paginator->paginate(
      $cars, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['tip'] = TipOpremeData::TIP;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/archive.html.twig', $args);
    }

    return $this->render('tool/archive.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_tool_form', defaults: ['id' => 0])]
  #[Entity('tool', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Tool $tool): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $type = $request->query->getInt('type');
    $history = null;
    if($tool->getId()) {
      $history = $this->json($tool, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $form = $this->createForm(ToolFormType::class, $tool, ['attr' => ['action' => $this->generateUrl('app_tool_form', ['id' => $tool->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Tool::class)->save($tool, $history);
        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::TOOL_ADD);


        if ($type == 1) {
          return $this->redirectToRoute('app_tools');
        }
        return $this->redirectToRoute('app_tool_view', ['id' => $tool->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['tool'] = $tool;

    return $this->render('tool/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_tool_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Tool $tool): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['tool'] = $tool;

    return $this->render('tool/view.html.twig', $args);
  }

  #[Route('/activate/{id}', name: 'app_tool_activate')]
  public function delete(Tool $tool, Request $request): Response {

    $type = $request->query->getInt('type');

    $history = null;
    if($tool->getId()) {
      $history = $this->json($tool, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    if ($tool->isSuspended()) {
      $tool->setIsSuspended(false);
      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::TOOL_ACTIVATE);
    } else {
      $tool->setIsSuspended(true);

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::TOOL_DEACTIVATE);
    }


    $this->em->getRepository(Tool::class)->save($tool, $history);

    if ($type == 1) {
      return $this->redirectToRoute('app_tools');
    }

    return $this->redirectToRoute('app_tool_view', ['id' => $tool->getId()]);
  }

  #[Route('/history-tool-list/{id}', name: 'app_tool_history_list')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function listToolHistory(Tool $tool, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $args['tool'] = $tool;

    $cars = $this->em->getRepository(ToolHistory::class)->getToolsHistoryPaginator($tool);

    $pagination = $paginator->paginate(
      $cars, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/tool_history_list.html.twig', $args);
    }

    return $this->render('tool/tool_history_list.html.twig', $args);

  }

  #[Route('/history-tool-view/{id}', name: 'app_tool_profile_history_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewToolHistory(ToolHistory $toolHistory, SerializerInterface $serializer): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args['toolH'] = $serializer->deserialize($toolHistory->getHistory(), tool::class, 'json');
    $args['toolHistory'] = $toolHistory;

    return $this->render('tool/view_history_profile.html.twig', $args);
  }

  #[Route('/list-reservations/{id}', name: 'app_tools_reservations')]
  public function listReservations(Tool $tool, PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $args['tool'] = $tool;
    $reservations = $this->em->getRepository(ToolReservation::class)->getReservationsByToolPaginator($tool);
    $args['lastReservation'] = $this->em->getRepository(ToolReservation::class)->findOneBy(['tool' => $tool], ['id' => 'desc']);

    $pagination = $paginator->paginate(
      $reservations, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/list_reservations.html.twig', $args);
    }

    return $this->render('tool/list_reservations.html.twig', $args);
  }

  #[Route('/form-reservation/{id}', name: 'app_tool_reservation_form')]
  #[Entity('tool', expr: 'repository.find(id)')]
  #[Entity('reservation', expr: 'repository.findForFormTool(tool)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]

  public function formReservation(ToolReservation $reservation, Tool $tool, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $type = $request->query->getInt('type');
    $user = $this->getUser();
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $reservation->setUser($user);
    }

    $form = $this->createForm(ToolReservationFormType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_tool_reservation_form', ['id' => $tool->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(ToolReservation::class)->save($reservation);


        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::TOOL_RESERVE);
        if ($type == 1) {
          return $this->redirectToRoute('app_tools');
        }
        if ($type == 2) {
          return $this->redirectToRoute('app_car_tools_details_view');
        }
        return $this->redirectToRoute('app_tools_reservations', ['id' => $tool->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['tool'] = $tool;
    $args['reservation'] = $reservation;

    return $this->render('tool/form_reservation.html.twig', $args);
  }

  #[Route('/form-reservation-user/', name: 'app_tool_reservation_user_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]

  public function formReservationUser(Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $type = $request->query->getInt('type');
    $user = $this->getUser();
    $reservation = new ToolReservation();
    $reservation->setCompany($user->getCompany());
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $reservation->setUser($user);
    }

    $form = $this->createForm(ToolReservationFormDetailsType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_tool_reservation_user_form')]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(ToolReservation::class)->save($reservation);


        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::TOOL_RESERVE);


        return $this->redirectToRoute('app_employee_tools_view', ['id' => $user->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['reservation'] = $reservation;
    $mobileDetect = new MobileDetect();

    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/form_reservation_employee_details_form_user.html.twig', $args);
    }
    return $this->render('tool/form_reservation_employee_details_form_user.html.twig', $args);
  }

  #[Route('/stop-reservation/{id}', name: 'app_tool_reservation_stop')]
  public function stopReservation(ToolReservation $reservation, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $type = $request->query->getInt('type');
    $form = $this->createForm(ToolStopReservationFormType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_tool_reservation_stop', ['id' => $reservation->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $reservation->setFinished(new DateTimeImmutable());
        $this->em->getRepository(ToolReservation::class)->save($reservation);

        if ($type == 1) {
          return $this->redirectToRoute('app_tools');
        }
        return $this->redirectToRoute('app_tools_reservations', ['id' => $reservation->getTool()->getId()]);
      }
    }

    $args['form'] = $form->createView();
    $args['reservation'] = $reservation;
    $args['tool'] = $reservation->getTool();

    return $this->render('tool/form_reservation_stop.html.twig', $args);
  }

  #[Route('/view-reservation/{id}', name: 'app_tool_reservation_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewReservation(ToolReservation $reservation): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['reservation'] = $reservation;

    return $this->render('tool/view_reservation.html.twig', $args);
  }

  #[Route('/stop-employee-reservation-details-tool/{id}', name: 'app_tool_employee_reservation_stop_details')]
  public function stopEmployeeReservationDetails(ToolReservation $reservation, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();
    $form = $this->createForm(ToolStopReservationFormDetailsType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_tool_employee_reservation_stop_details', ['id' => $reservation->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $reservation->setFinished(new DateTimeImmutable());
        $this->em->getRepository(ToolReservation::class)->save($reservation);

        return $this->redirectToRoute('app_car_tools_details_view');
      }
    }

    $args['form'] = $form->createView();
    $args['reservation'] = $reservation;
    $args['tool'] = $reservation->getTool();
    $args['user'] = $user;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/form_reservation_stop_employee.html.twig', $args);
    }
    return $this->render('tool/form_reservation_stop_employee.html.twig', $args);
  }

  #[Route('/form-employee-reservation-details-tool/{id}', name: 'app_employee_reservation_details_tool_form', defaults: ['id' => 0])]
  #[Entity('tool', expr: 'repository.find(id)')]
  #[Entity('reservation', expr: 'repository.findForFormTool(tool)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]

  public function formEmployeeReservationDetailsTool(ToolReservation $reservation, Tool $tool, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $reservation->setUser($user);
    }



    $form = $this->createForm(ToolReservationFormDetailsType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_employee_reservation_details_tool_form', ['id' => $tool->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(ToolReservation::class)->save($reservation);


        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::TOOL_RESERVE);

        return $this->redirectToRoute('app_car_tools_details_view');

      }
    }
    $args['form'] = $form->createView();
    $args['tool'] = $tool;
    $args['reservation'] = $reservation;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/form_reservation_employee_details_form_user.html.twig', $args);
    }
    return $this->render('tool/form_reservation_employee_details_form_user.html.twig', $args);
  }


  #[Route('/stop-employee-reservation-tool/{id}', name: 'app_tool_employee_reservation_stop')]
  public function stopEmployeeReservationTool(ToolReservation $reservation, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();
    $form = $this->createForm(ToolStopReservationFormType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_tool_employee_reservation_stop', ['id' => $reservation->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $reservation->setFinished(new DateTimeImmutable());
        $this->em->getRepository(ToolReservation::class)->save($reservation);

        return $this->redirectToRoute('app_employee_tools_view', ['id' => $user->getId()]);
      }
    }

    $args['form'] = $form->createView();
    $args['reservation'] = $reservation;
    $args['tool'] = $reservation->getTool();
    $args['user'] = $user;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/form_reservation_stop_employee.html.twig', $args);
    }
    return $this->render('tool/form_reservation_stop_employee.html.twig', $args);
  }

  #[Route('/view-employee-reservation-tool/{id}', name: 'app_tool_employee_reservation_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewEmployeeReservationTool(ToolReservation $reservation): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['reservation'] = $reservation;
    $args['user'] = $this->getUser();
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/view_reservation_employee.html.twig', $args);
    }
    return $this->render('tool/view_reservation_employee.html.twig', $args);
  }

}