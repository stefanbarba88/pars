<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WidgetController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  public function userProfilSidebar($id): Response {

    $user = $this->em->getRepository(User::class)->find($id);
    $args['user'] = $user;
    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['user' => $user]);
    $args['users'] = $this->em->getRepository(User::class)->findAll();

    return $this->render('widget/user_profil_sidebar.html.twig', $args);
  }

  public function userProfilNavigation($id): Response {

    $user = $this->em->getRepository(User::class)->find($id);
    $args['user'] = $user;

    return $this->render('widget/users_nav.html.twig', $args);
  }

}
