<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\TipOpremeData;
use App\Entity\City;
use App\Entity\Country;
use App\Form\CityFormType;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/cities')]
class CityController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_cities')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $search = [];

    $search['title'] = $request->query->get('title');
    $search['drzava'] = $request->query->get('drzava');
    $search['grad'] = $request->query->get('grad');
    $search['ptt'] = $request->query->get('ptt');

    $cities = $this->em->getRepository(City::class)->getCitiesFilterPaginator($search);

    $pagination = $paginator->paginate(
      $cities, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $session = new Session();
    $session->set('url', $request->getRequestUri());

    $args['pagination'] = $pagination;
    $args['countries'] = $this->em->getRepository(Country::class)->findAll();

    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('city/phone/list.html.twig', $args);
    }

    return $this->render('city/list.html.twig', $args);
  }


  #[Route('/form/{id}', name: 'app_city_form', defaults: ['id' => 0])]
  #[Entity('city', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, City $city)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $city->setEditBy($this->getUser());

    $form = $this->createForm(CityFormType::class, $city, ['attr' => ['action' => $this->generateUrl('app_city_form', ['id' => $city->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        $region = $request->request->get('region_other');
        $opstina = $request->request->get('municipality_other');

         if (!empty($region) && !empty($opstina)) {
          $city->setRegion($region);
          $city->setMunicipality($opstina);
         }

        $this->em->getRepository(City::class)->save($city);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_cities');
      }
    }
    $args['form'] = $form->createView();
    $args['city'] = $city;
    $args['regions'] = $this->em->getRepository(City::class)->getRegionsSerbia();
    $args['municipalities'] = $this->em->getRepository(City::class)->getMunicipalitiesSerbia();

    return $this->render('city/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_city_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(City $city)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['city'] = $city;

    return $this->render('city/view.html.twig', $args);
  }

}