<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Image;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class WidgetController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  public function adminMainSidebar(): Response {

    $args = [];

    $args['countUsers'] = $this->em->getRepository(User::class)->count([]);
//    $args['projects'] = $this->em->getRepository(Project::class)->findAll();
//    $args['projects'] = $this->em->getRepository(Project::class)->findAll();
//    $args['projects'] = $this->em->getRepository(Project::class)->findAll();
//    $args['projects'] = $this->em->getRepository(Project::class)->findAll();
    return $this->render('widget/main_admin_sidebar.html.twig', $args);
  }

  public function userProfilSidebar(User $user): Response {

    $args['user'] = $user;
//    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['user' => $user]);
    $args['users'] = $this->em->getRepository(User::class)->findAll();

    return $this->render('widget/user_profil_sidebar.html.twig', $args);
  }

  public function userProfilNavigation(User $user): Response {

    $args['user'] = $user;

    return $this->render('widget/users_nav.html.twig', $args);
  }

  public function projectProfilNavigation(Project $project): Response {

    $args['project'] = $project;

    return $this->render('widget/project_nav.html.twig', $args);
  }

  public function clientProfilNavigation(Client $client): Response {

    $args['client'] = $client;

    return $this->render('widget/clients_nav.html.twig', $args);
  }

  public function rightSidebar(): Response {

    return $this->render('widget/right_sidebar.html.twig');
  }

  public function confirmationModal(string $message): Response {

    $args['message'] = $message;

    return $this->render('widget/confirmation_modal.html.twig', $args);
  }

}
