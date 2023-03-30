<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\ResponseMessages;
use App\Entity\Category;
use App\Form\CategoryFormType;
use Doctrine\Persistence\ManagerRegistry;
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
  public function list(): Response {
    $args = [];
    $args['categories'] = $this->em->getRepository(Category::class)->findAll();

    return $this->render('category/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_category_form', defaults: ['id' => 0])]
  #[Entity('category', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Category $category): Response {
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
  public function view(Category $category): Response {
    $args['category'] = $category;

    return $this->render('category/view.html.twig', $args);
  }

}
