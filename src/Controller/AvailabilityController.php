<?php

namespace App\Controller;

use App\Entity\Availability;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/availability')]
class AvailabilityController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_availability_list')]
  public function list(): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];
    $user = $this->getUser();

    $args['dostupnosti'] = $this->em->getRepository(Availability::class)->getDostupnost();

    return $this->render('availability/list.html.twig', $args);
  }

  #[Route('/calendar/', name: 'app_availability_calendar')]
  public function calendar(): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];
    $user = $this->getUser();

    $args['dostupnosti'] = $this->em->getRepository(Availability::class)->getDostupnost();


    return $this->render('availability/calendar.html.twig', $args);
  }

  #[Route('/available/', name: 'app_availability_available')]
  public function dostupni(): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];
    $user = $this->getUser();

    $args['dostupni'] = $this->em->getRepository(User::class)->getDostupni();

    return $this->render('availability/dostupni.html.twig', $args);
  }

  #[Route('/unavailable/', name: 'app_availability_unavailable')]
  public function nedostupni(): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];
    $user = $this->getUser();

    $args['nedostupni'] = $this->em->getRepository(User::class)->getNedostupni();

    return $this->render('availability/nedostupni.html.twig', $args);
  }

  #[Route('/delete/{id}', name: 'app_availability_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function delete(Availability $dostupnost): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $this->em->getRepository(Availability::class)->remove($dostupnost);

    return $this->redirectToRoute('app_availability_list');
  }

  #[Route('/make-available/{id}', name: 'app_availability_add')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function add(User $user): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }


    $this->em->getRepository(Availability::class)->makeAvailable($user);

    return $this->redirectToRoute('app_availability_unavailable');
  }


  #[Route('/make-unavailable/{id}', name: 'app_availability_remove')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function remove(User $user): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $this->em->getRepository(Availability::class)->makeUnavailable($user);

    return $this->redirectToRoute('app_availability_available');
  }
}
