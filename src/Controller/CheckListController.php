<?php

namespace App\Controller;

use App\Classes\Data\InternTaskStatusData;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\PrioritetData;
use App\Classes\Data\RepeatingIntervalData;
use App\Classes\Data\UserRolesData;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\ManagerChecklist;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use App\Form\ManagerChecklistFormType;
use App\Service\MailService;
use App\Service\UploadService;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checklists')]
class CheckListController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_checklist_list')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $user = $this->getUser();

    $dostupnosti = $this->em->getRepository(ManagerChecklist::class)->getChecklistPaginator(InternTaskStatusData::NIJE_ZAPOCETO, $user);

    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('check_list/phone/list.html.twig', $args);
    }
    return $this->render('check_list/list.html.twig', $args);
  }

  #[Route('/list-archive/', name: 'app_checklist_archive')]
  public function archive(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $user = $this->getUser();

    $dostupnosti = $this->em->getRepository(ManagerChecklist::class)->getChecklistPaginator(InternTaskStatusData::ZAVRSENO, $user);

    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('check_list/phone/archive.html.twig', $args);
    }
    return $this->render('check_list/archive.html.twig', $args);
  }

  #[Route('/list-active/', name: 'app_checklist_active')]
  public function active(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $user = $this->getUser();
    $dostupnosti = $this->em->getRepository(ManagerChecklist::class)->getChecklistPaginator(InternTaskStatusData::ZAPOCETO, $user);

    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('check_list/phone/active.html.twig', $args);
    }
    return $this->render('check_list/active.html.twig', $args);
  }

  #[Route('/to-do/', name: 'app_checklist_to_do')]
  public function toDo(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $user = $this->getUser();

    $dostupnosti = $this->em->getRepository(ManagerChecklist::class)->getChecklistToDoPaginator($user);

    $pagination = $paginator->paginate(
      $dostupnosti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('check_list/phone/list_to_do.html.twig', $args);
    }
    return $this->render('check_list/list_to_do.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_checklist_form', defaults: ['id' => 0])]
  #[Entity('checklist', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, ManagerChecklist $checklist, MailService $mailService, UploadService $uploadService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $mobileDetect = new MobileDetect();

    $company = $this->getUser()->getCompany();

    $datum = new DateTimeImmutable();
    $datum->setTime(0,0);


    if (!is_null($request->get('project'))) {
      $args['project'] = $this->em->getRepository(Project::class)->find($request->get('project'));
    }

    if (!is_null($request->get('datumCheck'))) {
      $args['noviDatum'] = DateTimeImmutable::createFromFormat('d.m.Y', $request->get('datumCheck'));
      $datum = $args['noviDatum'];
    }

    if (!is_null($request->get('phoneDatumCheck'))) {
      $args['noviDatum'] = DateTimeImmutable::createFromFormat('Y-m-d', $request->get('phoneDatumCheck'));
      $datum = $args['noviDatum'];
    }

    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      $args['korisnik'] = $this->getUser();
    }

    if ($request->isMethod('POST')) {

      $data = $request->request->all();

      $files = [];

      if (isset($data['checklist']['datum'])) {
        $datumKreiranja = DateTimeImmutable::createFromFormat('d.m.Y', $data['checklist']['datum'])->setTime(0, 0);
        $uploadFiles = $request->files->all()['checklist']['pdf'];
        $repeating = $data['checklist']['repeating'];
        $repeatingInterval = $data['checklist']['repeatingInterval'];


        $time1 = null;
        if (isset($request->request->all('checklist')['podsetnik'])) {
          $notify = $request->request->all('checklist')['podsetnik'];
        } else {
          $notify = null;
        }

        if (!empty($request->request->all('checklist')['vreme'])) {
          $time = $request->request->all('checklist')['vreme'];
          $time1 = $datumKreiranja->modify($time);
        }


        if(!empty ($uploadFiles)) {
          foreach ($uploadFiles as $uploadFile) {
            $pdf = new Pdf();
            $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
            $files[] = $file;
          }
        }

        foreach ($data['checklist']['zaduzeni'] as $zaduzeni) {
          $task = new ManagerChecklist();
          $task->setTask($data['checklist']['zadatak']);
          $task->setPriority($data['checklist']['prioritet']);
          $task->setCreatedBy($this->getUser());
          $task->setUser($this->em->getRepository(User::class)->find($zaduzeni));
          $task->setProject($this->em->getRepository(Project::class)->find($data['checklist']['project']));
          $task->setCategory($this->em->getRepository(Category::class)->find($data['checklist']['category']));
          $task->setDatumKreiranja($datumKreiranja);
          $task->setCompany($this->getUser()->getCompany());
          $task->setRepeating($repeating);
          $task->setTime($time1);
          if ($notify == 'on') {
            $task->setIsNotify(true);
          } else {
            $task->setIsNotify(false);
          }


          if ($repeating == 1) {

            $task->setRepeatingInterval($repeatingInterval);

            if ($task->getRepeatingInterval() == RepeatingIntervalData::DAY) {
              $task->setDatumPonavljanja($datumKreiranja->modify('+1 day'));
            }
            if ($task->getRepeatingInterval() == RepeatingIntervalData::WEEK) {
              $task->setDatumPonavljanja($datumKreiranja->modify('+1 week'));
            }
            if ($task->getRepeatingInterval() == RepeatingIntervalData::MONTH) {
              $task->setDatumPonavljanja($datumKreiranja->modify('+1 month'));
            }
            if ($task->getRepeatingInterval() == RepeatingIntervalData::YEAR) {
              $task->setDatumPonavljanja($datumKreiranja->modify('+1 year'));
            }
            if ($task->getRepeatingInterval() == RepeatingIntervalData::TACAN_DATUM) {
              $task->setDatumPonavljanja(DateTimeImmutable::createFromFormat('d.m.Y', $data['checklist']['datumPonavljanja'])->setTime(0, 0));
            }
          }

          if (!empty($files)) {
            foreach ($files as $file) {
              $pdf = new Pdf();
              $pdf->setTitle($file->getFileName());
              $pdf->setPath($file->getAssetPath());
              if (!is_null($task->getProject())) {
                $pdf->setProject($task->getProject());
              }
              $task->addPdf($pdf);
            }
          }

          $this->em->getRepository(ManagerChecklist::class)->save($task);

        $mailService->checklistTask($task);

        }
      }

      if (isset($data['phone_checklist']['datum'])) {
        $datumKreiranja = DateTimeImmutable::createFromFormat('m/d/Y', $data['phone_checklist']['datum'])->setTime(0, 0);
        $uploadFiles = $request->files->all()['phone_checklist']['pdf'];
        $repeating = $data['phone_checklist']['repeating'];
        $repeatingInterval = $data['phone_checklist']['repeatingInterval'];
        $time1 = null;

        if (isset($request->request->all('phone_checklist')['podsetnik'])) {
          $notify = $request->request->all('phone_checklist')['podsetnik'];
        } else {
          $notify = null;
        }

        if (!empty($request->request->all('phone_checklist')['vreme'])) {
          $time = $request->request->all('phone_checklist')['vreme'];
          $time1 = $datumKreiranja->modify($time);
        }

        if(!empty ($uploadFiles)) {
          foreach ($uploadFiles as $uploadFile) {
            $pdf = new Pdf();
            $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
            $files[] = $file;
          }
        }

        foreach ($data['phone_checklist']['zaduzeni'] as $zaduzeni) {
          $task = new ManagerChecklist();
          $task->setTask($data['phone_checklist']['zadatak']);
          $task->setPriority($data['phone_checklist']['prioritet']);
          $task->setCreatedBy($this->getUser());
          $task->setUser($this->em->getRepository(User::class)->find($zaduzeni));
          $task->setProject($this->em->getRepository(Project::class)->find($data['phone_checklist']['project']));
          $task->setCategory($this->em->getRepository(Category::class)->find($data['phone_checklist']['category']));
          $task->setDatumKreiranja($datumKreiranja);
          $task->setCompany($this->getUser()->getCompany());
          $task->setRepeating($repeating);
          $task->setTime($time1);
          if ($notify == 'on') {
            $task->setIsNotify(true);
          } else {
            $task->setIsNotify(false);
          }

          if ($repeating == 1) {

            $task->setRepeatingInterval($repeatingInterval);

            if ($task->getRepeatingInterval() == RepeatingIntervalData::DAY) {
              $task->setDatumPonavljanja($datumKreiranja->modify('+1 day'));
            }
            if ($task->getRepeatingInterval() == RepeatingIntervalData::WEEK) {
              $task->setDatumPonavljanja($datumKreiranja->modify('+1 week'));
            }
            if ($task->getRepeatingInterval() == RepeatingIntervalData::MONTH) {
              $task->setDatumPonavljanja($datumKreiranja->modify('+1 month'));
            }
            if ($task->getRepeatingInterval() == RepeatingIntervalData::YEAR) {
              $task->setDatumPonavljanja($datumKreiranja->modify('+1 year'));
            }
            if ($task->getRepeatingInterval() == RepeatingIntervalData::TACAN_DATUM) {
              $datumPonavljanja = DateTimeImmutable::createFromFormat('Y-m-d', $data['phone_checklist']['datumPonavljanja']);
              $task->setDatumPonavljanja($datumPonavljanja->setTime(0, 0));
            }
          }


          if (!empty($files)) {
            foreach ($files as $file) {
              $pdf = new Pdf();
              $pdf->setTitle($file->getFileName());
              $pdf->setPath($file->getAssetPath());
              if (!is_null($task->getProject())) {
                $pdf->setProject($task->getProject());
              }
              $task->addPdf($pdf);
            }
          }

          $this->em->getRepository(ManagerChecklist::class)->save($task);

        $mailService->checklistTask($task);

        }
      }

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::CHECKLIST_ADD);

      if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
        return $this->redirectToRoute('app_home');
      }

      return $this->redirectToRoute('app_checklist_list');

    }


    if ($company->getSettings()->isCalendar()) {
      $args['users'] = $this->em->getRepository(User::class)->getUsersAvailableChecklist($datum);
    } else {
      $args['users'] = $this->em->getRepository(User::class)->getUsersForChecklist();
    }

    $args['projects'] = $this->em->getRepository(Project::class)->findBy(['company' => $checklist->getCompany(), 'isSuspended' => false], ['title' => 'ASC']);
    $args['categories'] = $this->em->getRepository(Category::class)->getCategoriesTask();
    $args['priority'] = PrioritetData::form();
    $args['repeatingInterval'] = RepeatingIntervalData::form();

    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('check_list/form.html.twig', $args);
      }
      return $this->render('check_list/phone/form.html.twig', $args);
    }
    return $this->render('check_list/form.html.twig', $args);

  }


  #[Route('/edit/{id}', name: 'app_checklist_edit', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function edit(Request $request, ManagerChecklist $checklist, MailService $mailService, UploadService $uploadService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    if ($this->getUser()->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $this->getUser()->getUserType() != UserRolesData::ROLE_ADMIN && $this->getUser()->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($this->getUser() != $checklist->getCreatedBy() || $this->getUser() != $checklist->getUser()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }

    if ($checklist->getStatus() != InternTaskStatusData::NIJE_ZAPOCETO) {
      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addError(NotifyMessagesData::CHECKLIST_EDIT_ERROR);

      if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
        return $this->redirectToRoute('app_home');
      }

      return $this->redirectToRoute('app_checklist_list');
    }


    $form = $this->createForm(ManagerChecklistFormType::class, $checklist, ['attr' => ['action' => $this->generateUrl('app_checklist_edit', ['id' => $checklist->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $uploadFiles = $request->files->all()['checklist']['pdf'];
        if (!empty ($uploadFiles)) {
          foreach ($uploadFiles as $uploadFile) {
            $pdf = new Pdf();
            $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());
            $pdf->setTitle($file->getFileName());
            $pdf->setPath($file->getAssetPath());
            if (!is_null($checklist->getProject())) {
              $pdf->setProject($checklist->getProject());
            }
            $checklist->addPdf($pdf);
          }
        }

        if ($checklist->getRepeating() == 1) {
          if ($checklist->getRepeatingInterval() == RepeatingIntervalData::DAY) {
            $checklist->setDatumPonavljanja($checklist->getDatumKreiranja()->modify('+1 day'));
          }
          if ($checklist->getRepeatingInterval() == RepeatingIntervalData::WEEK) {
            $checklist->setDatumPonavljanja($checklist->getDatumKreiranja()->modify('+1 week'));
          }
          if ($checklist->getRepeatingInterval() == RepeatingIntervalData::MONTH) {
            $checklist->setDatumPonavljanja($checklist->getDatumKreiranja()->modify('+1 month'));
          }
          if ($checklist->getRepeatingInterval() == RepeatingIntervalData::YEAR) {
            $checklist->setDatumPonavljanja($checklist->getDatumKreiranja()->modify('+1 year'));
          }
        }

        $vreme = $checklist->getTime();

        if (!empty($request->request->get('manager_checklist_form_vreme'))) {
          $time = $request->request->get('manager_checklist_form_vreme');
          if (is_null($vreme)) {
            $vreme = new DateTimeImmutable();
          }
          $time1 = $vreme->modify($time);
          $checklist->setTime($time1);
        }

        if (!is_null($request->request->get('manager_checklist_form_podsetnik'))) {
          $notify = $request->request->get('manager_checklist_form_podsetnik');
        } else {
          $notify = null;
        }

        if ($notify == 'on') {
          $checklist->setIsNotify(true);
        } else {
          $checklist->setIsNotify(false);
        }

        $this->em->getRepository(ManagerChecklist::class)->save($checklist);

        $mailService->checklistEditTask($checklist);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::CHECKLIST_EDIT);

        if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
          return $this->redirectToRoute('app_home');
        }

        return $this->redirectToRoute('app_checklist_list');
      }
    }
    $args['form'] = $form->createView();
    $args['checklist'] = $checklist;


    return $this->render('check_list/edit.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_checklist_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(ManagerChecklist $checklist)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['checklist'] = $checklist;
    $args['comments'] = $this->em->getRepository(Comment::class)->findBy(['managerChecklist' => $checklist, 'isSuspended' => false], ['id' => 'DESC']);
    return $this->render('check_list/view.html.twig', $args);
  }

  #[Route('/finish/{id}', name: 'app_checklist_finish')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function finish(ManagerChecklist $checklist, Request $request, MailService $mailService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    if ($request->isMethod('POST')) {

      $data = $request->request->all();
      $checklist->setFinishDesc($data['checklist']['desc']);
      $checklist->setEditBy($this->getUser());
      $checklist->setStatus(InternTaskStatusData::ZAVRSENO);

      $this->em->getRepository(ManagerChecklist::class)->finish($checklist);

      $mailService->checklistStatusTask($checklist);

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::CHECKLIST_CLOSE);

      if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
        return $this->redirectToRoute('app_home');
      }

      return $this->redirectToRoute('app_checklist_archive');

    }

    $args['checklist'] = $checklist;
    return $this->render('check_list/finish.html.twig', $args);
  }

  #[Route('/start/{id}', name: 'app_checklist_start')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function start(ManagerChecklist $checklist, MailService $mailService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $koris = $this->getUser();
    $this->em->getRepository(ManagerChecklist::class)->start($checklist, $koris);

    $mailService->checklistStatusTask($checklist);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::CHECKLIST_START);

    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      return $this->redirectToRoute('app_home');
    }

    return $this->redirectToRoute('app_checklist_active');
  }

  #[Route('/replay/{id}', name: 'app_checklist_replay')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function replay(ManagerChecklist $checklist, MailService $mailService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $koris = $this->getUser();

    $this->em->getRepository(ManagerChecklist::class)->replay($checklist, $koris);

    $mailService->checklistStatusTask($checklist);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::CHECKLIST_REPLAY);

    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      return $this->redirectToRoute('app_home');
    }

    return $this->redirectToRoute('app_checklist_list');
  }

  #[Route('/convert/{id}', name: 'app_checklist_convert')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function convert(ManagerChecklist $checklist, MailService $mailService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    if ($this->getUser()->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $this->getUser()->getUserType() != UserRolesData::ROLE_ADMIN && $this->getUser()->getUserType() != UserRolesData::ROLE_MANAGER) {
      if ($this->getUser() != $checklist->getCreatedBy() || $this->getUser() != $checklist->getUser()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }

    if ($checklist->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
      return $this->redirect($this->generateUrl('app_home'));
    }

    if ($checklist->getStatus() != InternTaskStatusData::NIJE_ZAPOCETO) {
      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addError(NotifyMessagesData::CHECKLIST_CONVERT_ERROR);
        return $this->redirectToRoute('app_checklist_list');
    }

    $koris = $this->getUser();

    $task = $this->em->getRepository(Task::class)->createTaskFromChecklist($checklist);
    $checklist->setStatus(InternTaskStatusData::KONVERTOVANO);
    $this->em->getRepository(ManagerChecklist::class)->save($checklist);

    $mailService->checklistConvertTask($checklist, $task);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::CHECKLIST_CONVERT);

    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      return $this->redirectToRoute('app_home');
    }

    return $this->redirectToRoute('app_checklist_list');
  }

  #[Route('/delete/{id}', name: 'app_checklist_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function delete(ManagerChecklist $checklist)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $this->em->getRepository(ManagerChecklist::class)->remove($checklist);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::CHECKLIST_DELETE);

    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      return $this->redirectToRoute('app_home');
    }

    return $this->redirectToRoute('app_checklist_list');
  }

  #[Route('/turn-off-repeating/{id}', name: 'app_checklist_turn_off_repeating')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function turnOff(ManagerChecklist $checklist)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $checklist->setRepeating(0);
    $checklist->setRepeatingInterval(null);
    $checklist->setDatumPonavljanja(null);

    $this->em->getRepository(ManagerChecklist::class)->save($checklist);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::CHECKLIST_EDIT);

    if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      return $this->redirectToRoute('app_home');
    }

    return $this->redirectToRoute('app_checklist_list');
  }
}
