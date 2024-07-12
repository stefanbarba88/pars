<?php

namespace App\Controller\Kadrovska;

use App\Classes\Avatar;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Image;
use App\Entity\User;
use App\Form\Kadrovska\UserKadrovskaEditAccountFormType;
use App\Form\Kadrovska\UserKadrovskaEditInfoFormType;
use App\Form\Kadrovska\UserKadrovskaRegistrationFormType;
use App\Form\UserEditImageFormType;
use App\Form\UserSuspendedFormType;
use App\Service\UploadService;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/kadrovska/user')]
class KadrovskaUserController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_kadrovska_users')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }

    $args = [];

    $search = [];

    $search['ime'] = $request->query->get('ime');
    $search['prezime'] = $request->query->get('prezime');
    $search['pozicija'] = $request->query->get('pozicija');
    $search['vrsta'] = $request->query->get('vrsta');

    $users = $this->em->getRepository(User::class)->getAllByLoggedUserPaginator($korisnik, $search, 0);

    $pagination = $paginator->paginate(
      $users, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['vrste'] = UserRolesData::DATA_KADROVSKA;

//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      return $this->render('_kadrovska/user/phone/list.html.twig', $args);
//    }

    return $this->render('_kadrovska/user/list.html.twig', $args);
  }

  #[Route('/archive/', name: 'app_kadrovska_users_archive')]
  public function archive(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }

    $args = [];

    $search = [];

    $search['ime'] = $request->query->get('ime');
    $search['prezime'] = $request->query->get('prezime');
    $search['pozicija'] = $request->query->get('pozicija');
    $search['vrsta'] = $request->query->get('vrsta');

    $users = $this->em->getRepository(User::class)->getAllByLoggedUserPaginator($korisnik, $search, 1);

    $pagination = $paginator->paginate(
      $users, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['vrste'] = UserRolesData::DATA_KADROVSKA;

//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      return $this->render('_kadrovska/user/phone/list_archive.html.twig', $args);
//    }

    return $this->render('_kadrovska/user/list_archive.html.twig', $args);
  }

//  #[Route('/list-employees/', name: 'app_kadrovska_employees')]
//  public function listEmployees(PaginatorInterface $paginator, Request $request)    : Response {
//    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->isKadrovska()) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $korisnik = $this->getUser();
//    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
//      return $this->redirect($this->generateUrl('app_kadrovska_home'));
//    }
//
//    $args = [];
//
//    $search = [];
//
//    $search['ime'] = $request->query->get('ime');
//    $search['prezime'] = $request->query->get('prezime');
//    $search['pozicija'] = $request->query->get('pozicija');
//    $search['vrsta'] = $request->query->get('vrsta');
//
//    $users = $this->em->getRepository(User::class)->getAllByLoggedUserPaginator($korisnik, $search, 0);
//
//    $pagination = $paginator->paginate(
//      $users, /* query NOT result */
//      $request->query->getInt('page', 1), /*page number*/
//      15
//    );
//
//    $session = new Session();
//    $session->set('url', $request->getRequestUri());
//
//    $args['pagination'] = $pagination;
//    $args['vrste'] = UserRolesData::DATA;
//
//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      return $this->render('user/phone/list.html.twig', $args);
//    }
//
//    return $this->render('user/list.html.twig', $args);
//  }

  #[Route('/form/{id}', name: 'app_kadrovska_user_form', defaults: ['id' => 0])]
  #[Entity('usr', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, User $usr, UploadService $uploadService)    : Response {
    if (!$this->isGranted('ROLE_USER')|| !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      return $this->redirect($this->generateUrl('app_kadrovska_home'));
    }

    $usr->setPlainUserType($this->getUser()->getUserType());
    $type = $request->query->getInt('type');
    $form = $this->createForm(UserKadrovskaRegistrationFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_kadrovska_user_form', ['id' => $usr->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $file = $request->files->all()['user_kadrovska_registration_form']['slika'];

        if(is_null($file)) {
          $file = Avatar::getAvatar($this->getParameter('kernel.project_dir') . $usr->getAvatarUploadPath(), $usr);
        } else {
          $file = $uploadService->upload($file, $usr->getImageUploadPath());
        }

        $this->em->getRepository(User::class)->registerNew($usr, $korisnik->getCompany()->getSettings(), $file, $this->getParameter('kernel.project_dir'));

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::REGISTRATION_USER_SUCCESS);

        return $this->redirectToRoute('app_kadrovska_users');

      }
    }
    $args['form'] = $form->createView();

    return $this->render('_kadrovska/user/registration_form.html.twig', $args);
  }

  #[Route('/view-profile/{id}', name: 'app_kadrovska_user_profile_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile(User $usr)    : Response {
    if (!$this->isGranted('ROLE_USER')|| !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $args['user'] = $usr;

    return $this->render('_kadrovska/user/view_profile.html.twig', $args);
  }

  #[Route('/edit-info/{id}', name: 'app_kadrovska_user_edit_info_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editInfo(User $usr, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')|| !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }
    $type = $request->query->getInt('type');

    $form = $this->createForm(UserKadrovskaEditInfoFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_kadrovska_user_edit_info_form', ['id' => $usr->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(User::class)->save($usr);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_SUCCESS);

        return $this->redirectToRoute('app_kadrovska_user_profile_view', ['id' => $usr->getId()]);

      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $usr;
    $args['type'] = $type;

    return $this->render('_kadrovska/user/edit_info.html.twig', $args);

  }

  #[Route('/edit-account/{id}', name: 'app_kadrovska_user_edit_account_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editAccount(User $usr, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')|| !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }

    $type = $request->query->getInt('type');
    $usr->setPlainUserType($this->getUser()->getUserType());


    $form = $this->createForm(UserKadrovskaEditAccountFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_kadrovska_user_edit_account_form', ['id' => $usr->getId(), 'type' => $type])]]);

    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        $this->em->getRepository(User::class)->save($usr);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_SUCCESS);

        return $this->redirectToRoute('app_kadrovska_user_profile_view', ['id' => $usr->getId()]);

      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $usr;
    $args['type'] = $type;

    return $this->render('_kadrovska/user/edit_account.html.twig', $args);

  }

  #[Route('/edit-image/{id}', name: 'app_kadrovska_user_edit_image_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function editImage(User $usr, Request $request, UploadService $uploadService)    : Response {
    if (!$this->isGranted('ROLE_USER')|| !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }
    $type = $request->query->getInt('type');

    $usr->setEditBy($this->getUser());

    $form = $this->createForm(UserEditImageFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_kadrovska_user_edit_image_form', ['id' => $usr->getId(), 'type' => $type])]]);
    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        $file = $request->files->all()['user_edit_image_form']['slika'];
        $file = $uploadService->upload($file, $usr->getImageUploadPath());

        $image = $this->em->getRepository(Image::class)->addImage($file, $usr->getThumbUploadPath(), $this->getParameter('kernel.project_dir'));
        $usr->setImage($image);

        $this->em->getRepository(User::class)->save($usr);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_USER_IMAGE_SUCCESS);


        return $this->redirectToRoute('app_kadrovska_user_profile_view', ['id' => $usr->getId()]);

      }
    }
    $args['form'] = $form->createView();
    $args['user'] = $usr;
    $args['type'] = $type;

    return $this->render('_kadrovska/user/edit_image.html.twig', $args);

  }

  #[Route('/settings/{id}', name: 'app_kadrovska_user_settings_form')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function settings(User $usr, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')|| !$this->getUser()->isKadrovska()) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if ($korisnik->getId() != $usr->getId()) {
        return $this->redirect($this->generateUrl('app_kadrovska_home'));
      }
    }
    $args['user'] = $usr;

    $form = $this->createForm(UserSuspendedFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_kadrovska_user_settings_form', ['id' => $usr->getId()])]]);
    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(User::class)->suspend($usr);

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

        return $this->redirectToRoute('app_kadrovska_user_profile_view', ['id' => $usr->getId()]);
      }
    }

    $args['form'] = $form->createView();
    $args['user'] = $usr;
    return $this->render('_kadrovska/user/settings.html.twig', $args);
  }

//  #[Route('/history-user-list/{id}', name: 'app_kadrovska_user_history_list')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function listUserHistory(User $user, PaginatorInterface $paginator, Request $request)    : Response {
//    if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $korisnik = $this->getUser();
//    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
//      if ($korisnik->getId() != $user->getId()) {
//        return $this->redirect($this->generateUrl('app_home'));
//      }
//
//    }
//    if ($korisnik->getCompany() != $user->getCompany()) {
//      return $this->redirect($this->generateUrl('app_home'));
//    }
//    $args=[];
//    $histories = $this->em->getRepository(UserHistory::class)->getAllPaginator($user);
//
//    $pagination = $paginator->paginate(
//      $histories, /* query NOT result */
//      $request->query->getInt('page', 1), /*page number*/
//      15
//    );
//
//    $args['pagination'] = $pagination;
//    $args['user'] = $user;
//
//    $mobileDetect = new MobileDetect();
//    if($mobileDetect->isMobile()) {
//      return $this->render('user/phone/user_history_list.html.twig', $args);
//    }
//
//    return $this->render('user/user_history_list.html.twig', $args);
//  }
//
//  #[Route('/history-user-view/{id}', name: 'app_kadrovska_user_profile_history_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewUserHistory(UserHistory $userHistory, SerializerInterface $serializer)    : Response {
//    if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $korisnik = $this->getUser();
//    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
//      return $this->redirect($this->generateUrl('app_home'));
//
//    }
//    $args['userH'] = $serializer->deserialize($userHistory->getHistory(), User::class, 'json');
//    $args['userHistory'] = $userHistory;
//
//    return $this->render('user/view_history_profile.html.twig', $args);
//  }

}
