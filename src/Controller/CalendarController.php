<?php

namespace App\Controller;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\UserRolesData;
use App\Entity\Availability;
use App\Entity\Calendar;
use App\Entity\Holiday;
use App\Entity\User;
use App\Form\CalendarFormType;
use App\Form\PhoneCalendarFormType;
use App\Service\MailService;
use DateInterval;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
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
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $user = $this->getUser();

    $calendars = $this->em->getRepository(Calendar::class)->getCalendarPaginator($user);

    $pagination = $paginator->paginate(
      $calendars, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('calendar/phone/list.html.twig', $args);
    }

    return $this->render('calendar/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_calendar_form', defaults: ['id' => 0])]
  #[Entity('calendar', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Calendar $calendar, MailService $mailService): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    $company = $calendar->getCompany();
    $args['disabledDates'] = [];

    if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      $calendar->addUser($korisnik);
//      $args['disabledDates'] = $this->em->getRepository(Calendar::class)->getDisabledDates($korisnik);
    } else {
      if (!is_null($request->get('user'))) {
        $koris = $this->em->getRepository(User::class)->find($request->get('user'));
        $calendar->addUser($koris);
//        $args['disabledDates'] = $this->em->getRepository(Calendar::class)->getDisabledDates($koris);
      }
    }

    $mobileDetect = new MobileDetect();

    if($mobileDetect->isMobile()) {

      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        $form = $this->createForm(CalendarFormType::class, $calendar, ['attr' => ['action' => $this->generateUrl('app_calendar_form', ['id' => $calendar->getId()])]]);
      } else {
        $form = $this->createForm(PhoneCalendarFormType::class, $calendar, ['attr' => ['action' => $this->generateUrl('app_calendar_form', ['id' => $calendar->getId()])]]);
      }
    } else {
      $form = $this->createForm(CalendarFormType::class, $calendar, ['attr' => ['action' => $this->generateUrl('app_calendar_form', ['id' => $calendar->getId()])]]);
    }

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        if ($calendar->getStart() > $calendar->getFinish()) {

          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addError(NotifyMessagesData::EDIT_ITEM_ERROR);

          return $this->redirectToRoute('app_home');

        }

        if ($calendar->getUser()->isEmpty()) {
          $data = $request->request->all();
          $user = $this->em->getRepository(User::class)->find($data['form']['zaposleni']);
          $calendar->addUser($user);
        }
        $this->em->getRepository(Calendar::class)->save($calendar);

        $korisnik = $calendar->getUser()->first();

        $mailService->calendar($calendar, $company->getEmail());

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        //ako je bolovanje automatski se odobrava

        if ($calendar->getType() == 3) {

            $calendar->setStatus(2);

            $start = $calendar->getStart();
            $finish = $calendar->getFinish();
            $datumi = [];
            $current = clone $start;

            while ($current <= $finish) {
              $datumi[] = clone $current;
              $current = $current->add(new DateInterval('P1D'));
            }

            foreach ($datumi as $datum) {
              if ($datum->format('N') < $company->getSettings()->getWorkWeek()) {
                $check = $this->em->getRepository(Availability::class)->findBy(['User' => $calendar->getUser()->first(), 'datum' => $datum]);
                if (empty($check)) {
                  $checkPraznik = $this->em->getRepository(Holiday::class)->findBy(['company' => $company, 'datum' => $datum]);
                  if (empty($checkPraznik)) {
                    $dostupnost = new Availability();
                    $dostupnost->setDatum($datum);
                    $dostupnost->setUser($calendar->getUser()->first());
                    $dostupnost->setType(AvailabilityData::NEDOSTUPAN);
                    $dostupnost->setZahtev($calendar->getType());
                    $dostupnost->setCalendar($calendar->getId());
                    $dostupnost->setCompany($calendar->getCompany());
                    $this->em->getRepository(Availability::class)->save($dostupnost);
                  }
                }
              }
            }

          $mailService->responseCalendar($calendar);

          $this->em->getRepository(Calendar::class)->save($calendar);

        }

        return $this->redirectToRoute('app_employee_calendar_view', ['id' => $korisnik->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['calendar'] = $calendar;
    $args['users'] =  $this->em->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false, 'company' => $korisnik->getCompany()],['isSuspended' => 'ASC', 'prezime' => 'ASC']);



    if($mobileDetect->isMobile()) {
      if($korisnik->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('calendar/form.html.twig', $args);
      }
      return $this->render('calendar/phone/form.html.twig', $args);
    }
    return $this->render('calendar/form.html.twig', $args);
  }

  #[Route('/form-admin/{id}', name: 'app_calendar_admin_form', defaults: ['id' => 0])]
  #[Entity('calendar', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formAdmin(Request $request, Calendar $calendar, MailService $mailService): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $company = $calendar->getCompany();
    $form = $this->createForm(CalendarFormType::class, $calendar, ['attr' => ['action' => $this->generateUrl('app_calendar_admin_form', ['id' => $calendar->getId()])]]);


    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        if ($calendar->getStart() > $calendar->getFinish()) {

          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addError(NotifyMessagesData::EDIT_ITEM_ERROR);

          return $this->redirectToRoute('app_calendar_list');

        }

        if ($calendar->getUser()->isEmpty()) {
          $data = $request->request->all();
          $user = $this->em->getRepository(User::class)->find($data['form']['zaposleni']);
          $calendar->addUser($user);
        }
        $this->em->getRepository(Calendar::class)->save($calendar);
        $mailService->calendar($calendar, $company->getEmail());


        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);


        //ako kreira admin automatski se odobrava

          $calendar->setStatus(2);

          $start = $calendar->getStart();
          $finish = $calendar->getFinish();
          $datumi = [];
          $current = clone $start;

          while ($current <= $finish) {
            $datumi[] = clone $current;
            $current = $current->add(new DateInterval('P1D'));
          }

        foreach ($datumi as $datum) {
          if ($datum->format('N') < $company->getSettings()->getWorkWeek()) {
            $check = $this->em->getRepository(Availability::class)->findBy(['User' => $calendar->getUser()->first(), 'datum' => $datum]);
            if (empty($check)) {
              $checkPraznik = $this->em->getRepository(Holiday::class)->findBy(['company' => $company, 'datum' => $datum]);
              if (empty($checkPraznik)) {
                $dostupnost = new Availability();
                $dostupnost->setDatum($datum);
                $dostupnost->setUser($calendar->getUser()->first());
                $dostupnost->setType(AvailabilityData::NEDOSTUPAN);
                $dostupnost->setZahtev($calendar->getType());
                $dostupnost->setCalendar($calendar->getId());
                $dostupnost->setCompany($calendar->getCompany());
                $this->em->getRepository(Availability::class)->save($dostupnost);
              }
            }
          }
        }

          $mailService->responseCalendar($calendar);

          $this->em->getRepository(Calendar::class)->save($calendar);

        return $this->redirectToRoute('app_calendar_list');
      }
    }

    $args['form'] = $form->createView();
    $args['calendar'] = $calendar;
    $args['users'] =  $this->em->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false, 'company' => $company],['isSuspended' => 'ASC', 'prezime' => 'ASC']);

    return $this->render('calendar/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_calendar_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Calendar $calendar): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
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
  public function allow(Calendar $calendar, MailService $mailService): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $calendar->setStatus(2);
    $company = $calendar->getCompany();

    $start = $calendar->getStart();
    $finish = $calendar->getFinish();
      $datumi = [];
      $current = clone $start;

      while ($current <= $finish) {
        $datumi[] = clone $current;
        $current = $current->add(new DateInterval('P1D'));
      }

    foreach ($datumi as $datum) {
      if ($datum->format('N') < $company->getSettings()->getWorkWeek()) {
        $check = $this->em->getRepository(Availability::class)->findBy(['User' => $calendar->getUser()->first(), 'datum' => $datum]);
        if (empty($check)) {
          $checkPraznik = $this->em->getRepository(Holiday::class)->findBy(['company' => $company, 'datum' => $datum]);
          if (empty($checkPraznik)) {
            $dostupnost = new Availability();
            $dostupnost->setDatum($datum);
            $dostupnost->setUser($calendar->getUser()->first());
            $dostupnost->setType(AvailabilityData::NEDOSTUPAN);
            $dostupnost->setZahtev($calendar->getType());
            $dostupnost->setCalendar($calendar->getId());
            $dostupnost->setCompany($calendar->getCompany());
            $this->em->getRepository(Availability::class)->save($dostupnost);
          }
        }
      }
    }

    $mailService->responseCalendar($calendar);

    $this->em->getRepository(Calendar::class)->save($calendar);

    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      return $this->redirectToRoute('app_employee_calendar_view', ['id' => $this->getUser()->getId()]);
    }

    return $this->redirectToRoute('app_calendar_list');
  }

  #[Route('/decline/{id}', name: 'app_calendar_decline')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function decline(Calendar $calendar, MailService $mailService): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $calendar->setStatus(3);
    $mailService->responseCalendar($calendar);
    $this->em->getRepository(Calendar::class)->save($calendar);

    $dostupnosti = $this->em->getRepository(Availability::class)->findBy(['calendar' => $calendar->getId()]);

    if (!empty($dostupnosti) ) {
      foreach ($dostupnosti as $dostupnost) {
        $this->em->getRepository(Availability::class)->remove($dostupnost);
      }
    }
    return $this->redirectToRoute('app_calendar_list');
  }

  #[Route('/delete/{id}', name: 'app_calendar_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function delete(Calendar $calendar, MailService $mailService): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $calendar->setStatus(0);
    $mailService->responseCalendar($calendar);
    $this->em->getRepository(Calendar::class)->save($calendar);
    $dostupnosti = $this->em->getRepository(Availability::class)->findBy(['calendar' => $calendar->getId()]);
    if (!empty($dostupnosti) ) {
      foreach ($dostupnosti as $dostupnost) {
        $this->em->getRepository(Availability::class)->remove($dostupnost);
      }
    }

    return $this->redirectToRoute('app_calendar_list');
  }

}