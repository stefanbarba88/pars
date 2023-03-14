<?php

namespace App\Controller;

use App\Classes\Avatar;
use App\Classes\Data\UserRolesData;
use App\Classes\Downloader;
use App\Classes\Thumb;
use App\Classes\Thumbnail;
use App\Entity\Image;
use App\Entity\User;
use App\Form\UserEditInfoFormType;
use App\Form\UserRegistrationFormType;
use App\Service\UploadService;
use Doctrine\Persistence\ManagerRegistry;
use Imagick;
use Imagine\Imagick\Imagine;
use LasseRafn\Initials\Initials;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users')]
class UserController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_users')]
  public function list(): Response {
    $args=[];
    $args['users'] = $this->em->getRepository(User::class)->getAll();

    return $this->render('user/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_user_form', defaults: ['id' => 0])]
  #[Entity('usr', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, User $usr, UploadService $uploadService): Response {

    $form = $this->createForm(UserRegistrationFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_form', ['id' => $usr->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $file = $request->files->all()['user_registration_form']['slika'];

        if(is_null($file)) {
          $file = Avatar::getAvatar($this->getParameter('kernel.project_dir') . $usr->getAvatarUploadPath(), $usr);
        } else {
          $file = $uploadService->upload($file, $usr->getImageUploadPath());
        }

        $this->em->getRepository(User::class)->register($usr, $file, $this->getParameter('kernel.project_dir'));

        sweetalert()
          ->toast()
          ->option('position', 'top-right')
          ->option('timeout', 15000)
          ->addFlash('info', 'Your password has been reset and a new one has been sent to your email.');

        return $this->redirectToRoute('app_users');
      }
    }
    $args['form'] = $form->createView();

    return $this->render('user/registration_form.html.twig', $args);
  }

  #[Route('/edit/{id}', name: 'app_user_edit_info_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editAccount(User $usr, Request $request, UploadService $uploadService): Response {

    $form = $this->createForm(UserEditInfoFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_edit_info_form', ['id' => $usr->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

//        $file = $request->files->all()['user_registration_form']['slika'];
//
//        if(is_null($file)) {
//          $file = Avatar::getAvatar($this->getParameter('kernel.project_dir') . $usr->getAvatarUploadPath(), $usr);
//        } else {
//          $file = $uploadService->upload($file, $usr->getImageUploadPath());
//        }
//
//        $this->em->getRepository(User::class)->register($usr, $file, $this->getParameter('kernel.project_dir'));

        return $this->redirectToRoute('app_user_view', ['id' => $usr->getId()]);
      }
    }
    $args['form'] = $form->createView();

    return $this->render('user/edit_account.html.twig', $args);
  }


  #[Route('/view/{id}', name: 'app_user_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(User $usr): Response {
    $args['user'] = $usr;

    return $this->render('user/view.html.twig', $args);
  }
}
