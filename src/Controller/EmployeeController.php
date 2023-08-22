<?php

namespace App\Controller;

use App\Entity\CarReservation;
use App\Entity\Comment;
use App\Entity\Expense;
use App\Entity\Notes;
use App\Entity\Pdf;
use App\Entity\ToolReservation;
use App\Entity\User;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/employees')]
class EmployeeController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_employees')]
  public function list(Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $type = $request->query->getInt('type');

    $args['users'] = $this->em->getRepository(User::class)->getEmployees($type);

    return $this->render('employee/list.html.twig', $args);
  }

  #[Route('/view-profile/{id}', name: 'app_employee_profile_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['user'] = $usr;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/view_profile.html.twig', $args);
    }
    return $this->render('employee/view_profile.html.twig', $args);
  }

  #[Route('/view-activity/{id}', name: 'app_employee_activity_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewActivity(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['user'] = $usr;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/view_activity.html.twig', $args);
    }
    return $this->render('employee/view_activity.html.twig', $args);
  }

  #[Route('/view-calendar/{id}', name: 'app_employee_calendar_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewCalendar(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['user'] = $usr;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/view_calendar.html.twig', $args);
    }
    return $this->render('employee/view_calendar.html.twig', $args);
  }

  #[Route('/view-cars/{id}', name: 'app_employee_car_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewCar(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['user'] = $usr;
    $args['reservations'] = $usr->getCarReservations();
    $args['lastReservation'] = $this->em->getRepository(CarReservation::class)->findOneBy(['driver' => $usr], ['id' => 'desc']);
    $args['expenses'] = $this->em->getRepository(Expense::class)->findBy(['createdBy' => $usr, 'isSuspended' => false], ['id' => 'desc']);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/view_cars.html.twig', $args);
    }
    return $this->render('employee/view_cars.html.twig', $args);
  }

  #[Route('/view-tools/{id}', name: 'app_employee_tools_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewTools(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['user'] = $usr;
    $args['reservations'] = $usr->getToolReservations();

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/view_tools.html.twig', $args);
    }

    return $this->render('employee/view_tools.html.twig', $args);
  }

  #[Route('/view-docs/{id}', name: 'app_employee_docs_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewDocs(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['user'] = $usr;
    $args['pdfs'] = $this->em->getRepository(User::class)->getPdfsByUser($usr);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/view_docs.html.twig', $args);
    }

    return $this->render('employee/view_docs.html.twig', $args);
  }

  #[Route('/view-images/{id}', name: 'app_employee_images_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewImages(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['user'] = $usr;
    $args['images'] = $this->em->getRepository(User::class)->getImagesByUser($usr);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/view_images.html.twig', $args);
    }

    return $this->render('employee/view_images.html.twig', $args);
  }

  #[Route('/view-comments/{id}', name: 'app_employee_comments_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewComments(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['user'] = $usr;
    $args['comments'] = $this->em->getRepository(Comment::class)->getCommentsByUser($usr);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/view_comments.html.twig', $args);
    }

    return $this->render('employee/view_comments.html.twig', $args);
  }

  #[Route('/view-notes/{id}', name: 'app_employee_notes_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewNotes(User $usr): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

      $args['user'] = $usr;
      $args['notes'] = $this->em->getRepository(Notes::class)->findBy(['user' => $usr], ['isSuspended' => 'ASC', 'id' => 'DESC']);
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/view_notes.html.twig', $args);
    }

      return $this->render('employee/view_notes.html.twig', $args);

  }


}
