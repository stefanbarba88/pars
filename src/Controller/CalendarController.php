<?php

namespace App\Controller;

use App\Classes\Data\CalendarData;
use App\Classes\Data\UserRolesData;
use App\Entity\Calendar;
use App\Entity\TaskLog;
use App\Form\CalendarFormType;
use App\Form\PhoneCalendarFormType;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Classes\Data\NotifyMessagesData;
use Symfony\Component\HttpFoundation\Request;

#[Route('/calendars')]
class CalendarController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_calendar_list')]
  public function list(): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $user = $this->getUser();

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {

      $args['calendars'] = $user->getCalendars();

    } else {

      $args['calendars'] = $this->em->getRepository(Calendar::class)->findAll();

    }

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('calendar/phone/list.html.twig', $args);
    }
    return $this->render('calendar/list.html.twig', $args);
  }


  #[Route('/form/{id}', name: 'app_calendar_form', defaults: ['id' => 0])]
  #[Entity('calendar', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Calendar $calendar): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $calendar->addUser($this->getUser());
    $mobileDetect = new MobileDetect();

    if($mobileDetect->isMobile()) {
      $form = $this->createForm(PhoneCalendarFormType::class, $calendar, ['attr' => ['action' => $this->generateUrl('app_calendar_form', ['id' => $calendar->getId()])]]);
    }
    $form = $this->createForm(CalendarFormType::class, $calendar, ['attr' => ['action' => $this->generateUrl('app_calendar_form', ['id' => $calendar->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Calendar::class)->save($calendar);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_calendar_list');
      }
    }
    $args['form'] = $form->createView();
    $args['calendar'] = $calendar;

    if($mobileDetect->isMobile()) {
      return $this->render('calendar/phone/form.html.twig', $args);
    }
    return $this->render('calendar/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_calendar_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Calendar $calendar): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['calendar'] = $calendar;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('calendar/phone/view.html.twig', $args);
    }
    return $this->render('calendar/view.html.twig', $args);
  }

  #[Route('/allow/{id}', name: 'app_calendar_allow')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function allow(Calendar $calendar): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $calendar->setStatus(2);
    $this->em->getRepository(Calendar::class)->save($calendar);

    return $this->redirectToRoute('app_calendar_list');
  }

  #[Route('/decline/{id}', name: 'app_calendar_decline')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function decline(Calendar $calendar): Response {
    $calendar->setStatus(3);
    $this->em->getRepository(Calendar::class)->save($calendar);

    return $this->redirectToRoute('app_calendar_list');
  }


  #[Route('/delete/{id}', name: 'app_calendar_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function delete(Calendar $calendar): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $calendar->setStatus(0);
    $this->em->getRepository(Calendar::class)->save($calendar);

    return $this->redirectToRoute('app_calendar_list');
  }

}