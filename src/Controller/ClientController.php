<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Entity\Client;
use App\Entity\Image;
use App\Entity\User;
use App\Form\ClientFormType;
use App\Form\ClientSuspendedFormType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[Route('/clients')]
class ClientController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_clients')]
  public function list(): Response {
    $args=[];
    $args['clients'] = $this->em->getRepository(Client::class)->findBy([], ['isSuspended' => 'ASC']);

    return $this->render('client/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_client_form', defaults: ['id' => 0])]
  #[Entity('client', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Client $client): Response {
    $history = null;
    if($client->getId()) {
      $history = $this->json($client, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $form = $this->createForm(ClientFormType::class, $client, ['attr' => ['action' => $this->generateUrl('app_client_form', ['id' => $client->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Client::class)->save($client, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_clients');
      }
    }
    $args['form'] = $form->createView();
    $args['client'] = $client;
    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['client' => $client]);

    return $this->render('client/form.html.twig', $args);
  }

  #[Route('/view-profile/{id}', name: 'app_client_profile_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile(Client $client): Response {
    $args['client'] = $client;
    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['client' => $client]);

    return $this->render('client/view_profile.html.twig', $args);
  }

  #[Route('/view-activity/{id}', name: 'app_client_activity_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewActivity(Client $client): Response {
    $args['client'] = $client;
    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['client' => $client]);

    return $this->render('client/view_activity.html.twig', $args);
  }

  #[Route('/view-calendar/{id}', name: 'app_client_calendar_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewCalendar(Client $client): Response {
    $args['client'] = $client;
    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['client' => $client]);

    return $this->render('client/view_calendar.html.twig', $args);
  }

  #[Route('/view-cars/{id}', name: 'app_client_car_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewCar(Client $client): Response {
    $args['client'] = $client;
    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['client' => $client]);

    return $this->render('client/view_cars.html.twig', $args);
  }

  #[Route('/view-tools/{id}', name: 'app_client_tools_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewTools(Client $client): Response {
    $args['client'] = $client;
    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['client' => $client]);

    return $this->render('client/view_tools.html.twig', $args);
  }

  #[Route('/settings/{id}', name: 'app_client_settings_form')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function settings(Client $client, Request $request): Response {
    $client->setEditBy($this->getUser());
    $args['client'] = $client;
    $form = $this->createForm(ClientSuspendedFormType::class, $client, ['attr' => ['action' => $this->generateUrl('app_client_settings_form', ['id' => $client->getId()])]]);
    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Client::class)->suspend($client);

        if ($client->isSuspended()) {
          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addSuccess(NotifyMessagesData::USER_SUSPENDED_TRUE);
        } else {
          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addSuccess(NotifyMessagesData::USER_SUSPENDED_FALSE);
        }

        return $this->redirectToRoute('app_client_profile_view', ['id' => $client->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['image'] = $this->em->getRepository(Image::class)->findOneBy(['client' => $client]);


    return $this->render('client/settings.html.twig', $args);
  }

}
