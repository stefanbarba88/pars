<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


//#[IsGranted('ROLE_USER', message: 'Nemas pristup', statusCode: 403)]

class HomeController extends AbstractController {
  #[Route('/', name: 'app_home')]
  public function index(): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirectToRoute('app_login');
    }

    return $this->render('home/index.html.twig', [
      'controller_name' => 'HomeController',
    ]);
  }
}
