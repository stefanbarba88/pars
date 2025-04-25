<?php

namespace App\Controller;

use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Classes\ResponseMessages;
use App\Entity\Category;
use App\Entity\Deo;
use App\Entity\Element;
use App\Entity\ManagerChecklist;
use App\Entity\Product;
use App\Entity\Production;
use App\Entity\Project;
use App\Entity\Sastavnica;
use App\Entity\Task;
use App\Form\CategoryFormType;
use App\Form\ElementFormType;
use App\Form\NalogFormType;
use App\Form\ProductFormType;
use DateTimeImmutable;
use Detection\MobileDetect;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/production')]
class ProductionController extends AbstractController {
    private $knpSnappyPdf;
    public function __construct(private readonly ManagerRegistry $em, Pdf $knpSnappyPdf) {
        $this->knpSnappyPdf = $knpSnappyPdf;
    }

  //elementi
  #[Route('/list-elements/', name: 'app_production_elements')]
  public function listElements(PaginatorInterface $paginator, Request $request)    : Response {
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
    $search = [];

    $search['key'] = $request->query->get('key');
    $search['title'] = $request->query->get('title');


    $categories = $this->em->getRepository(Element::class)->getElementsPaginator($korisnik->getCompany(), $search);

    $pagination = $paginator->paginate(
      $categories, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('production/phone/element_list.html.twig', $args);
    }
    return $this->render('production/element_list.html.twig', $args);
  }

  #[Route('/form-element/{id}', name: 'app_element_form', defaults: ['id' => 0])]
  #[Entity('element', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function formElement(Request $request, Element $element)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $element->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }


    $form = $this->createForm(ElementFormType::class, $element, ['attr' => ['action' => $this->generateUrl('app_element_form', ['id' => $element->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $this->em->getRepository(Element::class)->save($element);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_production_elements');
      }
    }
    $args['form'] = $form->createView();
    $args['element'] = $element;

    return $this->render('production/element_form.html.twig', $args);
  }

  #[Route('/view-element/{id}', name: 'app_element_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function viewElement(Element $element)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $korisnik = $this->getUser();
    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
      if (!$korisnik->isAdmin()) {
        return $this->redirect($this->generateUrl('app_home'));
      }
    }
    if ($korisnik->getCompany() != $element->getCompany()) {
      return $this->redirect($this->generateUrl('app_home'));
    }
    $args['element'] = $element;

    return $this->render('production/element_view.html.twig', $args);
  }

  #[Route('/delete-element/{id}', name: 'app_element_delete')]
//#[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function deleteElement(Element $element)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
            if (!$korisnik->isAdmin()) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }
        if ($korisnik->getCompany() != $element->getCompany()) {
            return $this->redirect($this->generateUrl('app_home'));
        }

        if ($element->isSuspended()) {
            $element->setIsSuspended(false);
        } else {
            $element->setIsSuspended(true);
        }
        $this->em->getRepository(Element::class)->save($element);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_production_elements');

    }

//products
#[Route('/list-products/', name: 'app_production_products')]
public function listSastavnice(PaginatorInterface $paginator, Request $request)    : Response {
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
        $search = [];

        $search['productKey'] = $request->query->get('key');
        $search['title'] = $request->query->get('title');
        $now = new DateTimeImmutable('first day of this month');
        $nextMonth = $now->modify('first day of next month');

        $args['chart'] = $this->em->getRepository(Product::class)->getChartData($korisnik->getCompany(), $now, $nextMonth);
        $args['chart1'] = $this->em->getRepository(Product::class)->getChartData($korisnik->getCompany(), $korisnik->getCompany()->getCreated(), $nextMonth);


        $categories = $this->em->getRepository(Product::class)->getProductsPaginator($korisnik->getCompany(), $search);

        $pagination = $paginator->paginate(
            $categories, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            15
        );

        $args['pagination'] = $pagination;
        $mobileDetect = new MobileDetect();
        if($mobileDetect->isMobile()) {
            return $this->render('production/phone/product_list.html.twig', $args);
        }
        return $this->render('production/product_list.html.twig', $args);
    }


#[Route('/get-elements-by-name', name: 'get_elements_by_name', methods: 'GET')]
public function searchElements(Request $request, EntityManagerInterface $em, Packages $assetsManager): JsonResponse {

        $term = $request->query->get('q', '');
        $products = $em->getRepository(Element::class)->searchByTerm($term);

        $results = [];
        foreach ($products as $product) {

            $results[] = [
                'id' => $product['id'],
                'text' => $product['title'],
                'sku' => $product['productKey'],
            ];
        }

        return new JsonResponse(['results' => $results]);
    }
    #[Route('/form-product/{id}', name: 'app_product_form', defaults: ['id' => 0])]
    #[Entity('product', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function formProduct(Request $request, Product $product)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
            if (!$korisnik->isAdmin()) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }
        if ($korisnik->getCompany() != $product->getCompany()) {
            return $this->redirect($this->generateUrl('app_home'));
        }


        $form = $this->createForm(ProductFormType::class, $product, ['attr' => ['action' => $this->generateUrl('app_product_form', ['id' => $product->getId()])]]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $seenProducts = [];
                $result = [];

                foreach ($request->request->all('edit_order_products_form')['product'] as $item) {
                    if (!in_array($item['product'], $seenProducts)) {
                        $result[] = $item;
                        $seenProducts[] = $item['product'];
                    }
                }

                foreach ($result as $prod) {

                    $dbProduct = $this->em->getRepository(Element::class)->find($prod['product']);
                    $sastavnica = new Sastavnica();
                    $sastavnica->setElement($dbProduct);
                    $sastavnica->setSastav($prod['ppdv']);
//                  $this->em->getRepository(Product::class)->save($dbProduct);
                    $product->addSastavnica($sastavnica);
                }


                $this->em->getRepository(Product::class)->save($product);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_production_products');
            }
        }
        $args['form'] = $form->createView();
        $args['product'] = $product;

        return $this->render('production/product_form.html.twig', $args);
    }

    #[Route('/edit-product/{id}', name: 'app_product_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function editProduct(Request $request, Product $product)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
            if (!$korisnik->isAdmin()) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }
        if ($korisnik->getCompany() != $product->getCompany()) {
            return $this->redirect($this->generateUrl('app_home'));
        }


        $form = $this->createForm(ProductFormType::class, $product, ['attr' => ['action' => $this->generateUrl('app_product_edit', ['id' => $product->getId()])]]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                foreach ($product->getSastavnica() as $sastavnica) {
                    $product->removeSastavnica($sastavnica);
                    $this->em->getRepository(Sastavnica::class)->remove($sastavnica, true);
                }

                $seenProducts = [];
                $result = [];

                foreach ($request->request->all('edit_order_products_form')['product'] as $item) {
                    if (!in_array($item['product'], $seenProducts)) {
                        $result[] = $item;
                        $seenProducts[] = $item['product'];
                    }
                }

                foreach ($result as $prod) {

                    $dbProduct = $this->em->getRepository(Element::class)->find($prod['product']);
                    $sastavnica = new Sastavnica();
                    $sastavnica->setElement($dbProduct);
                    $sastavnica->setSastav($prod['ppdv']);
//                  $this->em->getRepository(Product::class)->save($dbProduct);
                    $product->addSastavnica($sastavnica);
                }


                $this->em->getRepository(Product::class)->save($product);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_production_products');
            }
        }
        $args['form'] = $form->createView();
        $args['product'] = $product;

        return $this->render('production/product_edit.html.twig', $args);
    }

    #[Route('/view-product/{id}', name: 'app_product_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function viewProduct(Product $product)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
            if (!$korisnik->isAdmin()) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }
        if ($korisnik->getCompany() != $product->getCompany()) {
            return $this->redirect($this->generateUrl('app_home'));
        }
        $args['product'] = $product;

        return $this->render('production/product_view.html.twig', $args);
    }

    #[Route('/delete-product/{id}', name: 'app_product_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function deleteProduct(product $product)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
            if (!$korisnik->isAdmin()) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }
        if ($korisnik->getCompany() != $product->getCompany()) {
            return $this->redirect($this->generateUrl('app_home'));
        }

        if ($product->isSuspended()) {
            $product->setIsSuspended(false);
        } else {
            $product->setIsSuspended(true);
        }
        $this->em->getRepository(Product::class)->save($product);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_production_products');

    }

    #[Route('/list-productions/', name: 'app_productions')]
    public function listProductions(PaginatorInterface $paginator, Request $request)    : Response {
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
        $search = [];
        $search['key'] = $request->query->get('nalogKey');
//        $search['title'] = $request->query->get('title');


        $categories = $this->em->getRepository(Production::class)->getProductionsPaginator($korisnik->getCompany(), $search);

        $pagination = $paginator->paginate(
            $categories, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            15
        );

        $args['pagination'] = $pagination;
        $mobileDetect = new MobileDetect();
        if($mobileDetect->isMobile()) {
            return $this->render('production/phone/nalog_list.html.twig', $args);
        }
        return $this->render('production/nalog_list.html.twig', $args);
    }


    #[Route('/get-products-by-name', name: 'get_products_by_name', methods: 'GET')]
    public function searchProducts(Request $request, EntityManagerInterface $em, Packages $assetsManager): JsonResponse {

        $term = $request->query->get('q', '');
        $products = $em->getRepository(Product::class)->searchByTerm($term);

        $results = [];
        foreach ($products as $product) {

            $results[] = [
                'id' => $product->getId(),
                'text' => $product->getTitle(),
                'sku' => $product->getProductKey(),
                'brojclanova' => $product->getSastavnica()->count(),
            ];
        }

        return new JsonResponse(['results' => $results]);
    }
    #[Route('/form-production/{id}', name: 'app_production_form', defaults: ['id' => 0])]
    #[Entity('production', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function formProduction(Request $request, Production $production)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
            if (!$korisnik->isAdmin()) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }
        if ($korisnik->getCompany() != $production->getCompany()) {
            return $this->redirect($this->generateUrl('app_home'));
        }

        if (!is_null($request->get('project'))) {
            $args['project'] = $this->em->getRepository(Project::class)->find($request->get('project'));
            $production->setProject($args['project']);
        } else {
            $args['project'] = null;
        }

        $form = $this->createForm(NalogFormType::class, $production, ['attr' => ['action' => $this->generateUrl('app_production_form', ['id' => $production->getId()])]]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $seenProducts = [];
                $result = [];

                foreach ($request->request->all('edit_order_products_form')['product'] as $item) {
                    if (!in_array($item['product'], $seenProducts)) {
                        $result[] = $item;
                        $seenProducts[] = $item['product'];
                    }
                }

                $arhiva = [];
                $progres = [];
                $kol = 0;
                foreach ($result as $prod) {

                    $dbProduct = $this->em->getRepository(Product::class)->find($prod['product']);
                    $sastavnica = new Deo();
                    $sastavnica->setProduct($dbProduct);
                    $sastavnica->setKolicina($prod['ppdv']);

                    $progress = [];
                    $archive = [];
                    foreach ($dbProduct->getSastavnica() as $item) {
                        $archive[] = [
                            'product' => $prod['product'],
                            'id' => $item->getId(),
                            'text' => $item->getElement()->getTitle(),
                            'sku' => $item->getElement()->getProductKey(),
                            'sastav' => $item->getSastav(),
                            'kolicina' => $prod['ppdv'],
                        ];

                        $progress[] = [
                            'product' => $prod['product'],
                            'productName' => $item->getProduct()->getTitle(),
                            'id' => $item->getId(),
                            'text' => $item->getElement()->getTitle(),
                            'sku' => $item->getElement()->getProductKey(),
                            'sastav' => $item->getSastav(),
                            'kolicina' => $prod['ppdv'] * $item->getSastav(),
                            'kolicinaTren' => 0,
                        ];
                        $kol = $kol + ($prod['ppdv'] * $item->getSastav());
                    }

                    $sastavnica->setArchive($archive);

//                  $this->em->getRepository(Product::class)->save($dbProduct);
                    $production->addDeo($sastavnica);
                    $arhiva[] = $archive;
                    $progres[] = $progress;
                }

                $arhiva = [
                    'datum' => new \DateTimeImmutable(),
                    'arhiva' => $arhiva,
                ];

                $progres = [
                    'datum' => new \DateTimeImmutable(),
                    'progres' => $progres,
                    'percent' => 0,
                    'kolicina' => $kol,
                    'kolicinaTren' => 0,
                ];


                $production->setArchive($arhiva);
                $production->setProgres($progres);
                $this->em->getRepository(Production::class)->save($production);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_project_productions_view', ['id' => $production->getProject()->getId()]);
            }
        }
        $args['form'] = $form->createView();
        $args['production'] = $production;
        $args['productKey'] = $this->em->getRepository(Production::class)->generateProductKey();

        return $this->render('production/nalog_form.html.twig', $args);
    }

    #[Route('/edit-production/{id}', name: 'app_production_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function editProduction(Request $request, Production $production)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
            if (!$korisnik->isAdmin()) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }
        if ($korisnik->getCompany() != $production->getCompany()) {
            return $this->redirect($this->generateUrl('app_home'));
        }


        $form = $this->createForm(NalogFormType::class, $production, ['attr' => ['action' => $this->generateUrl('app_production_edit', ['id' => $production->getId()])]]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                foreach ($production->getDeo() as $sastavnica) {
                    $production->removeDeo($sastavnica);
                    $this->em->getRepository(Deo::class)->remove($sastavnica, true);
                }

                $seenProducts = [];
                $result = [];

                foreach ($request->request->all('edit_order_products_form')['product'] as $item) {
                    if (!in_array($item['product'], $seenProducts)) {
                        $result[] = $item;
                        $seenProducts[] = $item['product'];
                    }
                }

                $arhivaDb = $production->getArchive();
                foreach ($result as $prod) {

                    $dbProduct = $this->em->getRepository(Product::class)->find($prod['product']);
                    $sastavnica = new Deo();
                    $sastavnica->setProduct($dbProduct);
                    $sastavnica->setKolicina($prod['ppdv']);


                    foreach ($dbProduct->getSastavnica() as $item) {
                        $archive[] = [
                            'product' => $prod['product'],
                            'id' => $item->getId(),
                            'text' => $item->getElement()->getTitle(),
                            'sku' => $item->getElement()->getProductKey(),
                            'sastav' => $item->getSastav(),
                            'kolicina' => $prod['ppdv'],
                        ];
                    }

                    $sastavnica->setArchive($archive);

//                  $this->em->getRepository(Product::class)->save($dbProduct);
                    $production->addDeo($sastavnica);
                    $arhiva[] = $archive;
                }

                $arhivaDb[] = [
                    'datum' => new \DateTimeImmutable(),
                    'arhiva' => $arhiva,
                ];
                $production->setArchive($arhivaDb);


                $this->em->getRepository(Production::class)->save($production);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_productions');
            }
        }
        $args['form'] = $form->createView();
        $args['production'] = $production;

        return $this->render('production/nalog_edit.html.twig', $args);
    }

    #[Route('/view-production/{id}', name: 'app_production_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function viewProduction(Production $production, PaginatorInterface $paginator, Request $request, SessionInterface $session)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $korisnik = $this->getUser();

        $args['admin'] = false;
        if ($session->has('admin')) {
            $args['admin'] = true;
        }

        if ($korisnik->getCompany() != $production->getCompany()) {
            return $this->redirect($this->generateUrl('app_home'));
        }


        $tasks = $this->em->getRepository(Task::class)->getTasksByProductionPaginator($production);


        $pagination = $paginator->paginate(
            $tasks, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5
        );

        $args['pagination'] = $pagination;
        $args['production'] = $production;
        $args['progres'] = $this->em->getRepository(Production::class)->ocistiProgres($production->getProgres());

        $arhiva = $production->getArchive();

            if (isset($arhiva['product'])) {
                $args['archive'] = $arhiva;
            } else {
                $args['archive'] = end($arhiva);
            }

        $mobileDetect = new MobileDetect();
        if($mobileDetect->isMobile()) {
            return $this->render('production/phone/nalog_view.html.twig', $args);
        }
        return $this->render('production/nalog_view.html.twig', $args);

    }


    #[Route('/view-production-pdf/{id}', name: 'app_production_view_pdf')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function viewProductionPdf(Production $production, PaginatorInterface $paginator, Request $request, SessionInterface $session)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $korisnik = $this->getUser();

        if ($korisnik->getCompany() != $production->getCompany()) {
            return $this->redirect($this->generateUrl('app_home'));
        }


        $args['tasks'] = $production->getTasks()->toArray();
        $args['company'] = $production->getCompany();
        $args['production'] = $production;
        $args['progres'] = $this->em->getRepository(Production::class)->ocistiProgres($production->getProgres());

        $arhiva = $production->getArchive();

        if (isset($arhiva['product'])) {
            $args['archive'] = $arhiva;
        } else {
            $args['archive'] = end($arhiva);
        }

        $html = $this->renderView('production/pdf.html.twig', $args);

        $pdfContent = $this->knpSnappyPdf->getOutputFromHtml($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="#' . $args['production']->getProductKey() . '.pdf"',
        ]);

    }

    #[Route('/delete-production/{id}', name: 'app_production_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function deleteProduction(Production $production)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN) {
            if (!$korisnik->isAdmin()) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }
        if ($korisnik->getCompany() != $production->getCompany()) {
            return $this->redirect($this->generateUrl('app_home'));
        }

        if ($production->isSuspended()) {
            $production->setIsSuspended(false);
        } else {
            $production->setIsSuspended(true);
        }
        $this->em->getRepository(Production::class)->save($production);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_productions');

    }

    #[Route('/reports', name: 'app_production_reports')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function formReport(Request $request)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        if ($request->isMethod('POST')) {

            $data = $request->request->all();


            $args['reports'] = $this->em->getRepository(Project::class)->getReportProduction($data['report_form']);


                $brojElemenata = isset($args['reports'][2]) ? array_sum(array_map('count', $args['reports'][2])) : 0;

                // Sabiranje vremena iz drugog podniza
                $ukupnoMinuta = 0;
                $brojVremeR = 0;

                foreach ($args['reports'][1] as $podniz) {
                    if (isset($podniz['vremeR'])) {
                        $brojVremeR++;
                        list($sati, $minuti) = explode(':', $podniz['vremeR']);
                        $ukupnoMinuta += (int)$sati * 60 + (int)$minuti;
                    }
                }

                // Računanje proseka u minutima
                $prosekMinuta = $brojVremeR > 0 ? intdiv($ukupnoMinuta, $brojVremeR) : 0;

                // Konvertovanje minuta u sate i minute za ukupno vreme i prosek
                $ukupnoSati = intdiv($ukupnoMinuta, 60);
                $ukupnoOstatakMinuta = $ukupnoMinuta % 60;

                $prosekSati = intdiv($prosekMinuta, 60);
                $prosekOstatakMinuta = $prosekMinuta % 60;

                // Povratni rezultat
                $args['details'] = [
                    'broj_elemenata' => $brojElemenata,
                    'ukupno_vreme' => sprintf('%02d:%02d', $ukupnoSati, $ukupnoOstatakMinuta),
                    'prosek_vreme' => sprintf('%02d:%02d', $prosekSati, $prosekOstatakMinuta),
                ];





            $args['period'] = $data['report_form']['period'];

            if (isset($data['report_form']['datum'])){
                $args['datum'] = 1;
            }
            if (isset($data['report_form']['opis'])){
                $args['opis'] = 1;
            }
            if (isset($data['report_form']['klijent'])){
                $args['klijent'] = 1;
            }
            if (isset($data['report_form']['start'])){
                $args['start'] = 1;
            }
            if (isset($data['report_form']['stop'])){
                $args['stop'] = 1;
            }
            if (isset($data['report_form']['razlika'])){
                $args['razlika'] = 1;
            }
            if (isset($data['report_form']['razlikaz'])){
                $args['razlikaz'] = 1;
            }
            if (isset($data['report_form']['ukupno'])){
                $args['ukupno'] = 1;
            }
            if (isset($data['report_form']['ukupnoz'])){
                $args['ukupnoz'] = 1;
            }
            if (isset($data['report_form']['zaduzeni'])){
                $args['zaduzeni'] = 1;
            }
            if (isset($data['report_form']['napomena'])){
                $args['napomena'] = 1;
            }
            if (isset($data['report_form']['checklist'])){
                $args['checklist'] = 1;
            }

//            if (empty($data['report_form']['project'])) {
//                return $this->render('report_project/view_all.html.twig', $args);
//            }

            $args['dataPdf'] = $data;

//            if (isset($data['report_form']['zatvoren'])) {
//                return $this->render('report_project/view.html.twig', $args);
//            }

            return $this->render('report_production/view_other.html.twig', $args);

        }

        $args = [];

        $args['productions'] = $this->em->getRepository(Production::class)->findBy(['company' => $this->getUser()->getCompany()], ['id' => 'DESC']);

        return $this->render('report_production/control.html.twig', $args);
    }

    #[Route('/reports-pdf', name: 'app_production_reports_pdf')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function formReportPdf(Request $request)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        $data = $request->query->all()['data'];

        $args['production'] = $this->em->getRepository(Production::class)->find($request->query->all()['data']['report_form']['production']);

        $args['reports'] = $this->em->getRepository(Project::class)->getReportProduction($data['report_form']);

        $args['period'] = $request->query->all()['data']['report_form']['period'];

        if (isset($request->query->all()['data']['report_form']['datum'])){
            $args['datum'] = 1;
        }
        if (isset($request->query->all()['data']['report_form']['opis'])){
            $args['opis'] = 1;
        }
        if (isset($request->query->all()['data']['report_form']['klijent'])){
            $args['klijent'] = 1;
        }
        if (isset($request->query->all()['data']['report_form']['start'])){
            $args['start'] = 1;
        }
        if (isset($request->query->all()['data']['report_form']['stop'])){
            $args['stop'] = 1;
        }
        if (isset($request->query->all()['data']['report_form']['razlika'])){
            $args['razlika'] = 1;
        }
        if (isset($request->query->all()['data']['report_form']['razlikaz'])){
            $args['razlikaz'] = 1;
        }
        if (isset($request->query->all()['data']['report_form']['ukupno'])){
            $args['ukupno'] = 1;
        }
        if (isset($request->query->all()['data']['report_form']['ukupnoz'])){
            $args['ukupnoz'] = 1;
        }
        if (isset($request->query->all()['data']['report_form']['zaduzeni'])){
            $args['zaduzeni'] = 1;
        }
        if (isset($request->query->all()['data']['report_form']['napomena'])){
            $args['napomena'] = 1;
        }
        if (isset($request->query->all()['data']['report_form']['checklist'])){
            $args['checklist'] = 1;
        }



        $args['dataPdf'] = $request->query->all()['data']['report_form'];
        $args['company'] = $this->getUser()->getCompany();


        $html = $this->renderView('report_production/pdf_other.html.twig', $args);

        $pdfContent = $this->knpSnappyPdf->getOutputFromHtml($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="activity_' . $args['period'] . '.pdf"',
        ]);

    }

}
