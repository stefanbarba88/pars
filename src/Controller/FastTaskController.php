<?php

namespace App\Controller;

use App\Classes\Data\FastTaskData;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\TipProjektaData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\Car;
use App\Entity\Category;
use App\Entity\FastTask;
use App\Entity\Plan;
use App\Entity\PripremaZadatak;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\Tool;
use App\Entity\User;
use App\Service\MailService;
use DateInterval;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[Route('/quick-tasks')]
class
FastTaskController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }
  #[Route('/list', name: 'app_quick_tasks')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isPlan()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];

    $fastTasks = $this->em->getRepository(Plan::class)->getAllPlansPaginator();

    $pagination = $paginator->paginate(
      $fastTasks, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );


    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('fast_task/phone/list.html.twig', $args);
    }

    return $this->render('fast_task/list.html.twig', $args);
  }

  #[Route('/form-quick-date/', name: 'app_quick_tasks_form_date')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formDate(Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isPlan()) {
      return $this->redirect($this->generateUrl('app_login'));
    }


    if ($request->isMethod('POST')) {

      $data = $request->request->all();
      $args['datum'] = $data['task_quick_form_datum'];

      return $this->redirectToRoute('app_quick_tasks_form', $args);

    }

    $args = [];

    $args['disabledDates'] = $this->em->getRepository(Plan::class)->getDisabledDates();


    return $this->render('fast_task/form_date.html.twig', $args);

  }

  #[Route('/form-quick/{id}', name: 'app_quick_tasks_form', defaults: ['id' => 0])]
  #[Entity('plan', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Plan $plan, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isPlan()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    if ($request->get('datum') !== null) {
      $datum = $request->get('datum');
    }


    if ($request->isMethod('POST')) {

      $data = $request->request->all('zadatak');
      $dataInterni = $request->request->all('zadatakI');

      $datePlan = $request->request->get('task_quick_form_datum');
      $datumKreiranja = DateTimeImmutable::createFromFormat('d.m.Y', $datePlan);
      $plan->setDatumKreiranja($datumKreiranja->setTime(7,0));

      $plan = $this->em->getRepository(Plan::class)->savePlan($plan, $data, $dataInterni);

      if (is_null($plan)) {
        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addError(NotifyMessagesData::PLAN_ERROR);
      } else {
        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::PLAN_ADD);
      }

      return $this->redirectToRoute('app_quick_tasks');

    }

    $args = [];
    $datumCheck = DateTimeImmutable::createFromFormat('d.m.Y', $datum);


//    $args['users'] = $this->em->getRepository(User::class)->getUsersCarsAvailable($datum);
//    $args['drivers'] = $this->em->getRepository(User::class)->getUsersCarsAvailable($datum);
    $args['users'] = $this->em->getRepository(User::class)->getUsersCarAvailable($datumCheck);
    $args['activities'] = $this->em->getRepository(Activity::class)->getActivities();
    $args['projects'] = $this->em->getRepository(Project::class)->findBy(['isSuspended' => false, 'company' => $this->getUser()->getCompany()], ['title' => 'ASC']);
    $args['categories'] = $this->em->getRepository(Category::class)->getCategoriesTask();
    $args['cars'] = $this->em->getRepository(Car::class)->findBy(['isSuspended' => false, 'company' => $this->getUser()->getCompany()]);
    $args['tools'] = $this->em->getRepository(Tool::class)->findBy(['isSuspended' => false, 'company' => $this->getUser()->getCompany()]);
    $args['disabledDates'] = $this->em->getRepository(Plan::class)->getDisabledDates();
    $args['datum'] = $datum;

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('fast_task/phone/form.html.twig', $args);
    }
    return $this->render('fast_task/form.html.twig', $args);

  }

  #[Route('/edit-quick/{id}', name: 'app_quick_task_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function edit(Plan $plan, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isPlan()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    if ($request->isMethod('POST')) {

      $data = $request->request->all('zadatak');
      $dataInterni = $request->request->all('zadatakI');

      $plan = $this->em->getRepository(Plan::class)->editPlan($plan, $data, $dataInterni);

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::PLAN_ADD);

      return $this->redirectToRoute('app_quick_tasks');

    }

    $args = [];

    $args['users'] = $this->em->getRepository(User::class)->getUsersCarAvailable($plan->getDatumKreiranja());
    $args['activities'] = $this->em->getRepository(Activity::class)->getActivities();
    $args['projects'] = $this->em->getRepository(Project::class)->findBy(['isSuspended' => false, 'company' => $this->getUser()->getCompany()], ['title' => 'ASC']);
    $args['categories'] = $this->em->getRepository(Category::class)->getCategoriesTask();
    $args['cars'] = $this->em->getRepository(Car::class)->findBy(['isSuspended' => false, 'company' => $this->getUser()->getCompany()]);
    $args['tools'] = $this->em->getRepository(Tool::class)->findBy(['isSuspended' => false, 'company' => $this->getUser()->getCompany()]);


    $args['plan'] = $plan;
    $args['tasks'] = $this->em->getRepository(PripremaZadatak::class)->findBy(['plan' => $plan, 'taskType' => 1]);
    $args['tasksI'] = $this->em->getRepository(PripremaZadatak::class)->findBy(['plan' => $plan, 'taskType' => 2]);

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('fast_task/phone/edit.html.twig', $args);
    }

    return $this->render('fast_task/edit.html.twig', $args);

  }

  #[Route('/view/{id}', name: 'app_quick_tasks_view')]
  public function view(Plan $plan)    : Response {

    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isPlan()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $args = [];
    $args['plan'] = $plan;

    return $this->render('fast_task/view.html.twig', $args);
  }

  #[Route('/create-tasks/{id}', name: 'app_create_tasks')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function createTasks(Plan $plan, MailService $mail, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')|| !$this->getUser()->getCompany()->getSettings()->isPlan()) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $user = $this->getUser();
    $this->em->getRepository(Task::class)->createTasksFromPlan($plan);

//    $args['timetable'] = $this->em->getRepository(FastTask::class)->getTimetableByFastTasks($fastTask);
//    $args['datum']= $fastTask->getDatum();
//    $args['users']= $this->em->getRepository(FastTask::class)->getUsersForEmail($fastTask);
//    $mail->plan($args['timetable'], $args['users'], $args['datum']);

    return $this->redirectToRoute('app_tasks');

  }

  #[Route('/delete/{id}', name: 'app_quick_tasks_delete')]
  public function delete(Plan $plan)    : Response {

    if (!$this->isGranted('ROLE_USER') || !$this->getUser()->getCompany()->getSettings()->isPlan()) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    if ($plan->getStatus() == FastTaskData::FINAL) {
      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addError(NotifyMessagesData::PLAN_ERROR_DELETE);
      return $this->redirectToRoute('app_quick_tasks');

    } else {
      $this->em->getRepository(Plan::class)->remove($plan);
      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addSuccess(NotifyMessagesData::PLAN_DELETE);
    }




    return $this->redirectToRoute('app_quick_tasks');
  }

  #[Route('/email-timetable/{id}', name: 'app_email_timetable')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function emailTimetable(FastTask $fastTask, MailService $mail, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

      $datum = $fastTask->getDatum();
      $timetable = $this->em->getRepository(FastTask::class)->getTimetableByFastTasks($fastTask);
      $subs = $this->em->getRepository(FastTask::class)->getSubsByFastTasks($fastTask);

      $users = $this->em->getRepository(FastTask::class)->getUsersForEmail($fastTask, FastTaskData::SAVED);
      $usersSub = $this->em->getRepository(FastTask::class)->getUsersSubsForEmail($fastTask, FastTaskData::SAVED);

      $mail->plan($timetable, $users, $datum);
      $mail->subs($subs, $usersSub, $datum);

    return $this->redirectToRoute('app_quick_tasks');

  }
//  #[Route('/save-plan/', name: 'app_save_plan')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function saveTimetable(Request $request, MailService $mail) {
//
//
//    $plan = $this->em->getRepository(FastTask::class)->findOneBy(['status' => FastTaskData::OPEN],['datum' => 'ASC']);
//    if(!is_null($plan)) {
//
//      $plan->setStatus(FastTaskData::SAVED);
//      $plan = $this->em->getRepository(FastTask::class)->save($plan);
//
//      $timetable = $this->em->getRepository(FastTask::class)->getTimetableByFastTasks($plan);
//
//      $datum = $plan->getDatum();
//      $users= $this->em->getRepository(FastTask::class)->getUsersForEmail($plan, FastTaskData::OPEN);
//
//      $mail->plan($timetable, $users, $datum);
//    }
//  }

//  #[Route('/edit-plan/', name: 'app_edit_plan')]
////  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
//  public function editTimetable(Request $request, MailService $mail) {
//
//    $plan = $this->em->getRepository(FastTask::class)->findOneBy(['status' => FastTaskData::EDIT],['datum' => 'ASC']);
//    if (!is_null($plan)) {
//      $timetable = $this->em->getRepository(FastTask::class)->getTimetableByFastTasks($plan);
//      $datum = $plan->getDatum();
//      $users= $this->em->getRepository(FastTask::class)->getUsersForEmail($plan, FastTaskData::EDIT);
//      $mail->plan($timetable, $users, $datum);
//    }
//  }

//  #[Route('/final-plan/', name: 'app_final_tasks')]
//
//  public function createTasksByTimetable(MailService $mail, Request $request) {
//
//    $plan = $this->em->getRepository(FastTask::class)->getTimeTableId(new DateTimeImmutable());
//
////    $plan = $this->em->getRepository(FastTask::class)->getTimeTableId(DateTimeImmutable::createFromFormat('d.m.Y H:i:s', '31.8.2023 15:00:00'));
//
//    if ($plan != 0) {
//      $fastTask = $this->em->getRepository(FastTask::class)->find($plan);
//      $fastTask->setStatus(FastTaskData::FINAL);
//      $fastTask = $this->em->getRepository(FastTask::class)->save($fastTask);
//
//      $this->em->getRepository(Task::class)->createTasksFromList($fastTask, $this->em->getRepository(User::class)->find(1));
//
//      $args['timetable'] = $this->em->getRepository(FastTask::class)->getTimetableByFastTasks($fastTask);
//      $args['datum']= $fastTask->getDatum();
//      $args['users']= $this->em->getRepository(FastTask::class)->getUsersForEmail($fastTask, FastTaskData::FINAL);
//      $mail->plan($args['timetable'], $args['users'], $args['datum']);
//    }
//
//  }
}
