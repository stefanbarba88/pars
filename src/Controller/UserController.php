<?php

namespace App\Controller;

use App\Classes\Avatar;
use App\Classes\Data\UserRolesData;
use App\Classes\Downloader;
use App\Entity\User;
use App\Form\UserRegistrationFormType;
use App\Service\UploadService;
use Doctrine\Persistence\ManagerRegistry;
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

  #[Route('/list/{page}', name: 'app_users', defaults: ['page' => 1])]
  public function list(): Response {
    return $this->render('user/list.html.twig', [
      'controller_name' => 'UserController',
    ]);
  }

  #[Route('/form/{id}', name: 'app_user_form', defaults: ['id' => 0])]
  #[Entity('user', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, User $user, UploadService $uploadService): Response {
//    $sysUser = $this->getUser();
//
//    $userType = match ($sysUser->getUserType()) {
//      UserRolesData::ROLE_UPRAVNIK_CENTRALE => UserRolesData::ROLE_UPRAVNIK_GRANE,
//      UserRolesData::ROLE_UPRAVNIK_GRANE => UserRolesData::ROLE_POVERENIK,
//      UserRolesData::ROLE_SUPER_ADMIN => $request->query->getInt('user_type'),
//      default => 0,
//    };
//
//    $userTypeUrl = [];
//    if ($userType > 0) {
//      $d = UserRolesData::getRoleByType($userType);
//      $usr->setRole($d);
//      $usr->setUserType($userType);
//      $userTypeUrl = ['user_type' => $userType];
//    }

//    $form = $this->createForm(UserType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_form', ['id' => $usr->getId(), ] + $userTypeUrl)]]);
    $form = $this->createForm(UserRegistrationFormType::class, $user, ['attr' => ['action' => $this->generateUrl('app_user_form', ['id' => $user->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        dd($user);
        $file = $request->files->all()['user_registration_form']['slika'];

        if(is_null($file)) {
          $path = Avatar::getAvatar($user->getIme(), $user->getPrezime(), $this->getParameter('kernel.project_dir') . $user->getAvatarUploadPath());
        } else {
          $path = $uploadService->upload($file, $user->getImageUploadPath());
          $path = $path->getUrl();
        }

        $user->setSlika($path);

        $this->em->getRepository(User::class)->register($user);


        return $this->redirectToRoute('app_users');
      }
    }
    $args['form'] = $form->createView();

    return $this->render('user/registration_form.html.twig', $args);
  }
}
