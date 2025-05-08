<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Company;
use App\Entity\Image;
use App\Entity\Survey;
use App\Entity\Vote;
use App\Form\CompanyFormType;
use App\Service\UploadService;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/surveys')]
class SurveyController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/anketa', name: 'app_anketa_form')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request)    : Response {

    $anketa = new Survey();
    $anketa->setTitle('Proslava Nove godine 2024');
    $this->em->getRepository(Survey::class)->save($anketa);

    return $this->redirect($this->generateUrl('app_home'));

  }


  #[Route('/form/{id}', name: 'app_survey_form', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function vote(Request $request, Survey $survey)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();

    if ($request->isMethod('POST')) {

      $data = $request->request->all();
      $vote = $this->em->getRepository(Vote::class)->findOneBy(['user'=>$korisnik, 'survey' => $survey]);

      if (is_null($vote)) {
        $vote = new Vote();
        $vote->setSurvey($survey);
        $vote->setUser($korisnik);
        $vote->setValue1($data['attendance']);
        $vote->setValue2($data['secret_santa']);
        $this->em->getRepository(Vote::class)->save($vote);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::VERIFY_SURVEY_ADD);

        return $this->redirectToRoute('app_home');
      }

      notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->duration(5000)
        ->dismissible(true)
        ->addError(NotifyMessagesData::VERIFY_SURVEY_ERROR);

      return $this->redirectToRoute('app_home');
    }


    return $this->render('company/form.html.twig');
  }

  #[Route('/view', name: 'app_survey_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewProfile()    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }


    $args['survey'] = $this->em->getRepository(Survey::class)->findOneBy([],['id' => 'DESC']);
    $votes = $args['survey']->getVote()->toArray();

    $result = [
      'total_votes' => 0,
      'value1' => [
        '0' => ['count' => 0, 'users' => []],
        '1' => ['count' => 0, 'users' => []],
        '2' => ['count' => 0, 'users' => []],
      ],
      'value2' => [
        '0' => ['count' => 0, 'users' => []],
        '1' => ['count' => 0, 'users' => []],
      ],
    ];

    // Prolazak kroz svaki glas
    foreach ($votes as $vote) {
      $result['total_votes']++;

      // Prikupljanje podataka za value1
      $value1 = (string)$vote->getValue1();
      if (isset($result['value1'][$value1])) {
        $result['value1'][$value1]['count']++;
        $result['value1'][$value1]['users'][] = $vote->getUser(); // ili bilo koja identifikacija korisnika
      }

      // Prikupljanje podataka za value2
      $value2 = (string)$vote->getValue2();
      if (isset($result['value2'][$value2])) {
        $result['value2'][$value2]['count']++;
        $result['value2'][$value2]['users'][] = $vote->getUser(); // ili bilo koja identifikacija korisnika
      }
    }
    $args['vote'] = $result;

    return $this->render('survey/view.html.twig', $args);
  }

}
