<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Car;
use App\Entity\CarHistory;
use App\Entity\CarReservation;
use DateTimeImmutable;
use App\Form\CarFormType;
use App\Form\CarReservationFormType;
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
  public function list(): Response {
    $args = [];
    $args['cars'] = $this->em->getRepository(Car::class)->findAll();

    return $this->render('car/list.html.twig', $args);
  }


  #[Route('/form/{id}', name: 'app_car_form', defaults: ['id' => 0])]
  #[Entity('car', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Car $car): Response {
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

    $form = $this->createForm(CarFormType::class, $car, ['attr' => ['action' => $this->generateUrl('app_car_form', ['id' => $car->getId()])]]);
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

        return $this->redirectToRoute('app_cars');
      }
    }
    $args['form'] = $form->createView();
    $args['car'] = $car;

    return $this->render('car/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_car_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Car $car): Response {
    $args['car'] = $car;

    return $this->render('car/view.html.twig', $args);
  }

  #[Route('/activate/{id}', name: 'app_car_activate')]
  public function delete(Car $car): Response {
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

      //dodaj kad se deaktivira da razduzi vozilo ako je bilo zaduzeno
      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::CAR_DEACTIVATE);
    }

    $this->em->getRepository(Car::class)->save($car, $history);

    return $this->redirectToRoute('app_car_view', ['id' => $car->getId()]);
  }

  #[Route('/history-car-list/{id}', name: 'app_car_history_list')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function listCarHistory(Car $car): Response {
    $args['car'] = $car;
    $args['historyCars'] = $this->em->getRepository(CarHistory::class)->findBy(['car' => $car], ['id' => 'DESC']);

    return $this->render('car/car_history_list.html.twig', $args);
  }

  #[Route('/history-car-view/{id}', name: 'app_car_profile_history_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewCarHistory(CarHistory $carHistory, SerializerInterface $serializer): Response {

    $args['carH'] = $serializer->deserialize($carHistory->getHistory(), car::class, 'json');
    $args['carHistory'] = $carHistory;

    return $this->render('car/view_history_profile.html.twig', $args);
  }

  #[Route('/list-reservations/{id}', name: 'app_cars_reservations')]
  public function listReservations(Car $car): Response {
    $args = [];
    $args['reservations'] = $this->em->getRepository(CarReservation::class)->findBy(['car' => $car], ['id' => 'desc']);
    $args['car'] = $car;

    return $this->render('car/list_reservations.html.twig', $args);
  }

  #[Route('/form-reservation/{id}', name: 'app_car_reservation_form')]
  #[Entity('car', expr: 'repository.find(id)')]
  #[Entity('reservation', expr: 'repository.findForFormCar(car)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]

  public function formReservation(CarReservation $reservation, Car $car, Request $request): Response {

    $user = $this->getUser();
    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $reservation->setDriver($user);
    }

    $form = $this->createForm(CarReservationFormType::class, $reservation, ['attr' => ['action' => $this->generateUrl('app_car_reservation_form', ['id' => $car->getId()])]]);
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

        return $this->redirectToRoute('app_cars_reservations');
      }
    }
    $args['form'] = $form->createView();
    $args['car'] = $car;
    $args['reservation'] = $reservation;

    return $this->render('car/form_reservation.html.twig', $args);
  }

  #[Route('/stop-reservation/{id}', name: 'app_car_reservation_stop')]
  public function stopReservation(Car $car): Response {

    $reservation = $this->em->getRepository(CarReservation::class)->findOneBy(['car' => $car], ['id' => 'desc']);

    $reservation->setFinished(new DateTimeImmutable());
    $this->em->getRepository(CarReservation::class)->save($reservation);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

    $this->em->getRepository(CarReservation::class)->save($reservation);

    return $this->redirectToRoute('app_cars_reservations', ['id' => $car->getId()]);
  }

  #[Route('/view-reservation/{id}', name: 'app_car_reservation_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewReservation(CarReservation $reservation): Response {
    $args['reservation'] = $reservation;

    return $this->render('car/view_reservation.html.twig', $args);
  }
}