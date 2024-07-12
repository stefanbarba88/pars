<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\Vacation;
use App\Form\VacationFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vacation')]
class VacationController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/form/{id}', name: 'app_vacation_form')]
  public function form(Vacation $vacation, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();
    if ($user->getCompany() != $vacation->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    $form = $this->createForm(VacationFormType::class, $vacation, ['attr' => ['action' => $this->generateUrl('app_vacation_form', ['id' => $vacation->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

//        $test1 = $serializer->deserialize($test->getContent(), Project::class, 'json');

        $this->em->getRepository(vacation::class)->save($vacation);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_employee_calendar_view', ['id' => $vacation->getUser()->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['vacation'] = $vacation;

    return $this->render('vacation/form.html.twig', $args);
  }
}
