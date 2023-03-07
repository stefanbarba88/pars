<?php

namespace App\Controller;

use App\Classes\JMBGcheck\JMBGcheck;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


//#[IsGranted('ROLE_USER', message: 'Nemas pristup', statusCode: 403)]

class HomeController extends AbstractController {
  #[Route('/', name: 'app_home')]
  public function index(): Response {


    return $this->render('home/index.html.twig', [
      'controller_name' => 'HomeController',
    ]);
  }

  public function nav(): Response {
//    $args = [];
////    $args['sindikati'] = $em->getRepository(GranskiSindikat::class)->findBy(['parent' => GranskiSindikat::PARENT]);
//
//    $userRole = $this->getUser()->getUserType();
//    $args['uputstvo'] = $em->getRepository(Uputstvo::class)->findOneBy(['role' => $userRole]);
//
//    switch ($userRole) {
//      case UserRolesData::ROLE_UPRAVNIK_GRANE;
//
//        $args['grana'] = $this->getUser()->getGranskiSindikatUpGrana();
//        $args['kongres'] = $em->getRepository(Kongres::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false], ['datumOdrzavanja'=>'DESC']);
//        $args['glavniOdbor'] = $em->getRepository(GlavniOdbor::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['izvrsniOdbor'] = $em->getRepository(IzvrsniOdbor::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['nadzorniOdbor'] = $em->getRepository(NadzorniOdbor::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['sekcijaZena'] = $em->getRepository(SekcijaZena::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['sekcijaMladih'] = $em->getRepository(SekcijaMladih::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['skupstina'] = $em->getRepository(Skupstina::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//        $args['predsednistvo'] = $em->getRepository(Predsednistvo::class)->findOneBy(['grana' => $args['grana'], 'isArhiva' => false]);
//
//        return $this->render('navigation/upravnik_grane_navigation.html.twig', $args);
//
//      case UserRolesData::ROLE_UPRAVNIK_CENTRALE;
//        $args['savetUgs'] = $em->getRepository(SavetUgs::class)->findOneBy(['isArhiva' => false]);
//        $args['statutarniOdbor'] = $em->getRepository(StatutarniOdbor::class)->findOneBy(['isArhiva' => false]);
//        return $this->render('navigation/upravnik_centrale_navigation.html.twig', $args);
//
//      case UserRolesData::ROLE_REG_POVERENIK;
//        $args['poverenistvo'] = $this->getUser()->getPoverenistvo();
//        $args['grana'] = $args['poverenistvo']->getGranskiSindikat();
//        return $this->render('navigation/regionalni_poverenik_navigation.html.twig', $args);
//
//      case UserRolesData::ROLE_POVERENIK;
//        return $this->render('navigation/poverenik_navigation.html.twig', $args);
//
//      case UserRolesData::ROLE_SUPER_ADMIN;
//        $args['savetUgs'] = $em->getRepository(SavetUgs::class)->findOneBy(['isArhiva' => false]);
//        $args['statutarniOdbor'] = $em->getRepository(StatutarniOdbor::class)->findOneBy(['isArhiva' => false]);
//        return $this->render('navigation/super_admin_navigation.html.twig', $args);
//
//      default:
//        return new Response('');
//    }
  }
}
