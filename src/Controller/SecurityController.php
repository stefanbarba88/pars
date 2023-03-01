<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController {

  #[Route('/', name: 'app_login')]
  public function login(AuthenticationUtils $authenticationUtils): Response {
    $args = [];

    $error = $authenticationUtils->getLastAuthenticationError();

    // last username entered by the user
    $lastUsername = $authenticationUtils->getLastUsername();

    $args['error'] = $error;
    $args['lastUsername'] = $lastUsername;

    return $this->render('login.html.twig', $args);
  }

  #[Route('/logout', name: 'app_logout', methods: 'GET')]
  public function logout(): Response {
    return new Response('logout');
  }

}
