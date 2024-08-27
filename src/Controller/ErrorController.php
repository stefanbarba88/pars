<?php

namespace App\Controller;

use App\Classes\Data\UserRolesData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController {
  #[Route('/error', name: 'error_page')]
  public function error(): Response {
    $args = [];

    if ($this->getUser()->isKadrovska()) {
      return $this->render('_kadrovska/error/error.html.twig', $args);
    }


    return $this->render('error/error.html.twig', $args);
  }
}
