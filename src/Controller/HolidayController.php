<?php

namespace App\Controller;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Entity\Availability;
use App\Entity\Holiday;
use App\Form\HolidayFormType;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/holidays')]
class HolidayController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_holidays')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $year = date("Y");

    $holidays = $this->em->getRepository(Holiday::class)->getHolidaysPaginator($year);

    $pagination = $paginator->paginate(
      $holidays, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;

    return $this->render('holiday/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_holiday_form', defaults: ['id' => 0])]
  #[Entity('holiday', expr: 'repository.findForForm(id)')]
  public function form(Request $request, Holiday $holiday): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $form = $this->createForm(HolidayFormType::class, $holiday, ['attr' => ['action' => $this->generateUrl('app_holiday_form', ['id' => $holiday->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Holiday::class)->save($holiday);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_holidays');
      }
    }
    $args['form'] = $form->createView();
    $args['holiday'] = $holiday;

    return $this->render('holiday/form.html.twig', $args);
  }

  #[Route('/vacation/{id}', name: 'app_holiday_vacation_form', defaults: ['id' => 0])]
  public function vacation(Request $request): Response {

    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    if ($request->isMethod('POST')) {
      $start = $request->request->get('datum_start');
      $kraj = $request->request->get('datum_kraj');

      $startDatum = DateTimeImmutable::createFromFormat('d.m.Y', $start)->setTime(0, 0);
      $krajDatum = DateTimeImmutable::createFromFormat('d.m.Y', $kraj)->setTime(0, 0);

      $datumi = [];
      $current = clone $startDatum;

      while ($current <= $krajDatum) {
        $datumi[] = clone $current;
        $current = $current->add(new DateInterval('P1D'));
      }

      foreach ($datumi as $datum) {
        if ($datum->format('N') != 7) {
          $check = $this->em->getRepository(Holiday::class)->findBy( ['datum' => $datum]);
          if (empty($check)) {
            $odmor = new Holiday();
            $odmor->setDatum($datum);
            $odmor->setTitle('Kolektivni odmor');
            $odmor->setType(TipNeradnihDanaData::KOLEKTIVNI_ODMOR);
            $this->em->getRepository(Holiday::class)->save($odmor);
          }
        }
      }

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

      return $this->redirectToRoute('app_holidays');

    }

    return $this->render('holiday/vacation.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_holiday_view')]
  public function view(Holiday $holiday): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['holiday'] = $holiday;

    return $this->render('holiday/view.html.twig', $args);
  }

}
