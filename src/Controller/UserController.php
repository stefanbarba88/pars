<?php

namespace App\Controller;

use App\Classes\Avatar;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Image;
use App\Entity\User;
use App\Entity\UserHistory;
use App\Form\UserEditImageFormType;
use App\Form\UserEditInfoFormType;
use App\Form\UserEditAccountFormType;
use App\Form\UserEditSelfAccountFormType;
use App\Form\UserRegistrationFormType;
use App\Form\UserSuspendedFormType;
use App\Service\UploadService;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/users')]
class UserController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_users')]
  public function list()    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args=[];
    $args['users'] = $this->em->getRepository(User::class)->getAllByLoggedUser($korisnik);

    return $this->render('user/list.html.twig', $args);
  }

  #[Route('/list-contact/', name: 'app_users_contact')]
  public function listContacts()    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args=[];
    $args['users'] = $this->em->getRepository(User::class)->getAllContacts();

    return $this->render('user/contact_list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_user_form', defaults: ['id' => 0])]
  #[Entity('usr', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, User $usr, UploadService $uploadService)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $usr->setPlainUserType($this->getUser()->getUserType());
    $type = $request->query->getInt('type');
    $form = $this->createForm(UserRegistrationFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_form', ['id' => $usr->getId(), 'type' => $type])]]);
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

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::REGISTRATION_USER_SUCCESS);

        if ($type != 1) {
          return $this->redirectToRoute('app_users');
        }
        return $this->redirectToRoute('app_employees');

      }
    }
    $args['form'] = $form->createView();

    return $this->render('user/registration_form.html.twig', $args);
  }

  #[Route('/edit-info/{id}', name: 'app_user_edit_info_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editInfo(User $usr, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    $type = $request->query->getInt('type');

    $history = null;
    if($usr->getId()) {
      $history = $this->json($usr, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    $form = $this->createForm(UserEditInfoFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_edit_info_form', ['id' => $usr->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(User::class)->save($usr, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_SUCCESS);

        if ($type != 1) {
          return $this->redirectToRoute('app_user_profile_view', ['id' => $usr->getId()]);
        }
        return $this->redirectToRoute('app_employee_profile_view', ['id' => $usr->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $usr;
    $args['type'] = $type;

    if ($type != 1) {
      return $this->render('user/edit_info.html.twig', $args);
    }
      $mobileDetect = new MobileDetect();
      if($mobileDetect->isMobile()) {
        return $this->render('employee/phone/edit_info.html.twig', $args);
      }
    return $this->render('employee/edit_info.html.twig', $args);
  }

  #[Route('/edit-account/{id}', name: 'app_user_edit_account_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editAccount(User $usr, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }

    $type = $request->query->getInt('type');
    $usr->setPlainUserType($this->getUser()->getUserType());
    $history = null;
    if($usr->getId()) {
      $history = $this->json($usr, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }

    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      $form = $this->createForm(UserEditSelfAccountFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_edit_account_form', ['id' => $usr->getId(), 'type' => $type])]]);
    } else {
      $form = $this->createForm(UserEditAccountFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_edit_account_form', ['id' => $usr->getId(), 'type' => $type])]]);
    }

    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(User::class)->save($usr, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_SUCCESS);

        if ($type != 1) {
          return $this->redirectToRoute('app_user_profile_view', ['id' => $usr->getId()]);
        }
        return $this->redirectToRoute('app_employee_profile_view', ['id' => $usr->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $usr;
    $args['type'] = $type;

    if ($type != 1) {
      return $this->render('user/edit_account.html.twig', $args);
    } else {
      if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
        $mobileDetect = new MobileDetect();
        if($mobileDetect->isMobile()) {
          return $this->render('employee/phone/edit_account.html.twig', $args);
        }
        return $this->render('employee/edit_account.html.twig', $args);
      } else {

        return $this->render('employee/manager_edit_account.html.twig', $args);
      }

    }



  }

  #[Route('/edit-image/{id}', name: 'app_user_edit_image_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editImage(User $usr, Request $request, UploadService $uploadService)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    $type = $request->query->getInt('type');

    $usr->setEditBy($this->getUser());

    $form = $this->createForm(UserEditImageFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_edit_image_form', ['id' => $usr->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $history = null;
      if($usr->getId()) {
        $history = $this->json($usr, Response::HTTP_OK, [], [
            ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
              return $object->getId();
            }
          ]
        );
        $history = $history->getContent();
      }

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $file = $request->files->all()['user_edit_image_form']['slika'];
        $file = $uploadService->upload($file, $usr->getImageUploadPath());

        $image = $this->em->getRepository(Image::class)->addImage($file, $usr->getThumbUploadPath(), $this->getParameter('kernel.project_dir'));
        $usr->setImage($image);

        $this->em->getRepository(User::class)->save($usr, $history);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_IMAGE_SUCCESS);

        if ($type != 1) {
          return $this->redirectToRoute('app_user_profile_view', ['id' => $usr->getId()]);
        }

        return $this->redirectToRoute('app_employee_profile_view', ['id' => $usr->getId()]);
      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $usr;
    $args['type'] = $type;

    if ($type != 1) {
      return $this->render('user/edit_image.html.twig', $args);
    }
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('employee/phone/edit_image.html.twig', $args);
    }
    return $this->render('employee/edit_image.html.twig', $args);
  }

  #[Route('/view-profile/{id}', name: 'app_user_profile_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile(User $usr)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    $args['user'] = $usr;

    return $this->render('user/view_profile.html.twig', $args);
  }

  #[Route('/settings/{id}', name: 'app_user_settings_form')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function settings(User $usr, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }

    $history = null;
    if($usr->getId()) {
      $history = $this->json($usr, Response::HTTP_OK, [], [
          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
          }
        ]
      );
      $history = $history->getContent();
    }
    $args['user'] = $usr;

    $form = $this->createForm(UserSuspendedFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_user_settings_form', ['id' => $usr->getId()])]]);
    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(User::class)->suspend($usr, $history);

          if ($usr->isSuspended()) {
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

        return $this->redirectToRoute('app_user_profile_view', ['id' => $usr->getId()]);
      }
    }

    $args['form'] = $form->createView();
    $args['user'] = $usr;
    return $this->render('user/settings.html.twig', $args);
  }

  #[Route('/history-user-list/{id}', name: 'app_user_history_list')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function listUserHistory(User $user)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($korisnik->getId() != $user->getId()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    $args['user'] = $user;
    $args['historyUsers'] = $this->em->getRepository(UserHistory::class)->findBy(['user' => $user], ['id' => 'ASC']);

    return $this->render('user/user_history_list.html.twig', $args);
  }

  #[Route('/history-user-view/{id}', name: 'app_user_profile_history_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewUserHistory(UserHistory $userHistory, SerializerInterface $serializer)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['userH'] = $serializer->deserialize($userHistory->getHistory(), user::class, 'json');
    $args['userHistory'] = $userHistory;

    return $this->render('user/view_history_profile.html.twig', $args);
  }
}
