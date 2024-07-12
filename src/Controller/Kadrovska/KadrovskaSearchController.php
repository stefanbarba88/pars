<?php

namespace App\Controller\Kadrovska;

use App\Classes\Data\UserRolesData;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/kadrovska')]
class KadrovskaSearchController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/search', name: 'app_search_kadrovska')]
  public function search(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }

    $args = [];
    $search = [];


    $search['tekst'] = $request->query->get('tekst');
    $search['firma'] = $request->query->get('firma');
    $search['zaposleni'] = $request->query->get('zaposleni');
    $search['status'] = $request->query->get('status');
    $search['statusStanje'] = $request->query->get('statusStanje');



//      if (!is_null($search['projekat'])) {
        $projects = $this->em->getRepository(Project::class)->getProjectsSearchPaginator($search, $korisnik);
//      }
//      if (!is_null($search['zadatak'])) {
//        $tasks = $this->em->getRepository(Task::class)->getTasksSearchPaginator($search, $korisnik);
//      }
//      if (!is_null($search['zaposleni'])) {
        $users = $this->em->getRepository(User::class)->getUsersSearchPaginatorKadrovska($search);
//      }

      $pagination = $paginator->paginate(
        $projects, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        10,
        [
          'pageName' => 'page',  // Menjamo naziv parametra za stranicu
          'pageParameterName' => 'page',  // Menjamo naziv parametra za stranicu
          'sortFieldParameterName' => 'sort',  // Menjamo naziv parametra za sortiranje
        ]
      );

//      $pagination1 = $paginator->paginate(
//        $tasks, /* query NOT result */
//        $request->query->getInt('page1', 1), /*page number*/
//        10,
//        [
//          'pageName' => 'page1',  // Menjamo naziv parametra za stranicu
//          'pageParameterName' => 'page1',  // Menjamo naziv parametra za stranicu
//          'sortFieldParameterName' => 'sort1',  // Menjamo naziv parametra za sortiranje
//        ]
//      );
      $pagination2 = $paginator->paginate(
        $users, /* query NOT result */
        $request->query->getInt('page2', 1), /*page number*/
        10,
        [
          'pageName' => 'page2',  // Menjamo naziv parametra za stranicu
          'pageParameterName' => 'page2',  // Menjamo naziv parametra za stranicu
          'sortFieldParameterName' => 'sort2',  // Menjamo naziv parametra za sortiranje
        ]
      );

      $args['pagination'] = $pagination;
      $args['pagination2'] = $pagination2;
//      $args['pagination2'] = $pagination2;


    $session = new Session();
    $session->set('url', $request->getRequestUri());
    $args['search'] = $search;

    return $this->render('_kadrovska/search/list.html.twig', $args);
  }

  #[Route('/search-employee', name: 'app_search_employee_kadrovska')]
  public function searchEmployee(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    $args = [];
    $search = [];


    $search['tekst'] = $request->query->get('tekst');
//    $search['projekat'] = $request->query->get('projekat');
//    $search['zadatak'] = $request->query->get('zadatak');
//    $search['zaposleni'] = $request->query->get('zaposleni');
//    $search['status'] = $request->query->get('status');
//    $search['statusStanje'] = $request->query->get('statusStanje');



//      if (!is_null($search['projekat'])) {
//    $projects = $this->em->getRepository(Project::class)->getProjectsSearchByUserPaginator($korisnik, $search);
    $users = $this->em->getRepository(User::class)->getUsersSearchPaginatorKadrovskaEmployee($search, $korisnik->getCompany());
//      }
//      if (!is_null($search['zadatak'])) {
//    $tasks = $this->em->getRepository(Task::class)->getTasksSearchPaginator($search, $korisnik);
////      }
////      if (!is_null($search['zaposleni'])) {
//    $users = $this->em->getRepository(User::class)->getUsersSearchPaginator($search, $korisnik);
////      }

    $pagination = $paginator->paginate(
      $users, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );



//    $pagination1 = $paginator->paginate(
//      $tasks, /* query NOT result */
//      $request->query->getInt('page1', 1), /*page number*/
//      5,
//      [
//        'pageName' => 'page1',  // Menjamo naziv parametra za stranicu
//        'pageParameterName' => 'page1',  // Menjamo naziv parametra za stranicu
//        'sortFieldParameterName' => 'sort1',  // Menjamo naziv parametra za sortiranje
//      ]
//    );
//    $pagination2 = $paginator->paginate(
//      $users, /* query NOT result */
//      $request->query->getInt('page2', 1), /*page number*/
//      5,
//      [
//        'pageName' => 'page2',  // Menjamo naziv parametra za stranicu
//        'pageParameterName' => 'page2',  // Menjamo naziv parametra za stranicu
//        'sortFieldParameterName' => 'sort2',  // Menjamo naziv parametra za sortiranje
//      ]
//    );

    $args['pagination'] = $pagination;

    $session = new Session();
    $session->set('url', $request->getRequestUri());
    $args['search'] = $search;

//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      return $this->render('search/phone/list_employee.html.twig', $args);
//    }
    return $this->render('_kadrovska/search/list_employee.html.twig', $args);
  }
}
