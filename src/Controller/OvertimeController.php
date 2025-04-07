<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Overtime;
use App\Entity\User;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/overtimes')]
class OvertimeController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_overtimes')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    $args = [];

    $holidays = $this->em->getRepository(Overtime::class)->getOvertimePaginator();

    $pagination = $paginator->paginate(
      $holidays, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('overtime/phone/list.html.twig', $args);
    }

    return $this->render('overtime/list.html.twig', $args);
  }

  #[Route('/archive/', name: 'app_overtime_archive')]
  public function archive(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    $args = [];

    $holidays = $this->em->getRepository(Overtime::class)->getOvertimeArchivePaginator();

    $pagination = $paginator->paginate(
      $holidays, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('overtime/phone/archive.html.twig', $args);
    }
    return $this->render('overtime/archive.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_overtime_form', defaults: ['id' => 0])]
  #[Entity('overtime', expr: 'repository.findForForm(id)')]
  public function form(Request $request, Overtime $overtime): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $overtime->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    if ($request->isMethod('POST')) {

      $user = $this->em->getRepository(User::class)->find($request->request->get('overtime_zaduzeni'));
      $sati = $request->request->get('overtime_vreme_sati');
      $minuti = $request->request->get('overtime_vreme_minuti');
      $datum = $request->request->get('overtime_datum');
      $napomena = $request->request->get('overtime_napomena');

      $overtime->setUser($user);
      $overtime->setHours($sati);
      $overtime->setMinutes($minuti);
      $overtime->setDatum(DateTimeImmutable::createFromFormat('d.m.Y', $datum)->setTime(0, 0));
      $overtime->setStatus(1);
      $overtime->setNote($napomena);


      $this->em->getRepository(Overtime::class)->save($overtime);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_overtime_archive');

    }

    $args['overtime'] = $overtime;
    $args['users'] = $this->em->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false, 'company' => $this->getUser()->getCompany()], ['prezime' => 'ASC']);

    return $this->render('overtime/form.html.twig', $args);
  }

  #[Route('/form-employee/{id}', name: 'app_overtime_employee_form', defaults: ['id' => 0])]
  #[Entity('overtime', expr: 'repository.findForForm(id)')]
  public function formEmployee(Request $request, Overtime $overtime): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getCompany() != $overtime->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    if ($request->isMethod('POST')) {

      $user = $this->em->getRepository(User::class)->find($request->request->get('overtime_zaduzeni'));
      $sati = $request->request->get('overtime_vreme_sati');
      $minuti = $request->request->get('overtime_vreme_minuti');
      $datum = $request->request->get('overtime_datum');
      $napomena = $request->request->get('overtime_napomena');

      $overtime->setUser($user);
      $overtime->setHours($sati);
      $overtime->setMinutes($minuti);
      $overtime->setDatum(DateTimeImmutable::createFromFormat('d.m.Y', $datum)->setTime(0, 0));
      $overtime->setStatus(0);
      $overtime->setNote($napomena);


      $this->em->getRepository(Overtime::class)->save($overtime);

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

      return $this->redirectToRoute('app_employee_calendar_view', (['id' => $user->getId()]));

    }

    $args['overtime'] = $overtime;
    $args['user'] = $this->getUser();

    return $this->render('overtime/form_employee.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_overtime_view')]
  public function view(Overtime $overtime): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $overtime->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['overtime'] = $overtime;

    return $this->render('overtime/view.html.twig', $args);
  }

  #[Route('/allow/{id}', name: 'app_overtime_allow')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function allow(Overtime $overtime): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $overtime->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $overtime->setStatus(1);
    $this->em->getRepository(Overtime::class)->save($overtime);

    return $this->redirectToRoute('app_overtimes');
  }

  #[Route('/decline/{id}', name: 'app_overtime_decline')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function decline(Overtime $overtime): Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isCalendar()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $overtime->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $overtime->setStatus(2);
    $this->em->getRepository(Overtime::class)->save($overtime);

    return $this->redirectToRoute('app_overtimes');
  }

}
