<?php

namespace App\Controller\Kadrovska;

use App\Classes\Data\UserRolesData;
use App\Entity\Company;
use App\Entity\User;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/kadrovska')]
class KadrovskaHomeController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/', name: 'app_kadrovska_home')]
  public function index()    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $loggedUser = $this->getUser();

    $args['countUsers'] = $this->em->getRepository(User::class)->count(['isKadrovska' => true, 'isSuspended' => false, 'userType' => UserRolesData::ROLE_MANAGER]);

    $args['countCompanies'] = $this->em->getRepository(Company::class)->count(['firma' => $loggedUser->getCompany()->getId(), 'isSuspended' => false]);

    $args['countEmployees'] = $this->em->getRepository(User::class)->count(['isKadrovska' => true, 'isSuspended' => false, 'userType' => UserRolesData::ROLE_EMPLOYEE]);
    $args['user'] = $loggedUser;
//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      return $this->render('_kadrovska/home/phone/index_admin.html.twig', $args);
//    }

    if ($loggedUser->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      return $this->render('_kadrovska/home/index_employee.html.twig', $args);
    }
    return $this->render('_kadrovska/home/index_admin.html.twig', $args);

  }
}
