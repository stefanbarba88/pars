<?php

namespace App\Controller;

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
}
