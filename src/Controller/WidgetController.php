<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class WidgetController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  public function adminMainSidebar(): Response {

    return $this->render('widget/main_admin_sidebar.html.twig');
  }

  public function userProfilSidebar(User $user): Response {

    $args['user'] = $user;
    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['user' => $user]);
    $args['users'] = $this->em->getRepository(User::class)->findAll();

    return $this->render('widget/user_profil_sidebar.html.twig', $args);
  }

  public function userProfilNavigation(User $user): Response {

    $args['user'] = $user;

    return $this->render('widget/users_nav.html.twig', $args);
  }

  public function confirmationModal(User $user, string $message): Response {

    $args['user'] = $user;
    $args['message'] = $message;

    return $this->render('widget/confirmation_modal.html.twig', $args);
  }

}
