<?php

namespace App\Controller;

use App\Classes\Data\UserRolesData;
use App\Entity\GranskiSindikat;
use App\Entity\User;
use App\Form\UserRegistrationFormType;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users')]
class UserController extends AbstractController {
  #[Route('/list/{page}', name: 'app_users', defaults: ['page' => 1])]
  public function list(): Response {
    return $this->render('user/list.html.twig', [
      'controller_name' => 'UserController',
    ]);
  }

  #[Route('/form/{id}', name: 'app_user_form', defaults: ['id' => 0])]
  #[Entity('usr', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, User $usr): Response {
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
    $form = $this->createForm(UserRegistrationFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_form', ['id' => $usr->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        dd($request);
        $request->request->all('delegat');
        dd($form);
//        if (!is_null($sysUser->getGrana())) {
//          $grana = $sysUser->getGrana();
//        } else {
//          $grana = $this->em->getRepository(GranskiSindikat::class)->find(GranskiSindikat::PARENT);
//        }
//        $this->em->getRepository(User::class)->save($usr, $grana);

        return $this->redirectToRoute('app_users');
      }
    }
    $args['form'] = $form->createView();

    return $this->render('user/registration_form.html.twig', $args);
  }
}
