<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/tools')]
class ToolController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_tools')]
  public function list(Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $type = $request->query->getInt('type');
    $args = [];

    switch ($type) {
      case 1:
        $args['tools'] = $this->em->getRepository(Tool::class)->findBy(['isReserved' => true, 'isSuspended' => false]);
        break;
      case 2:
        $args['tools'] = $this->em->getRepository(Tool::class)->getInactiveTools();
        break;
      default:
        $args['tools'] = $this->em->getRepository(Tool::class)->findAll();
    }

    $args['type'] = $type;

    $user = $this->getUser();

//    if ($user->getUserType() == UserRolesData::ROLE_ADMIN || $user->getUserType() == UserRolesData::ROLE_SUPER_ADMIN) {
//      $args['type'] = $type;
//    }
    return $this->render('tool/list.html.twig', $args);
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
          ->addSuccess(NotifyMessagesData::CAR_ADD);


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
        ->addSuccess(NotifyMessagesData::CAR_ACTIVATE);
    } else {
      $tool->setIsSuspended(true);

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::CAR_DEACTIVATE);
    }


    $this->em->getRepository(Tool::class)->save($tool, $history);

    if ($type == 1) {
      return $this->redirectToRoute('app_tools');
    }

    return $this->redirectToRoute('app_tool_view', ['id' => $tool->getId()]);
  }

  #[Route('/history-tool-list/{id}', name: 'app_tool_history_list')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function listToolHistory(Tool $tool): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['tool'] = $tool;
    $args['historyTools'] = $this->em->getRepository(ToolHistory::class)->findBy(['tool' => $tool], ['id' => 'DESC']);

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
  public function listReservations(Tool $tool): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $args['reservations'] = $this->em->getRepository(ToolReservation::class)->findBy(['tool' => $tool], ['id' => 'desc']);
    $args['lastReservation'] = $this->em->getRepository(ToolReservation::class)->findOneBy(['tool' => $tool], ['id' => 'desc']);

    $args['tool'] = $tool;

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
          ->addSuccess(NotifyMessagesData::CAR_ADD);
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
          ->addSuccess(NotifyMessagesData::CAR_ADD);


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


        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);
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


        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);

        return $this->redirectToRoute('app_car_tools_details_view');
      }
    }

    $args['form'] = $form->createView();
    $args['reservation'] = $reservation;
    $args['tool'] = $reservation->getTool();
    $args['user'] = $user;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/form_reservation_stop_details.html.twig', $args);
    }
    return $this->render('tool/form_reservation_stop_details.html.twig', $args);
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
          ->addSuccess(NotifyMessagesData::CAR_ADD);

        return $this->redirectToRoute('app_car_tools_details_view');

      }
    }
    $args['form'] = $form->createView();
    $args['tool'] = $tool;
    $args['reservation'] = $reservation;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('tool/phone/form_reservation_employee_details_form.html.twig', $args);
    }
    return $this->render('tool/form_reservation_employee_details_form.html.twig', $args);
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


        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);

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