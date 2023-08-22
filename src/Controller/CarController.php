<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Car;
use App\Entity\CarHistory;
use App\Entity\CarReservation;
use App\Entity\Expense;
use App\Entity\FastTask;
use App\Entity\Tool;
use App\Entity\ToolReservation;
use App\Form\CarReservationFormDetailsType;
use App\Form\CarStopReservationFormDetailsType;
use App\Form\CarStopReservationFormType;
use App\Form\ExpenseFormType;
use App\Form\PhoneExpenseFormType;
use App\Repository\FastTaskRepository;
use DateTimeImmutable;
use App\Form\CarFormType;
use App\Form\CarReservationFormType;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/cars')]
class CarController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_cars')]
  public function list(Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $type = $request->query->getInt('type');
    $args = [];
    switch ($type) {
      case 1:
        $args['cars'] = $this->em->getRepository(Car::class)->findBy(['isReserved' => true, 'isSuspended' => false]);
        break;
      case 2:
        $args['cars'] = $this->em->getRepository(Car::class)->findBy(['isReserved' => false, 'isSuspended' => false]);
        break;
      default:
        $args['cars'] = $this->em->getRepository(Car::class)->findAll();
    }
    $args['type'] = $type;

    $user = $this->getUser();

//    if ($user->getUserType() == UserRolesData::ROLE_ADMIN || $user->getUserType() == UserRolesData::ROLE_SUPER_ADMIN) {
//      $args['type'] = $type;
//    }
    return $this->render('car/list.html.twig', $args);
  }


  #[Route('/form/{id}', name: 'app_car_form', defaults: ['id' => 0])]
  #[Entity('car', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Car $car): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $type = $request->query->getInt('type');
    $history = null;
    if($car->getId()) {
      $history = $this->json($car, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $form = $this->createForm(CarFormType::class, $car, ['attr' => ['action' => $this->generateUrl('app_car_form', ['id' => $car->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Car::class)->save($car, $history);
        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);


        if ($type == 1) {
          return $this->redirectToRoute('app_cars');
        }
        return $this->redirectToRoute('app_car_view', ['id' => $car->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['car'] = $car;

    return $this->render('car/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_car_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Car $car): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['car'] = $car;

    return $this->render('car/view.html.twig', $args);
  }

  #[Route('/activate/{id}', name: 'app_car_activate')]
  public function delete(Car $car, Request $request): Response {

    $type = $request->query->getInt('type');

    $history = null;
    if($car->getId()) {
      $history = $this->json($car, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    if ($car->isSuspended()) {
      $car->setIsSuspended(false);
      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::CAR_ACTIVATE);
    } else {
      $car->setIsSuspended(true);

      $reservation = $this->em->getRepository(CarReservation::class)->findOneBy(['car' => $car], ['id' => 'desc']);

      if (!is_null($reservation)) {
        $reservation->setFinished(new DateTimeImmutable());
        $this->em->getRepository(CarReservation::class)->save($reservation);
      }

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::CAR_DEACTIVATE);
    }


    $this->em->getRepository(Car::class)->save($car, $history);

    if ($type == 1) {
      return $this->redirectToRoute('app_cars');
    }

    return $this->redirectToRoute('app_car_view', ['id' => $car->getId()]);
  }

  #[Route('/history-car-list/{id}', name: 'app_car_history_list')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function listCarHistory(Car $car): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['car'] = $car;
    $args['historyCars'] = $this->em->getRepository(CarHistory::class)->findBy(['car' => $car], ['id' => 'DESC']);

    return $this->render('car/car_history_list.html.twig', $args);
  }

  #[Route('/history-car-view/{id}', name: 'app_car_profile_history_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewCarHistory(CarHistory $carHistory, SerializerInterface $serializer): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args['carH'] = $serializer->deserialize($carHistory->getHistory(), car::class, 'json');
    $args['carHistory'] = $carHistory;

    return $this->render('car/view_history_profile.html.twig', $args);
  }

  #[Route('/list-reservations/{id}', name: 'app_cars_reservations')]
  public function listReservations(Car $car): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $args['reservations'] = $this->em->getRepository(CarReservation::class)->findBy(['car' => $car], ['id' => 'desc']);
    $args['lastReservation'] = $this->em->getRepository(CarReservation::class)->findOneBy(['car' => $car], ['id' => 'desc']);

    $args['car'] = $car;

    return $this->render('car/list_reservations.html.twig', $args);
  }

  #[Route('/form-reservation/{id}', name: 'app_car_reservation_form')]
  #[Entity('car', expr: 'repository.find(id)')]
  #[Entity('reservation', expr: 'repository.findForFormCar(car)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]

  public function formReservation(CarReservation $reservation, Car $car, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $type = $request->query->getInt('type');
    $user = $this->getUser();
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $reservation->setDriver($user);
    }

    $form = $this->createForm(CarReservationFormType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_car_reservation_form', ['id' => $car->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(CarReservation::class)->save($reservation);


        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);
        if ($type == 1) {
          return $this->redirectToRoute('app_cars');
        }
        if ($type == 2) {
          return $this->redirectToRoute('app_car_tools_details_view');
        }
        return $this->redirectToRoute('app_cars_reservations', ['id' => $car->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['car'] = $car;
    $args['reservation'] = $reservation;

    return $this->render('car/form_reservation.html.twig', $args);
  }

  #[Route('/stop-reservation/{id}', name: 'app_car_reservation_stop')]
  public function stopReservation(CarReservation $reservation, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $type = $request->query->getInt('type');
    $form = $this->createForm(CarStopReservationFormType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_car_reservation_stop', ['id' => $reservation->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $reservation->setFinished(new DateTimeImmutable());
        $this->em->getRepository(CarReservation::class)->save($reservation);


        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);
        if ($type == 1) {
          return $this->redirectToRoute('app_cars');
        }
        return $this->redirectToRoute('app_cars_reservations', ['id' => $reservation->getCar()->getId()]);
      }
    }

    $args['form'] = $form->createView();
    $args['reservation'] = $reservation;
    $args['car'] = $reservation->getCar();

    return $this->render('car/form_reservation_stop.html.twig', $args);
  }

  #[Route('/view-reservation/{id}', name: 'app_car_reservation_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewReservation(CarReservation $reservation): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['reservation'] = $reservation;

    return $this->render('car/view_reservation.html.twig', $args);
  }

  #[Route('/list-expenses/{id}', name: 'app_cars_expenses')]
  public function listExpenses(Car $car): Response {
    $args = [];
    $args['expenses'] = $this->em->getRepository(Expense::class)->findBy(['car' => $car], ['id' => 'desc']);

    $args['car'] = $car;

    return $this->render('car/list_expenses.html.twig', $args);
  }

  #[Route('/form-expense/{id}', name: 'app_car_expense_form')]
  #[Entity('car', expr: 'repository.find(id)')]
  #[Entity('expense', expr: 'repository.findForFormCar(car)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formExpense(Expense $expense, Car $car, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $type = $request->query->getInt('type');

    $form = $this->createForm(ExpenseFormType::class, $expense, ['attr' => ['action' => $this->generateUrl('app_car_expense_form', ['id' => $car->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Expense::class)->save($expense);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);
        if ($type == 1) {
          return $this->redirectToRoute('app_cars');
        }
        return $this->redirectToRoute('app_cars_expenses', ['id' => $car->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['car'] = $car;
    $args['expense'] = $expense;

    return $this->render('car/form_expense.html.twig', $args);
  }


  #[Route('/edit-expense/{id}', name: 'app_car_expense_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editExpense(Expense $expense, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $form = $this->createForm(ExpenseFormType::class, $expense, ['attr' => ['action' => $this->generateUrl('app_car_expense_edit', ['id' => $expense->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Expense::class)->save($expense);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);

        return $this->redirectToRoute('app_cars_expenses', ['id' => $expense->getCar()->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['car'] = $expense->getCar();
    $args['expense'] = $expense;

    return $this->render('car/form_expense.html.twig', $args);
  }


  #[Route('/view-expense/{id}', name: 'app_car_expense_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewExpense(Expense $expense): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['expense'] = $expense;

    return $this->render('car/view_expense.html.twig', $args);
  }


  #[Route('/form-employee-reservation/{id}', name: 'app_car_employee_reservation_form', defaults: ['id' => 0])]
  #[Entity('reservation', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]

  public function formEmployeeReservation(CarReservation $reservation, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();

    $reservation->setDriver($user);


    $form = $this->createForm(CarReservationFormType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_car_employee_reservation_form', ['id' => $reservation->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(CarReservation::class)->save($reservation);


        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);

        return $this->redirectToRoute('app_employee_car_view', ['id' => $user->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $user;
    $args['reservation'] = $reservation;
    $args['minKm'] = $this->em->getRepository(Car::class)->getCarsKm();
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('car/phone/form_reservation_employee.html.twig', $args);
    }
    return $this->render('car/form_reservation_employee.html.twig', $args);
  }

  #[Route('/stop-employee-reservation/{id}', name: 'app_car_employee_reservation_stop')]
  public function stopEmployeeReservation(CarReservation $reservation, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();
    $form = $this->createForm(CarStopReservationFormType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_car_employee_reservation_stop', ['id' => $reservation->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $reservation->setFinished(new DateTimeImmutable());
        $this->em->getRepository(CarReservation::class)->save($reservation);


        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);

        return $this->redirectToRoute('app_employee_car_view', ['id' => $user->getId()]);
      }
    }

    $args['form'] = $form->createView();
    $args['reservation'] = $reservation;
    $args['car'] = $reservation->getCar();
    $args['user'] = $user;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('car/phone/form_reservation_stop_employee.html.twig', $args);
    }
    return $this->render('car/form_reservation_stop_employee.html.twig', $args);
  }

  #[Route('/view-employee-reservation/{id}', name: 'app_car_employee_reservation_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewEmployeeReservation(CarReservation $reservation): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['reservation'] = $reservation;
    $args['user'] = $this->getUser();
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('car/phone/view_reservation.html.twig', $args);
    }
    return $this->render('car/view_reservation_employee.html.twig', $args);
  }

  #[Route('/form-employee-expense/{id}', name: 'app_car_employee_expense_form')]
  #[Entity('car', expr: 'repository.find(id)')]
  #[Entity('expense', expr: 'repository.findForFormCar(car)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formEmployeeExpense(Expense $expense, Car $car, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $user = $this->getUser();


    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      $form = $this->createForm(PhoneExpenseFormType::class, $expense, ['attr' => ['action' => $this->generateUrl('app_car_employee_expense_form', ['id' => $car->getId()])]]);
    } else {
      $form = $this->createForm(ExpenseFormType::class, $expense, ['attr' => ['action' => $this->generateUrl('app_car_employee_expense_form', ['id' => $car->getId()])]]);
    }

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Expense::class)->save($expense);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);

        return $this->redirectToRoute('app_employee_car_view', ['id' => $user->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['car'] = $car;
    $args['expense'] = $expense;
    $args['user'] = $user;

    if($mobileDetect->isMobile()) {
      return $this->render('car/phone/form_expense_employee.html.twig', $args);
    }
    return $this->render('car/form_expense_employee.html.twig', $args);
  }

  #[Route('/edit-employee-expense/{id}', name: 'app_car_employee_expense_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editEmployeeExpense(Expense $expense, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $user = $this->getUser();

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      $form = $this->createForm(PhoneExpenseFormType::class, $expense, ['attr' => ['action' => $this->generateUrl('app_car_employee_expense_edit', ['id' => $expense->getId()])]]);
    } else {
      $form = $this->createForm(ExpenseFormType::class, $expense, ['attr' => ['action' => $this->generateUrl('app_car_employee_expense_edit', ['id' => $expense->getId()])]]);
    }

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Expense::class)->save($expense);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CAR_ADD);

        return $this->redirectToRoute('app_employee_car_view', ['id' => $user->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['car'] = $expense->getCar();
    $args['expense'] = $expense;
    $args['user'] = $user;

    if($mobileDetect->isMobile()) {
      return $this->render('car/phone/form_expense_employee.html.twig', $args);
    }
    return $this->render('car/form_expense_employee.html.twig', $args);
  }

  #[Route('/view-employee-expense/{id}', name: 'app_car_employee_expense_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewEmployeeExpense(Expense $expense): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['expense'] = $expense;
    $args['user'] = $this->getUser();

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('car/phone/view_expense_employee.html.twig', $args);
    }
    return $this->render('car/view_expense_employee.html.twig', $args);
  }

  #[Route('/delete-expense/{id}', name: 'app_car_expense_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function deleteExpense(Expense $expense): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $user = $this->getUser();

    $expense->setIsSuspended(true);
    $this->em->getRepository(Expense::class)->save($expense);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::CAR_DEACTIVATE);

    return $this->redirectToRoute('app_employee_car_view', ['id' => $user->getId()]);

  }


  #[Route('/view-details-car-tools/', name: 'app_car_tools_details_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewDetailsCarTools(): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['user'] = $this->getUser();

    $args['reservation'] = $this->em->getRepository(CarReservation::class)->findOneBy(['driver' => $this->getUser()], ['id' => 'desc']);
    if (!is_null($args['reservation'])) {
      $car = $args['reservation']->getCar();
      $args['whereCarShouldGo'] = $this->em->getRepository(FastTask::class)->whereCarShouldGo($car);
    }
    $args['toolsReservation'] = $this->em->getRepository(Tool::class)->findReservedToolsBy($this->getUser());


    $args['carToReserve'] = $this->em->getRepository(FastTask::class)->findCarToReserve($this->getUser());
    $args['lastReservation'] = $this->em->getRepository(CarReservation::class)->findOneBy(['driver' => $this->getUser(), 'finished' => null], ['id' => 'desc']);
    $args['toolsToReserve'] = $this->em->getRepository(FastTask::class)->findToolsToReserve($this->getUser());

    return $this->render('car/view_details_car_tools.html.twig', $args);
  }

  #[Route('/stop-employee-reservation-details/{id}', name: 'app_car_employee_reservation_stop_details')]
  public function stopEmployeeReservationDetails(CarReservation $reservation, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();
    $form = $this->createForm(CarStopReservationFormDetailsType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_car_employee_reservation_stop_details', ['id' => $reservation->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $reservation->setFinished(new DateTimeImmutable());
        $this->em->getRepository(CarReservation::class)->save($reservation);


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
    $args['car'] = $reservation->getCar();
    $args['user'] = $user;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('car/phone/form_reservation_stop_details.html.twig', $args);
    }
    return $this->render('car/form_reservation_stop_details.html.twig', $args);
  }

  #[Route('/form-employee-reservation-details/{id}', name: 'app_car_employee_reservation_details_form', defaults: ['id' => 0])]
  #[Entity('car', expr: 'repository.find(id)')]
  #[Entity('reservation', expr: 'repository.findForFormCar(car)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]

  public function formEmployeeReservationDetails(CarReservation $reservation, Car $car, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $reservation->setDriver($user);
      $reservation->setKmStart($car->getKm());
    }

    $form = $this->createForm(CarReservationFormDetailsType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_car_employee_reservation_details_form', ['id' => $car->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(CarReservation::class)->save($reservation);


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
    $args['car'] = $car;
    $args['reservation'] = $reservation;
    $args['minKm'] = $this->em->getRepository(Car::class)->getCarsKm();

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('car/phone/form_reservation_employee_details_form.html.twig', $args);
    }
    return $this->render('car/form_reservation_employee_details_form.html.twig', $args);
  }

}