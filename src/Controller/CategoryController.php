<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Classes\ResponseMessages;
use App\Entity\Category;
use App\Form\CategoryFormType;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories')]
class CategoryController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_categories')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }

    $args = [];

    $categories = $this->em->getRepository(Category::class)->getCategoriesPaginator();

    $pagination = $paginator->paginate(
      $categories, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('category/phone/list.html.twig', $args);
    }
    return $this->render('category/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_category_form', defaults: ['id' => 0])]
  #[Entity('category', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Category $category)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $category->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $category->setEditBy($this->getUser());

    $form = $this->createForm(CategoryFormType::class, $category, ['attr' => ['action' => $this->generateUrl('app_category_form', ['id' => $category->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Category::class)->save($category);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_categories');
      }
    }
    $args['form'] = $form->createView();
    $args['category'] = $category;

    return $this->render('category/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_category_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Category $category)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $category->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['category'] = $category;

    return $this->render('category/view.html.twig', $args);
  }

}
