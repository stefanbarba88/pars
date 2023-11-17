<?php

namespace App\Controller;

use App\Entity\Availability;
use App\Entity\Calendar;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/availability')]
class AvailabilityController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_availability_list')]
  public function list(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];
    $dostupnosti = $this->em->getRepository(Availability::class)->getDostupnostPaginator();

    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;
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
  public function dostupni(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $dostupnosti = $this->em->getRepository(User::class)->getDostupniPaginator();


    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;


    return $this->render('availability/dostupni.html.twig', $args);
  }

  #[Route('/unavailable/', name: 'app_availability_unavailable')]
  public function nedostupni(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];
    $dostupnosti = $this->em->getRepository(User::class)->getNedostupniPaginator();

    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;

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
