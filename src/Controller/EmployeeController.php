<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Pdf;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/employees')]
class EmployeeController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }
  #[Route('/list/', name: 'app_employees')]
  public function list(): Response {
    $args = [];
    $args['users'] = $this->em->getRepository(User::class)->getEmployees();

    return $this->render('employee/list.html.twig', $args);
  }

  #[Route('/view-profile/{id}', name: 'app_employee_profile_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile(User $usr): Response {
    $args['user'] = $usr;
    return $this->render('employee/view_profile.html.twig', $args);
  }

  #[Route('/view-activity/{id}', name: 'app_employee_activity_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewActivity(User $usr): Response {
    $args['user'] = $usr;

    return $this->render('user/view_activity.html.twig', $args);
  }

  #[Route('/view-calendar/{id}', name: 'app_employee_calendar_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewCalendar(User $usr): Response {
    $args['user'] = $usr;

    return $this->render('user/view_calendar.html.twig', $args);
  }

  #[Route('/view-cars/{id}', name: 'app_employee_car_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewCar(User $usr): Response {
    $args['user'] = $usr;

    return $this->render('user/view_cars.html.twig', $args);
  }

  #[Route('/view-tools/{id}', name: 'app_employee_tools_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewTools(User $usr): Response {
    $args['user'] = $usr;

    return $this->render('user/view_tools.html.twig', $args);
  }

  #[Route('/view-docs/{id}', name: 'app_employee_docs_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewDocs(User $usr): Response {
    $args['user'] = $usr;
    $args['pdfs'] =  $this->em->getRepository(User::class)->getPdfsByUser($usr);
    return $this->render('employee/view_docs.html.twig', $args);
  }

  #[Route('/view-images/{id}', name: 'app_employee_images_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewImages(User $usr): Response {
    $args['user'] = $usr;
    $args['images'] =  $this->em->getRepository(User::class)->getImagesByUser($usr);

    return $this->render('employee/view_images.html.twig', $args);
  }
  #[Route('/view-comments/{id}', name: 'app_employee_comments_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewComments(User $usr): Response {
    $args['user'] = $usr;
    $args['comments'] =  $this->em->getRepository(Comment::class)->getCommentsByUser($usr);

    return $this->render('employee/view_comments.html.twig', $args);
  }
}
