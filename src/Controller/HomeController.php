<?php

namespace App\Controller;

use App\Classes\Data\UserRolesData;
use App\Classes\JMBGcheck\JMBGcheck;
use App\Entity\Availability;
use App\Entity\Car;
use App\Entity\FastTask;
use App\Entity\ManagerChecklist;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


//#[IsGranted('ROLE_USER', message: 'Nemas pristup', statusCode: 403)]

class HomeController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }
  #[Route('/', name: 'app_home')]
  public function index()    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $user = $this->getUser();

    $args['sutra'] = new DateTimeImmutable('tomorrow');
    $args['danas'] = new DateTimeImmutable();

    $args['timetable'] = $this->em->getRepository(Task::class)->getTasksByDate($args['danas']);

    $args['plan'] = $this->em->getRepository(FastTask::class)->getTimeTableActive();
    $args['subs'] = $this->em->getRepository(FastTask::class)->getTimeTableSubsActive();

    $args['tomorrowTimetable'] = $this->em->getRepository(FastTask::class)->getTimetable($args['sutra']);
    $args['tomorrowSubs'] = $this->em->getRepository(FastTask::class)->getSubs($args['sutra']);
    $args['tomorrowTimetableId'] = $this->em->getRepository(FastTask::class)->getTimeTableTomorrowId($args['sutra']);
//    $args['dostupnosti'] = $this->em->getRepository(Availability::class)->getDostupnostDanas();
    $args['dostupnosti'] = $this->em->getRepository(Availability::class)->getAllDostupnostiDanas();
    $args['dostupnostiSutra'] = $this->em->getRepository(Availability::class)->getAllDostupnostiSutra();

//srediti ovaj upit, uzima puno resursa
//    $args['countTasksUnclosed'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedLogs();
    $args['countTasksUnclosed'] = 0;

    $args['checklistActive'] = $this->em->getRepository(ManagerChecklist::class)->findBy(['user' => $user, 'status' => 0], ['priority' => 'ASC', 'id' => 'ASC']);
    $args['countChecklistActive'] = count($args['checklistActive']);

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE ) {
      $args['countTasksUnclosedByUser'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedByUser($user);
//      $args['countTasksUnclosed'] = $this->em->getRepository(Task::class)->countGetTasksUnclosedLogsByUser($user);
      $args['logs'] = $this->em->getRepository(TaskLog::class)->findByUser($user);
      $args['countLogs'] = $this->em->getRepository(TaskLog::class)->countLogsByUser($user);
      $mobileDetect = new MobileDetect();
      if($mobileDetect->isMobile()) {
        return $this->render('home/phone/index_employee.html.twig', $args);
      }
      return $this->render('home/index_employee.html.twig', $args);
    }

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('home/phone/index_admin.html.twig', $args);
    }

    return $this->render('home/index_admin.html.twig', $args);
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
