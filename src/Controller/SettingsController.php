<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\Settings;
use App\Form\SettingsFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/settings')]
class SettingsController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }
  #[Route('/form/{id}', name: 'app_settings_form')]
  public function form(Settings $settings, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();
    if ($user->getCompany() != $settings->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    $form = $this->createForm(SettingsFormType::class, $settings, ['attr' => ['action' => $this->generateUrl('app_settings_form', ['id' => $settings->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

//        $test1 = $serializer->deserialize($test->getContent(), Project::class, 'json');

        $this->em->getRepository(Settings::class)->save($settings);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_settings_form', ['id' => $settings->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['settings'] = $settings;

    return $this->render('settings/form.html.twig', $args);
  }
}
