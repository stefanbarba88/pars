<?php

namespace App\Controller;

use App\Classes\AppConfig;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\TaskStatusData;
use App\Classes\Data\UserRolesData;
use App\Entity\Image;
use App\Entity\Overtime;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\StopwatchTime;
use App\Entity\TaskLog;
use App\Entity\TimeTask;
use App\Entity\User;
use App\Entity\VerifyActivity;
use App\Form\ActiveStopwatchTimeAddFormType;
use App\Form\ActiveStopwatchTimeFormType;
use App\Form\StopwatchTimeAddFormType;
use App\Form\StopwatchTimeFormType;
use App\Service\MailService;
use App\Service\UploadService;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stopwatch')]
class StopwatchController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/start/{id}', name: 'app_stopwatch_start')]
  public function start(TaskLog $taskLog, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();

    if ($user->isInTask() ) {
        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addError(NotifyMessagesData::STOPWATCH_START_ERROR);

        return $this->redirectToRoute('app_home');

    }

    $stopwatch = new StopwatchTime();
    $stopwatch->setTaskLog($taskLog);
    $stopwatch->setStart(new DateTimeImmutable());
    $stopwatch->setLon($request->query->get('lon'));
    $stopwatch->setLat($request->query->get('lat'));
    $stopwatch->setCompany($taskLog->getTask()->getCompany());

    $this->em->getRepository(StopwatchTime::class)->save($stopwatch);

    $user->setIsInTask(true);
    $this->em->getRepository(User::class)->save($user);

//    $this->em->getRepository(Task::class)->changeStatus($taskLog->getTask(), TaskStatusData::ZAPOCETO);

    return $this->redirectToRoute('app_home');
  }

  #[Route('/form/{id}', name: 'app_stopwatch_form')]
  public function form(StopwatchTime $stopwatch, Request $request, UploadService $uploadService, SessionInterface $session, MailService $mailService) : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];


  if (is_null($stopwatch->getStop())) {

    $stopwatch->setStop(new DateTimeImmutable());

    if ($session->has('LonStop')) {
      $stopwatch->setLonStop($session->get('LonStop'));

    } else {
      $stopwatch->setLonStop($request->query->get('lon'));
      $session->set('LonStop', $request->query->get('lon'));
    }
    if ($session->has('LatStop')) {
      $stopwatch->setLatStop($session->get('LatStop'));

    } else {
      $stopwatch->setLatStop($request->query->get('lat'));
      $session->set('LatStop', $request->query->get('lat'));
    }
    $days = $stopwatch->getStart()->diff($stopwatch->getStop())->d;
    $hours = $stopwatch->getStart()->diff($stopwatch->getStop())->h;
    $hours = $days * 24 + $hours;
    $minutes = $stopwatch->getStart()->diff($stopwatch->getStop())->i;

    $stopwatch = $this->em->getRepository(StopwatchTime::class)->setTime($stopwatch, $hours, $minutes);
//    $session->remove('LonStop');
//    $session->remove('LatStop');
  }
    $history = null;
    //ovde izvlacimo ulogovanog usera
//    $user = $this->getUser();
//    $user = $this->em->getRepository(User::class)->find(1);
//    if ($task->getId()) {
//      $history = $this->json($task, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }
//    dd($stopwatch);
    $mobileDetect = new MobileDetect();

    $form = $this->createForm(StopwatchTimeFormType::class, $stopwatch, ['attr' => ['action' => $this->generateUrl('app_stopwatch_form', ['id' => $stopwatch->getId()])]]);

    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        if (isset($request->request->all()['image_delete'])) {
          $deleteImages = $request->request->all()['image_delete'];
          foreach ($deleteImages as $image) {
            if (isset($image['checked'])) {
              $image = $this->em->getRepository(Image::class)->find($image['value']);
              $stopwatch->removeImage($image);
            }
          }
        }
        if (isset($request->request->all()['pdf_delete'])) {
          $deletePdfs = $request->request->all()['pdf_delete'];
          foreach ($deletePdfs as $pdf) {
            if (isset($pdf['checked'])) {
              $pdf = $this->em->getRepository(Pdf::class)->find($pdf['value']);
              $stopwatch->removePdf($pdf);
              $pdf->setProject(null);
              $pdf->setTask(null);
              $pdf = $this->em->getRepository(Pdf::class)->savePdf($pdf);
            }
          }
        }


        $actIds = [];
        foreach ($stopwatch->getActivity() as $act) {
          $actIds[] = $act->getId();
        }

        if (!in_array(AppConfig::NEMA_U_LISTI_ID, $actIds)) {
          $stopwatch->setAdditionalActivity(null);
        }
        if (!in_array(AppConfig::OBRADA_ID, $actIds)) {
          $stopwatch->setAdditionalDesc(null);
        }

       if (isset($request->request->all()['stopwatch_time_form']['robotika'])) {
         $stopwatch->setIsRobotika(true);
       } else {
         $stopwatch->setIsRobotika(false);
       }

        $sati = $request->request->get('overtime_vreme_sati');
        $minuti = $request->request->get('overtime_vreme_minuti');
        $napomena = $request->request->get('overtime_napomena');

        $uploadFiles = $request->files->all()['stopwatch_time_form']['pdf'];
        if (!empty ($uploadFiles)) {
          foreach ($uploadFiles as $uploadFile) {
            $pdf = new Pdf();
            $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());

            $pdf->setTitle($file->getFileName());
            $pdf->setPath($file->getAssetPath());
            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $pdf->setProject($stopwatch->getTaskLog()->getTask()->getProject());
            }
            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $pdf->setProject($stopwatch->getTaskLog()->getTask()->getProject());
            }
            $pdf->setTask($stopwatch->getTaskLog()->getTask());

            $stopwatch->addPdf($pdf);
          }
        }

        $uploadImages = $request->files->all()['stopwatch_time_form']['image'];
        if (!empty ($uploadImages)) {
          foreach ($uploadImages as $uploadFile) {

            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $path = $stopwatch->getTaskLog()->getUploadPath();
              $pathThumb = $stopwatch->getTaskLog()->getThumbUploadPath();
            } else {
              $path = $stopwatch->getTaskLog()->getNoProjectUploadPath();
              $pathThumb = $stopwatch->getTaskLog()->getNoProjectThumbUploadPath();
            }
            $file = $uploadService->upload($uploadFile, $path);
            $image = $this->em->getRepository(Image::class)->add($file, $pathThumb, $this->getParameter('kernel.project_dir'));
            $stopwatch->addImage($image);
          }
        }


        $text = trim($stopwatch->getAdditionalActivity());
        $sentences = preg_split('/[.,;]\s*/', $text);
        // Iteriraj kroz svaku rečenicu
        foreach ($sentences as &$sentence) {
          // Učini svaku rečenicu da počinje velikim slovom
          $sentence = ucfirst(trim($sentence));
        }

        // Spoji rečenice nazad u jedan string
//        $processedText = implode('. ', $sentences) . '.';
        $processedText = implode('. ', $sentences);

        $stopwatch->setAdditionalActivity($processedText);

        //ako je neka od stavki "Sleganje" u dnevniku glavnog geodete kreiraj "VerifyActivity"
        if ($stopwatch->getTaskLog()->getTask()->getPriorityUserLog() == $stopwatch->getTaskLog()->getUser()->getId()) {
          foreach ($stopwatch->getActivity() as $activity) {
            if ($activity->getId() == 42) {
              $verify = new VerifyActivity();
              $verify->setStopwatch($stopwatch);
              $verify->setCompany($stopwatch->getCompany());
              $verify->setZaduzeni($stopwatch->getTaskLog()->getUser());
              $this->em->getRepository(VerifyActivity::class)->save($verify);
              $mailService->activityVerify($verify, $stopwatch->getCompany()->getEmail());
            }
          }
        }

        $this->em->getRepository(StopwatchTime::class)->save($stopwatch);

        if (!is_null($sati)) {
          if ($sati != 0 || $minuti != 0) {
            $overtime = new Overtime();
            $overtime->setUser($stopwatch->getTaskLog()->getUser());
            $overtime->setHours($sati);
            $overtime->setMinutes($minuti);
            $overtime->setNote($napomena);
            $overtime->setDatum($stopwatch->getCreated()->setTime(0, 0));
            $overtime->setStatus(0);
            $overtime->setTask($stopwatch->getTaskLog()->getTask());
            $overtime->setCompany($stopwatch->getTaskLog()->getTask()->getCompany());
            $this->em->getRepository(Overtime::class)->save($overtime);
          }
        }
        $user = $this->getUser();
        $user->setIsInTask(false);
        $this->em->getRepository(User::class)->save($user);
        $session->remove('LonStop');
        $session->remove('LatStop');

//        $this->em->getRepository(Task::class)->changeStatus($stopwatch->getTaskLog()->getTask(), TaskStatusData::ZAVRSENO);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::STOPWATCH_ADD);

//        return $this->redirectToRoute('app_task_log_view', ['id' => $stopwatch->getTaskLog()->getId()]);
        return $this->redirectToRoute('app_home');
      }
    }
    $args['form'] = $form->createView();
    $args['stopwatch'] = $stopwatch;
    $args['hours'] = intdiv($stopwatch->getDiffRounded(), 60);
    $args['minutes'] = $stopwatch->getDiffRounded() % 60;
    $args['task'] = $stopwatch->getTaskLog()->getTask();
    $args['images'] = $stopwatch->getImage()->toArray();
    $args['pdfs'] = $stopwatch->getPdf()->toArray();

    if ($args['hours'] < 10) {
      $args['hours'] = '0' . $args['hours'];
    }

    // Provjerava da li su minute jednocifren broj i dodaje nulu ispred
    if ($args['minutes'] < 10) {
      $args['minutes'] = '0' . $args['minutes'];
    }

    if ($mobileDetect->isMobile()) {
      return $this->render('task/mobile_stopwatch_form.html.twig', $args);
    }

    return $this->render('task/stopwatch_form.html.twig', $args);
  }

  #[Route('/form-add/{taskLog}/{id}', name: 'app_stopwatch_add_form', defaults: ['id' => 0])]
  #[Entity('taskLog', expr: 'repository.find(taskLog)')]
  #[Entity('stopwatch', expr: 'repository.findForForm(taskLog, id)')]
  public function add(TaskLog $taskLog, StopwatchTime $stopwatch, Request $request, UploadService $uploadService, MailService $mailService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $history = null;
    //ovde izvlacimo ulogovanog usera
    $user = $this->getUser();

//    $user = $this->em->getRepository(User::class)->find(1);
//    if ($task->getId()) {
//      $history = $this->json($task, Response::HTTP_OK, [], [
//          ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
//            return $object->getId();
//          }
//        ]
//      );
//      $history = $history->getContent();
//    }
//    dd($stopwatch);
    $form = $this->createForm(StopwatchTimeAddFormType::class, $stopwatch, ['attr' => ['action' => $this->generateUrl('app_stopwatch_add_form', ['taskLog' => $taskLog->getId(), 'id' => $stopwatch->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        if ($this->em->getRepository(StopwatchTime::class)->checkAddStopwatch($request->request->get('stopwatch_time_add_form_period'), $taskLog)) {
          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addError(NotifyMessagesData::STOPWATCH_ADD_ERROR);

          return $this->redirectToRoute('app_task_log_view', ['id' => $taskLog->getId()]);
        }

        if (isset($request->request->all()['stopwatch_time_add_form']['robotika'])) {
          $stopwatch->setIsRobotika(true);
        } else {
          $stopwatch->setIsRobotika(false);
        }

        $stopwatch = $this->em->getRepository(StopwatchTime::class)->setTimeManual($stopwatch, $request->request->get('stopwatch_time_add_form_period'));

        $uploadFiles = $request->files->all()['stopwatch_time_add_form']['pdf'];
        if (!empty ($uploadFiles)) {
          foreach ($uploadFiles as $uploadFile) {
            $pdf = new Pdf();
            $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());

            $pdf->setTitle($file->getFileName());
            $pdf->setPath($file->getAssetPath());
            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $pdf->setProject($stopwatch->getTaskLog()->getTask()->getProject());
            }
            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $pdf->setProject($stopwatch->getTaskLog()->getTask()->getProject());
            }
            $pdf->setTask($stopwatch->getTaskLog()->getTask());

            $stopwatch->addPdf($pdf);
          }
        }

        $uploadImages = $request->files->all()['stopwatch_time_add_form']['image'];
        if (!empty ($uploadImages)) {
          foreach ($uploadImages as $uploadFile) {

            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $path = $stopwatch->getTaskLog()->getUploadPath();
              $pathThumb = $stopwatch->getTaskLog()->getThumbUploadPath();
            } else {
              $path = $stopwatch->getTaskLog()->getNoProjectUploadPath();
              $pathThumb = $stopwatch->getTaskLog()->getNoProjectThumbUploadPath();
            }
            $file = $uploadService->upload($uploadFile, $path);
            $image = $this->em->getRepository(Image::class)->add($file, $pathThumb, $this->getParameter('kernel.project_dir'));
            $stopwatch->addImage($image);
          }
        }

        $text = trim($stopwatch->getAdditionalActivity());
        $sentences = preg_split('/[.,;]\s*/', $text);
        // Iteriraj kroz svaku rečenicu
        foreach ($sentences as &$sentence) {
          // Učini svaku rečenicu da počinje velikim slovom
          $sentence = ucfirst(trim($sentence));
        }

        // Spoji rečenice nazad u jedan string
//        $processedText = implode('. ', $sentences) . '.';
        $processedText = implode('. ', $sentences);

        $stopwatch->setAdditionalActivity($processedText);

//ako je neka od stavki "Sleganje" u dnevniku glavnog geodete kreiraj "VerifyActivity"
        if ($stopwatch->getTaskLog()->getTask()->getPriorityUserLog() == $stopwatch->getTaskLog()->getUser()->getId()) {
          foreach ($stopwatch->getActivity() as $activity) {
            if ($activity->getId() == 42) {
              $verify = new VerifyActivity();
              $verify->setStopwatch($stopwatch);
              $verify->setCompany($stopwatch->getTaskLog()->getUser()->getCompany());
              $verify->setZaduzeni($stopwatch->getTaskLog()->getUser());
              $this->em->getRepository(VerifyActivity::class)->save($verify);
              $mailService->activityVerify($verify, $stopwatch->getTaskLog()->getUser()->getCompany()->getEmail());
            }
          }
        }


        $stopwatch->setIsEdited(true);
        $stopwatch->setEditedBy($user);
        $stopwatch->setCompany($user->getCompany());
//        $this->em->getRepository(Availability::class)->addDostupnost($stopwatch);
        $this->em->getRepository(StopwatchTime::class)->save($stopwatch);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::STOPWATCH_ADD);

        return $this->redirectToRoute('app_task_log_view', ['id' => $taskLog->getId()]);
      }
    }

    $args['form'] = $form->createView();
    $args['min'] = 0;
    $args['step'] = 1;
    $args['task'] = $taskLog->getTask();

    if (!is_null($taskLog->getTask()->isIsTimeRoundUp())) {
      if ($taskLog->getTask()->isIsTimeRoundUp()) {
        $args['min'] = $taskLog->getTask()->getMinEntry();
        $args['step'] = $taskLog->getTask()->getRoundingInterval();
      }
    } else {
      if (!is_null($taskLog->getTask()->getProject())) {
        if ($taskLog->getTask()->getProject()->isTimeRoundUp()) {
          $args['min'] = $taskLog->getTask()->getProject()->getMinEntry();
          $args['step'] = $taskLog->getTask()->getProject()->getRoundingInterval();
        }
      }
    }

    $stopwatch->setMin($args['min']);


    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('task/stopwatch_form_modal.html.twig', $args);
      }
      return $this->render('task/phone/stopwatch_form_modal.html.twig', $args);
    }
    return $this->render('task/stopwatch_form_modal.html.twig', $args);
  }

  #[Route('/form-edit/{id}', name: 'app_stopwatch_edit_forma')]
  public function edit(StopwatchTime $stopwatch, Request $request, UploadService $uploadService, MailService $mailService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $args = [];

    $history = null;

    $user = $this->getUser();

    $form = $this->createForm(StopwatchTimeAddFormType::class, $stopwatch, ['attr' => ['action' => $this->generateUrl('app_stopwatch_edit_forma', ['id' => $stopwatch->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        if (isset($request->request->all()['stopwatch_time_add_form']['robotika'])) {
          $stopwatch->setIsRobotika(true);
        } else {
          $stopwatch->setIsRobotika(false);
        }

        $uploadFiles = $request->files->all()['stopwatch_time_add_form']['pdf'];
        if (!empty ($uploadFiles)) {
          foreach ($uploadFiles as $uploadFile) {
            $pdf = new Pdf();
            $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());

            $pdf->setTitle($file->getFileName());
            $pdf->setPath($file->getAssetPath());
            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $pdf->setProject($stopwatch->getTaskLog()->getTask()->getProject());
            }
            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $pdf->setProject($stopwatch->getTaskLog()->getTask()->getProject());
            }
            $pdf->setTask($stopwatch->getTaskLog()->getTask());

            $stopwatch->addPdf($pdf);
          }
        }

        $uploadImages = $request->files->all()['stopwatch_time_add_form']['image'];
        if (!empty ($uploadImages)) {
          foreach ($uploadImages as $uploadFile) {

            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $path = $stopwatch->getTaskLog()->getUploadPath();
              $pathThumb = $stopwatch->getTaskLog()->getThumbUploadPath();
            } else {
              $path = $stopwatch->getTaskLog()->getNoProjectUploadPath();
              $pathThumb = $stopwatch->getTaskLog()->getNoProjectThumbUploadPath();
            }
            $file = $uploadService->upload($uploadFile, $path);
            $image = $this->em->getRepository(Image::class)->add($file, $pathThumb, $this->getParameter('kernel.project_dir'));
            $stopwatch->addImage($image);
          }
        }

        $stopwatch->setIsEdited(true);
        $stopwatch->setEditedBy($user);

        $text = trim($stopwatch->getAdditionalActivity());
        $sentences = preg_split('/[.,;]\s*/', $text);
        // Iteriraj kroz svaku rečenicu
        foreach ($sentences as &$sentence) {
          // Učini svaku rečenicu da počinje velikim slovom
          $sentence = ucfirst(trim($sentence));
        }

        // Spoji rečenice nazad u jedan string
//        $processedText = implode('. ', $sentences) . '.';
        $processedText = implode('. ', $sentences);

        $stopwatch->setAdditionalActivity($processedText);

        //ako je neka od stavki "Sleganje" u dnevniku glavnog geodete kreiraj "VerifyActivity"
        if ($stopwatch->getTaskLog()->getTask()->getPriorityUserLog() == $stopwatch->getTaskLog()->getUser()->getId()) {

          $sleganje = false;
          $verify = $this->em->getRepository(VerifyActivity::class)->findOneBy(['zaduzeni' => $stopwatch->getTaskLog()->getUser(), 'status' => TaskStatusData::NIJE_ZAPOCETO, 'stopwatch' => $stopwatch]);

          foreach ($stopwatch->getActivity() as $activity) {
            if ($activity->getId() == 42) {
              $sleganje = true;
            }
          }

          if (!$sleganje) {
            if (!is_null($verify)) {
              $this->em->getRepository(VerifyActivity::class)->remove($verify);
            }
          } else {
            if (is_null($verify)) {
              $verify = new VerifyActivity();
              $verify->setStopwatch($stopwatch);
              $verify->setCompany($stopwatch->getCompany());
              $verify->setZaduzeni($stopwatch->getTaskLog()->getUser());
              $this->em->getRepository(VerifyActivity::class)->save($verify);
              $mailService->activityVerify($verify, $stopwatch->getCompany()->getEmail());
            }
          }
        }


        $this->em->getRepository(StopwatchTime::class)->save($stopwatch);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::STOPWATCH_EDIT);

        return $this->redirectToRoute('app_task_log_view', ['id' => $stopwatch->getTaskLog()->getId()]);
      }
    }

    $args['form'] = $form->createView();
    $args['stopwatch'] = $stopwatch;
    $args['task'] = $stopwatch->getTaskLog()->getTask();
    $args['taskLog'] = $stopwatch->getTaskLog();

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('task/stopwatch_form_edit.html.twig', $args);
      }
      return $this->render('task/phone/stopwatch_form_edit.html.twig', $args);
    }
    return $this->render('task/stopwatch_form_edit.html.twig', $args);
  }

  #[Route('/form-edit-time/{id}', name: 'app_stopwatch_edit_time_forma')]
  public function editTime(StopwatchTime $stopwatch, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $history = null;

    $user = $this->getUser();

    if ($request->isMethod('POST')) {


//      if ($this->em->getRepository(StopwatchTime::class)->checkAddStopwatch($request->request->get('stopwatch_time_add_form_period'), $stopwatch->getTaskLog())) {
//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addError(NotifyMessagesData::STOPWATCH_ADD_ERROR);
//
//        return $this->redirectToRoute('app_stopwatch_edit_time_forma', ['id' => $stopwatch->getId()]);
//      }


        $stopwatch = $this->em->getRepository(StopwatchTime::class)->setTimeManual($stopwatch, $request->request->get('stopwatch_time_add_form_period'));
        $stopwatch->setIsEdited(true);
        $stopwatch->setEditedBy($user);

        $this->em->getRepository(StopwatchTime::class)->save($stopwatch);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::STOPWATCH_EDIT);

        return $this->redirectToRoute('app_task_log_view', ['id' => $stopwatch->getTaskLog()->getId()]);

    }

    $args['stopwatch'] = $stopwatch;
    $args['task'] = $stopwatch->getTaskLog()->getTask();
    $args['taskLog'] = $stopwatch->getTaskLog();

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      if($this->getUser()->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
        return $this->render('task/stopwatch_form_edit_time.html.twig', $args);
      }
      return $this->render('task/phone/stopwatch_form_edit_time.html.twig', $args);
    }
    return $this->render('task/stopwatch_form_edit_time.html.twig', $args);
  }

  #[Route('/delete/{id}', name: 'app_stopwatch_delete')]
  public function delete(StopwatchTime $stopwatch)    : Response { if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $taskLogId = $stopwatch->getTaskLog()->getId();
    $user = $this->getUser();
//    $this->em->getRepository(Availability::class)->addDostupnostDelete($stopwatch);
    $this->em->getRepository(StopwatchTime::class)->deleteStopwatch($stopwatch, $user);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::STOPWATCH_DELETE);

    return $this->redirectToRoute('app_task_log_view', ['id' => $taskLogId]);
  }

  #[Route('/check/{id}', name: 'app_stopwatch_check')]
  public function check(StopwatchTime $stopwatch)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $stopwatch->setChecked(1);
    $this->em->getRepository(StopwatchTime::class)->save($stopwatch);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::STOPWATCH_CHECKED);

    return $this->redirectToRoute('app_stopwatch_list');
  }

  #[Route('/list/', name: 'app_stopwatch_list')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }
    $args = [];

    $tasks = $this->em->getRepository(StopwatchTime::class)->findAllToCheck();

    $pagination = $paginator->paginate(
      $tasks, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('task/phone/stopwatches_to_check.html.twig', $args);
    }

    return $this->render('task/stopwatches_to_check.html.twig', $args);

  }

  #[Route('/close/{id}', name: 'app_stopwatch_close')]
  public function close(StopwatchTime $stopwatch)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $task = $stopwatch->getTaskLog()->getTask();
    $this->em->getRepository(StopwatchTime::class)->close($stopwatch);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::STOPWATCH_CLOSE);

    return $this->redirectToRoute('app_task_view', ['id' => $task->getId()]);
  }


  #[Route('/start-firma/{id}', name: 'app_stopwatch_start_firma',  defaults: ['id' => 0])]
  #[Entity('task', expr: 'repository.findForForm(id)')]
  public function startFirma(TimeTask $task, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();

    if ($user->isInTask()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $this->em->getRepository(TimeTask::class)->save($task);

    $user->setIsInTask(true);
    $this->em->getRepository(User::class)->save($user);

    return $this->redirectToRoute('app_home');
  }

  #[Route('/stop-firma', name: 'app_stopwatch_stop_firma')]
  public function stopFirma(Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $user = $this->getUser();

    if ($request->isMethod('POST')) {
      $task = $this->em->getRepository(TimeTask::class)->findOneBy(['user' => $user, 'finish' => null]);
      $task->setFinish(new DateTimeImmutable());
      $task->setDescription($request->request->get('napomena'));
      $this->em->getRepository(TimeTask::class)->save($task);

      $user->setIsInTask(false);
      $this->em->getRepository(User::class)->save($user);

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::TIME_TASK_CLOSE);

//        return $this->redirectToRoute('app_task_log_view', ['id' => $stopwatch->getTaskLog()->getId()]);
      return $this->redirectToRoute('app_home');

    }

    return $this->render('task/time_task_form.html.twig', $args);
  }

  #[Route('/list-verify-activity/', name: 'app_stopwatch_verify_activity_list')]
  public function listVerifyActivity(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $user = $this->getUser();


    $activities = $this->em->getRepository(VerifyActivity::class)->getAllActivitiesToVerify($user);

    $pagination = $paginator->paginate(
      $activities, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();

    if($mobileDetect->isMobile()) {
      return $this->render('task/phone/activities_to_check.html.twig', $args);
    }

    return $this->render('task/activities_to_check.html.twig', $args);

  }

  #[Route('/list-verify-tasks/', name: 'app_stopwatch_verify_tasks_list')]
  public function listVerifyTasks(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
    $user = $this->getUser();


    $activities = $this->em->getRepository(Project::class)->getAllProjectsToVerify($user);

    $pagination = $paginator->paginate(
      $activities, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;

    $mobileDetect = new MobileDetect();

    if($mobileDetect->isMobile()) {
      return $this->render('task/phone/projects_to_check.html.twig', $args);
    }

    return $this->render('task/projects_to_check.html.twig', $args);

  }


  #[Route('/check-verify-task/{id}', name: 'app_stopwatch_verify_task_check')]
  public function checkVerifyTask(Project $project, MailService $mailService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $project->setIsCreated(false);
    $message = NotifyMessagesData::PROJECT_VERIFY_TASK;
    $this->em->getRepository(Project::class)->save($project);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess($message);

    return $this->redirectToRoute('app_stopwatch_verify_tasks_list');
  }


  #[Route('/check-verify-activity/{id}', name: 'app_stopwatch_verify_activity_check')]
  public function checkVerifyActivity(VerifyActivity $verifyActivity, MailService $mailService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    if ($verifyActivity->getStatus() == TaskStatusData::NIJE_ZAPOCETO) {
      $verifyActivity->setStatus(TaskStatusData::ZAVRSENO);
      $message = NotifyMessagesData::VERIFY_ACTIVITY_CHECKED;
    } else {
      $verifyActivity->setStatus(TaskStatusData::NIJE_ZAPOCETO);
      $message = NotifyMessagesData::VERIFY_ACTIVITY_UNCHECKED;
    }

    $this->em->getRepository(VerifyActivity::class)->save($verifyActivity);
    $mailService->responseActivityVerify($verifyActivity);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess($message);

    return $this->redirectToRoute('app_stopwatch_verify_activity_list');
  }

  #[Route('/delete-verify-activity/{id}', name: 'app_stopwatch_verify_activity_delete')]
  public function deleteVerifyActivity(VerifyActivity $verifyActivity)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    $this->em->getRepository(VerifyActivity::class)->remove($verifyActivity);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::VERIFY_ACTIVITY_DELETE);

    return $this->redirectToRoute('app_stopwatch_verify_activity_list');
  }


  #[Route('/form-add-in-task/{id}', name: 'app_stopwatch_add_form_in_task')]
  public function addInTask(StopwatchTime $stopwatch, Request $request, UploadService $uploadService, MailService $mailService)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];

    $history = null;

    $mobileDetect = new MobileDetect();

    $form = $this->createForm(ActiveStopwatchTimeFormType::class, $stopwatch, ['attr' => ['action' => $this->generateUrl('app_stopwatch_add_form_in_task', ['id' => $stopwatch->getId()])]]);

    if ($request->isMethod('POST')) {

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {


        if (isset($request->request->all()['image_delete'])) {
          $deleteImages = $request->request->all()['image_delete'];
          foreach ($deleteImages as $image) {
            if (isset($image['checked'])) {
              $image = $this->em->getRepository(Image::class)->find($image['value']);
              $stopwatch->removeImage($image);
            }
          }
        }
        if (isset($request->request->all()['pdf_delete'])) {
          $deletePdfs = $request->request->all()['pdf_delete'];
          foreach ($deletePdfs as $pdf) {
            if (isset($pdf['checked'])) {
              $pdf = $this->em->getRepository(Pdf::class)->find($pdf['value']);
              $stopwatch->removePdf($pdf);
              $pdf->setProject(null);
              $pdf->setTask(null);
              $pdf = $this->em->getRepository(Pdf::class)->savePdf($pdf);
            }
          }
        }

        $actIds = [];
        foreach ($stopwatch->getActivity() as $act) {
          $actIds[] = $act->getId();
        }

        if (!in_array(AppConfig::NEMA_U_LISTI_ID, $actIds)) {
          $stopwatch->setAdditionalActivity(null);
        }
        if (!in_array(AppConfig::OBRADA_ID, $actIds)) {
          $stopwatch->setAdditionalDesc(null);
        }


        $uploadFiles = $request->files->all()['active_stopwatch_time_form']['pdf'];
        if (!empty ($uploadFiles)) {
          foreach ($uploadFiles as $uploadFile) {
            $pdf = new Pdf();
            $file = $uploadService->upload($uploadFile, $pdf->getPdfUploadPath());

            $pdf->setTitle($file->getFileName());
            $pdf->setPath($file->getAssetPath());
            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $pdf->setProject($stopwatch->getTaskLog()->getTask()->getProject());
            }
            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $pdf->setProject($stopwatch->getTaskLog()->getTask()->getProject());
            }
            $pdf->setTask($stopwatch->getTaskLog()->getTask());

            $stopwatch->addPdf($pdf);
          }
        }

        $uploadImages = $request->files->all()['active_stopwatch_time_form']['image'];
        if (!empty ($uploadImages)) {
          foreach ($uploadImages as $uploadFile) {

            if (!is_null($stopwatch->getTaskLog()->getTask()->getProject())) {
              $path = $stopwatch->getTaskLog()->getUploadPath();
              $pathThumb = $stopwatch->getTaskLog()->getThumbUploadPath();
            } else {
              $path = $stopwatch->getTaskLog()->getNoProjectUploadPath();
              $pathThumb = $stopwatch->getTaskLog()->getNoProjectThumbUploadPath();
            }
            $file = $uploadService->upload($uploadFile, $path);
            $image = $this->em->getRepository(Image::class)->add($file, $pathThumb, $this->getParameter('kernel.project_dir'));
            $stopwatch->addImage($image);
          }
        }


        $text = trim($stopwatch->getAdditionalActivity());
        $sentences = preg_split('/[.,;]\s*/', $text);
        // Iteriraj kroz svaku rečenicu
        foreach ($sentences as &$sentence) {
          // Učini svaku rečenicu da počinje velikim slovom
          $sentence = ucfirst(trim($sentence));
        }

        // Spoji rečenice nazad u jedan string
//        $processedText = implode('. ', $sentences) . '.';
        $processedText = implode('. ', $sentences);

        $stopwatch->setAdditionalActivity($processedText);

        $this->em->getRepository(StopwatchTime::class)->save($stopwatch);

//        $this->em->getRepository(Task::class)->changeStatus($stopwatch->getTaskLog()->getTask(), TaskStatusData::ZAVRSENO);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::STOPWATCH_ADD);

//        return $this->redirectToRoute('app_task_log_view', ['id' => $stopwatch->getTaskLog()->getId()]);
        return $this->redirectToRoute('app_home');
      }
    }
    $args['form'] = $form->createView();
    $args['stopwatch'] = $stopwatch;
    $args['images'] = $stopwatch->getImage()->toArray();
    $args['pdfs'] = $stopwatch->getPdf()->toArray();

    $args['task'] = $stopwatch->getTaskLog()->getTask();



    if ($mobileDetect->isMobile()) {
      return $this->render('task/mobile_stopwatch_form_in_task.html.twig', $args);
    }

    return $this->render('task/stopwatch_form_in_task.html.twig', $args);
  }


  #[Route('/time-difference/{id}', name: 'app_stopwatch_get_time_difference')]
  public function getTimeDifference(StopwatchTime $stopwatch): JsonResponse {
    if (!$stopwatch || !$stopwatch->getStart()) {
      return new JsonResponse(['error' => 'Stopwatch not found or not started'], 404);
    }

    // Izračunaj razliku u vremenu
    $start = $stopwatch->getStart();
    $now = new DateTimeImmutable();
    $interval = $start->diff($now);

    // Ukupni sati računajući i preko 24h
    $totalHours = ($interval->days * 24) + $interval->h;
    $timeDifference = sprintf('%d:%02d', $totalHours, $interval->i);

    return new JsonResponse(['timeDifference' => $timeDifference]);
  }
}