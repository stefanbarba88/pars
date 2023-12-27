<?php

namespace App\Controller;

use App\Classes\Avatar;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Company;
use App\Entity\Image;
use App\Entity\User;
use App\Form\CompanyFormType;
use App\Service\UploadService;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/companies')]
class CompanyController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_companies')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args=[];
    $companies = $this->em->getRepository(Company::class)->getAllCompaniesPaginator();

    $pagination = $paginator->paginate(
      $companies, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      20
    );

    $args['pagination'] = $pagination;

    return $this->render('company/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_company_form', defaults: ['id' => 0])]
  #[Entity('company', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Company $company, UploadService $uploadService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    $form = $this->createForm(CompanyFormType::class, $company, ['attr' => ['action' => $this->generateUrl('app_company_form', ['id' => $company->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $file = $request->files->all()['company_form']['image'];

        if(!is_null($file)) {
//          $file = Avatar::getAvatar($this->getParameter('kernel.project_dir') . $usr->getAvatarUploadPath(), $usr);
          $file = $uploadService->upload($file, $company->getImageUploadPath());
          $image = $this->em->getRepository(Image::class)->addImage($file, $company->getThumbUploadPath(), $this->getParameter('kernel.project_dir'));
          $company->setImage($image);
        } else {
          $company->setImage($this->em->getRepository(Image::class)->find(2));
        }

        $this->em->getRepository(Company::class)->save($company);


        return $this->redirectToRoute('app_companies');
      }
    }
    $args['form'] = $form->createView();
    $args['company'] = $company;

    return $this->render('company/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_company_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile(Company $company)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['company'] = $company;

    return $this->render('company/view.html.twig', $args);
  }

//  #[Route('/view-activity/{id}', name: 'app_client_activity_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewActivity(Client $client)    : Response { if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $args['client'] = $client;
//
//    return $this->render('client/view_activity.html.twig', $args);
//  }
//
//  #[Route('/view-calendar/{id}', name: 'app_client_calendar_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewCalendar(Client $client)    : Response { if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $args['client'] = $client;
//
//    return $this->render('client/view_calendar.html.twig', $args);
//  }

//  #[Route('/view-cars/{id}', name: 'app_client_car_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewCar(Client $client)    : Response { if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $args['client'] = $client;
//
//    return $this->render('client/view_cars.html.twig', $args);
//  }
//
//  #[Route('/view-tools/{id}', name: 'app_client_tools_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewTools(Client $client)    : Response { if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//    $args['client'] = $client;
//
//    return $this->render('client/view_tools.html.twig', $args);
//  }
//
//  #[Route('/settings/{id}', name: 'app_client_settings_form')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function settings(Client $client, Request $request)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//    $history = null;
//    if($client->getId()) {
//      $history = $this->json($client, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }
//    $args['client'] = $client;
//    $form = $this->createForm(ClientSuspendedFormType::class, $client, ['attr' => ['action' => $this->generateUrl('app_client_settings_form', ['id' => $client->getId()])]]);
//    if ($request->isMethod('POST')) {
//
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//
//        $users = $client->getContact();
//        if (!empty ($users)) {
//          foreach ($users as $user) {
//            if($user->getId()) {
//              $historyUser = $this->json($user, Response::HTTP_OK, [], [
//                  ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//                    return $object->getId();
//                  }
//                ]
//              );
//              $historyUser = $historyUser->getContent();
//            }
//
//            if ($user->isSuspended()) {
//              $user->setIsSuspended(false);
//            } else {
//              $user->setIsSuspended(true);
//            }
//
//            $this->em->getRepository(User::class)->suspend($user, $historyUser);
//
//          }
//        }
//
//        $this->em->getRepository(Client::class)->save($client, $history);
//        if ($client->isSuspended()) {
//          notyf()
//            ->position('x', 'right')
//            ->position('y', 'top')
//            ->duration(5000)
//            ->dismissible(true)
//            ->addSuccess(NotifyMessagesData::CLIENT_SUSPENDED_TRUE);
//        } else {
//          notyf()
//            ->position('x', 'right')
//            ->position('y', 'top')
//            ->duration(5000)
//            ->dismissible(true)
//            ->addSuccess(NotifyMessagesData::CLIENT_SUSPENDED_FALSE);
//        }
//
//        return $this->redirectToRoute('app_client_profile_view', ['id' => $client->getId()]);
//      }
//    }
//    $args['form'] = $form->createView();
//
//
//    return $this->render('client/settings.html.twig', $args);
//  }
//
//  #[Route('/history-client-list/{id}', name: 'app_client_history_list')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function listClientHistory(Client $client, PaginatorInterface $paginator, Request $request)    : Response {
//    if (!$this->isGranted('ROLE_USER')) {
//      return $this->redirect($this->generateUrl('app_login'));
//    }
//
//    $args=[];
//    $args['client'] = $client;
//    $histories = $this->em->getRepository(ClientHistory::class)->getAllPaginator($client);
//
//    $pagination = $paginator->paginate(
//      $histories, /* query NOT result */
//      $request->query->getInt('page', 1), /*page number*/
//      20
//    );
//
//    $args['pagination'] = $pagination;
//
//    return $this->render('client/client_history_list.html.twig', $args);
//  }
//
//  #[Route('/history-client-view/{id}', name: 'app_client_profile_history_view')]
////  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function viewClientHistory(ClientHistory $clientHistory, SerializerInterface $serializer)    : Response { if (!$this->isGranted('ROLE_USER')) {
//    return $this->redirect($this->generateUrl('app_login'));
//  }
//
//    $args['clientH'] = $serializer->deserialize($clientHistory->getHistory(), Client::class, 'json');
//    $args['clientHistory'] = $clientHistory;
//
//    return $this->render('client/view_history_profile.html.twig', $args);
//  }

}
