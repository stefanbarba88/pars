<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Project;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class WidgetController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  public function adminMainSidebar(): Response {
    $loggedUser = $this->getUser();
    $args = [];

    $args['countUsers'] = $this->em->getRepository(User::class)->countUsersByLoggedUser($loggedUser);
    $args['countUsersActive'] = $this->em->getRepository(User::class)->countUsersActiveByLoggedUser($loggedUser);

    $args['countContacts'] = $this->em->getRepository(User::class)->countContacts();
    $args['countContactsActive'] = $this->em->getRepository(User::class)->countContactsActive();
    $args['countClients'] = $this->em->getRepository(Client::class)->count([]);
    $args['countClientsActive'] = $this->em->getRepository(Client::class)->countClientsActive();

    $args['countEmployees'] = $this->em->getRepository(User::class)->countEmployees();
    $args['countEmployeesActive'] = $this->em->getRepository(User::class)->countEmployeesActive();

    $args['countProjectsPermanent'] = $this->em->getRepository(Project::class)->countProjectsPermanent();
    $args['countProjectsChange'] = $this->em->getRepository(Project::class)->countProjectsChange();
    $args['countProjects'] = $this->em->getRepository(Project::class)->count([]);

    $args['countComments'] = $this->em->getRepository(Comment::class)->count([]);
    $args['countCommentsActive'] = $this->em->getRepository(Comment::class)->countCommentsActive();


    $args['countAllTeams'] = $this->em->getRepository(Team::class)->count([]);
    $args['countTeams'] = $this->em->getRepository(Team::class)->countTeams();
    $args['countTeamsActive'] = $this->em->getRepository(Team::class)->countTeamsActive();
    $args['countTeamsInactive'] = $this->em->getRepository(Team::class)->countTeamsInactive();

    return $this->render('widget/main_admin_sidebar.html.twig', $args);
  }

  public function userProfilSidebar(User $user): Response {

    $args['user'] = $user;
//    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['user' => $user]);
    $args['users'] = $this->em->getRepository(User::class)->findAll();

    return $this->render('widget/user_profil_sidebar.html.twig', $args);
  }

  public function support(): Response {

    return $this->render('widget/support.html.twig');
  }

  public function employeeProfilSidebar(User $user): Response {

    $args['user'] = $user;
//    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['user' => $user]);
    $args['users'] = $this->em->getRepository(User::class)->findAll();

    return $this->render('widget/employee_profil_sidebar.html.twig', $args);
  }

  public function userProfilNavigation(User $user): Response {

    $args['user'] = $user;

    return $this->render('widget/users_nav.html.twig', $args);
  }

  public function employeeProfilNavigation(User $user): Response {

    $args['user'] = $user;

    return $this->render('widget/employee_nav.html.twig', $args);
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

  public function header(): Response {

    $user = $this->getUser();
    $args['logged'] = $user;

    return $this->render('includes/header.html.twig', $args);
  }

  public function headerUser(): Response {

    $user = $this->getUser();
    $args['logged'] = $user;
//    dd($args);
    return $this->render('includes/header_user.html.twig', $args);
  }

}
