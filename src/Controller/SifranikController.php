<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\ResponseMessages;
use App\Entity\Category;
use App\Entity\Sifarnik;
use App\Form\CategoryFormType;
use App\Form\CodeFormType;
use Detection\MobileDetect;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/codes')]
class SifranikController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_codes')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args = [];
      $search = [];

      $search['title'] = $request->query->get('title');
      $search['short'] = $request->query->get('short');

    $codes = $this->em->getRepository(Sifarnik::class)->getAllCodesPaginator($search);

    $pagination = $paginator->paginate(
      $codes, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

      $session = new Session();
      $session->set('url', $request->getRequestUri());
    $args['pagination'] = $pagination;


      $mobileDetect = new MobileDetect();
      if($mobileDetect->isMobile()) {
          return $this->render('code/phone/list.html.twig', $args);
      }

    return $this->render('code/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_code_form', defaults: ['id' => 0])]
  #[Entity('code', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Sifarnik $code)    : Response {
      if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }


    $form = $this->createForm(CodeFormType::class, $code, ['attr' => ['action' => $this->generateUrl('app_code_form', ['id' => $code->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Sifarnik::class)->save($code);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_codes');
      }
    }
    $args['form'] = $form->createView();
    $args['code'] = $code;

    return $this->render('code/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_code_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Sifarnik $code)    : Response {
      if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['code'] = $code;

    return $this->render('code/view.html.twig', $args);
  }

  #[Route('/get-prostorije-by-name', name: 'get_prostorije_by_name', methods: 'GET')]
  public function searchProstorije(Request $request, EntityManagerInterface $em, Packages $assetsManager): JsonResponse {

        $term = $request->query->get('q', '');
        $products = $em->getRepository(Sifarnik::class)->searchByTerm($term);

        $results = [];
        foreach ($products as $product) {

            $results[] = [
                'id' => $product->getId(),
                'text' => $product->getTitle(),
                'struktura' => $product->getStruktura(),
                'short' => $product->getTitleShort(),
            ];
        }

        return new JsonResponse(['results' => $results]);
    }

}
