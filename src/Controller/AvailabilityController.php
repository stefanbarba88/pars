<?php

namespace App\Controller;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\CalendarData;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Entity\Availability;
use App\Entity\Calendar;
use App\Entity\User;
use App\Entity\Vacation;
use App\Form\AvailabilityFormType;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/availability')]
class AvailabilityController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_availability_list')]
  public function list(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];
    $dostupnosti = $this->em->getRepository(Availability::class)->getDostupnostPaginator();

    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $args['dostupnosti'] = $this->em->getRepository(Availability::class)->getDostupnost();


    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('availability/phone/list.html.twig', $args);
    }

    return $this->render('availability/list.html.twig', $args);

  }

//  #[Route('/calendar/', name: 'app_availability_calendar')]
//  public function calendar(): Response {
//    if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//
//    $args = [];
//    $user = $this->getUser();
//
//    $args['dostupnosti'] = $this->em->getRepository(Availability::class)->getDostupnost();
//
//
//    return $this->render('availability/calendar.html.twig', $args);
//  }

  #[Route('/available/', name: 'app_availability_available')]
  public function dostupni(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $dostupnosti = $this->em->getRepository(User::class)->getDostupniPaginator();


    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('availability/phone/dostupni.html.twig', $args);
    }

    return $this->render('availability/dostupni.html.twig', $args);
  }

  #[Route('/unavailable/', name: 'app_availability_unavailable')]
  public function nedostupni(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];
    $dostupnosti = $this->em->getRepository(User::class)->getNedostupniPaginator();

    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('availability/phone/nedostupni.html.twig', $args);
    }

    return $this->render('availability/nedostupni.html.twig', $args);
  }

  #[Route('/delete/{id}', name: 'app_availability_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function delete(Availability $dostupnost): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $this->em->getRepository(Availability::class)->remove($dostupnost);

    return $this->redirectToRoute('app_availability_list');
  }

  #[Route('/make-available/{id}', name: 'app_availability_add')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function add(User $user): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $this->em->getRepository(Availability::class)->makeAvailable($user);

    return $this->redirectToRoute('app_availability_unavailable');
  }

  #[Route('/make-unavailable/{id}', name: 'app_availability_remove')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function remove(User $user): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $this->em->getRepository(Availability::class)->makeUnavailable($user);

    return $this->redirectToRoute('app_availability_available');
  }

  #[Route('/make-available-old/{id}', name: 'app_availability_old_add')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function addOld(Availability $availability, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $presek = new DateTimeImmutable('first day of July this year');
    $presek2 = new DateTimeImmutable('first day of January this year');

    $args = [];
    $form = $this->createForm(AvailabilityFormType::class, $availability, ['attr' => ['action' => $this->generateUrl('app_availability_old_add', ['id' => $availability->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        if ($availability->getType() == AvailabilityData::PRISUTAN) {
          $availability->setZahtev(null);
        }

        $this->em->getRepository(Availability::class)->save($availability);

        $dostupnosti = $this->em->getRepository(Availability::class)->findBy(['User' => $availability->getUser(), 'type' => AvailabilityData::NEDOSTUPAN]);
        if (!empty($dostupnosti)) {
          $vacation = $availability->getUser()->getVacation();

          $vacation->setVacation1(0);
          $vacation->setVacationk1(0);
          $vacation->setVacationd1(0);
          $vacation->setStopwatch1(0);
          $vacation->setOther1(0);

          $vacation->setVacation2(0);
          $vacation->setVacationk2(0);
          $vacation->setVacationd2(0);
          $vacation->setStopwatch2(0);
          $vacation->setOther2(0);

          $vacation->setUsed1(0);
          $vacation->setUsed2(0);
          $vacation->setSlava(0);

          foreach ($dostupnosti as $dostupnost) {

            if ($dostupnost->getDatum() < $presek && $dostupnost->getDatum() > $presek2 ) {
              if ($dostupnost->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
                $kolektivni = $vacation->getVacationk1();
                $used1 = $vacation->getUsed1();
                $vacation->setVacationk1($kolektivni + 1);
                $vacation->setUsed1($used1 + 1);
              } else {
                if (is_null($dostupnost->getZahtev()) && $dostupnost->getTypeDay() != TipNeradnihDanaData::NEDELJA) {
                  $merenje = $vacation->getStopwatch1();
                  $used1 = $vacation->getUsed1();
                  $vacation->setStopwatch1($merenje + 1);
                  $vacation->setUsed1($used1 + 1);
                }
                if ($dostupnost->getZahtev() == CalendarData::SLOBODAN_DAN) {
                  $dan = $vacation->getVacationd1();
                  $used1 = $vacation->getUsed1();
                  $vacation->setVacationd1($dan + 1);
                  $vacation->setUsed1($used1 + 1);
                }
                if ($dostupnost->getZahtev() == CalendarData::ODMOR) {
                  $odmor = $vacation->getVacation1();
                  $used1 = $vacation->getUsed1();
                  $vacation->setVacation1($odmor + 1);
                  $vacation->setUsed1($used1 + 1);
                }
                if ($dostupnost->getZahtev() == CalendarData::BOLOVANJE) {
                  $ostalo = $vacation->getOther1();
                  $used1 = $vacation->getUsed1();
                  $vacation->setOther1($ostalo + 1);
                  $vacation->setUsed1($used1 + 1);
                }
                if ($dostupnost->getZahtev() == CalendarData::SLAVA) {
                  $slava = $vacation->getSlava();
                  $used1 = $vacation->getUsed1();
                  $vacation->setSlava($slava + 1);
                  $vacation->setUsed1($used1 + 1);
                }
              }
            }

            if ($dostupnost->getDatum() > $presek ) {
              if ($dostupnost->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
                $kolektivni = $vacation->getVacationk2();
                $used2 = $vacation->getUsed2();
                $vacation->setVacationk2($kolektivni + 1);
                $vacation->setUsed2($used2 + 1);
              } else {
                if (is_null($dostupnost->getZahtev()) && $dostupnost->getTypeDay() != TipNeradnihDanaData::NEDELJA) {
                  $merenje = $vacation->getStopwatch2();
                  $used2 = $vacation->getUsed2();
                  $vacation->setStopwatch2($merenje + 1);
                  $vacation->setUsed2($used2 + 1);
                }
                if ($dostupnost->getZahtev() == CalendarData::SLOBODAN_DAN) {
                  $dan = $vacation->getVacationd2();
                  $used2 = $vacation->getUsed2();
                  $vacation->setVacationd2($dan + 1);
                  $vacation->setUsed2($used2 + 1);
                }
                if ($dostupnost->getZahtev() == CalendarData::ODMOR) {
                  $odmor = $vacation->getVacation2();
                  $used2 = $vacation->getUsed2();
                  $vacation->setVacation2($odmor + 1);
                  $vacation->setUsed2($used2 + 1);
                }
                if ($dostupnost->getZahtev() == CalendarData::BOLOVANJE) {
                  $ostalo = $vacation->getOther2();
                  $used2 = $vacation->getUsed2();
                  $vacation->setOther2($ostalo + 1);
                  $vacation->setUsed2($used2 + 1);
                }
                if ($dostupnost->getZahtev() == CalendarData::SLAVA) {
                  $slava = $vacation->getSlava();
                  $used2 = $vacation->getUsed2();
                  $vacation->setSlava($slava + 1);
                  $vacation->setUsed2($used2 + 1);
                }
              }
            }

          }
          $this->em->getRepository(Vacation::class)->save($vacation);
        }

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_employee_calendar_days', ['id' => $availability->getUser()->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['availability'] = $availability;

    return $this->render('availability/edit.html.twig', $args);
  }
}
