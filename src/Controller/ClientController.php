<?php

namespace App\Controller;

use App\Classes\Avatar;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\DTO\UploadedFileDTO;
use App\Entity\Client;
use App\Entity\User;
use App\Form\ClientFormType;
use App\Form\UserRegistrationFormType;
use App\Service\UploadService;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/clients')]
class ClientController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_clients')]
  public function list(): Response {
    $args=[];
    $args['clients'] = $this->em->getRepository(Client::class)->findAll();

    return $this->render('client/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_client_form', defaults: ['id' => 0])]
  #[Entity('client', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Client $client, UploadService $uploadService): Response {
    $client->setEditBy($this->getUser());

    $form = $this->createForm(ClientFormType::class, $client, ['attr' => ['action' => $this->generateUrl('app_client_form', ['id' => $client->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $file = $request->files->all()['user_registration_form']['slika'];

        if(is_null($file)) {
          $file = new UploadedFileDTO($this->getParameter('kernel.project_dir') . $_ENV['USER_AVATAR_PATH'], $_ENV['USER_AVATAR_PATH'], 'default_avatar.png');
        } else {
          $file = $uploadService->upload($file, $client->getImageUploadPath());
        }

//        $this->em->getRepository(User::class)->save($client, $file, $this->getParameter('kernel.project_dir'));

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::REGISTRATION_USER_SUCCESS);

        return $this->redirectToRoute('app_users');
      }
    }
    $args['form'] = $form->createView();

    return $this->render('client/form.html.twig', $args);
  }

//  #[Route('/form/{id}', name: 'app_user_form', defaults: ['id' => 0])]
//  #[Entity('usr', expr: 'repository.findForForm(id)')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function form(Request $request, User $usr, UploadService $uploadService): Response {
//    $usr->setEditBy($this->getUser());
//
//    $form = $this->createForm(UserRegistrationFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_form', ['id' => $usr->getId()])]]);
//    if ($request->isMethod('POST')) {
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        $file = $request->files->all()['user_registration_form']['slika'];
//
//        if(is_null($file)) {
//          $file = Avatar::getAvatar($this->getParameter('kernel.project_dir') . $usr->getAvatarUploadPath(), $usr);
//        } else {
//          $file = $uploadService->upload($file, $usr->getImageUploadPath());
//        }
//
//        $this->em->getRepository(User::class)->register($usr, $file, $this->getParameter('kernel.project_dir'));
//
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::REGISTRATION_USER_SUCCESS);
//
//        return $this->redirectToRoute('app_users');
//      }
//    }
//    $args['form'] = $form->createView();
//
//    return $this->render('user/registration_form.html.twig', $args);
//  }

}
