<?php

namespace App\Controller;


use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Image;
use App\Entity\Lamela;
use App\Entity\Projekat;
use App\Entity\Prostorija;
use App\Entity\Sifarnik;
use App\Entity\Sprat;
use App\Entity\Stan;
use App\Entity\User;
use App\Form\LamelaFormType;
use App\Form\ProjekatFormType;
use App\Form\ProstorijaFormType;
use App\Form\ProstorijaMerenjeFormType;
use App\Form\SpratFormType;
use App\Form\StanFormType;
use App\Form\StanSlikaFormType;
use App\Service\UploadService;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/projekti')]
class ProjekatController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_projekats')]
  public function list(PaginatorInterface $paginator, Request $request): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $korisnik = $this->getUser();

    $search = [];
    $search['title'] = $request->query->get('title');

    $projekti = $this->em->getRepository(Projekat::class)->getProjektiByUserPaginator($korisnik, $search);

    $pagination = $paginator->paginate(
      $projekti, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

      $session = new Session();
      $session->set('url', $request->getRequestUri());
      $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();

    if($mobileDetect->isMobile()) {
      return $this->render('projekat/phone/list.html.twig', $args);
    }

    return $this->render('projekat/list.html.twig', $args);
  }

  #[Route('/list-check/', name: 'app_prostorije_check')]
  public function listCheck(PaginatorInterface $paginator, Request $request): Response {
      if (!$this->isGranted('ROLE_USER')) {
          return $this->redirect($this->generateUrl('app_login'));
      }

      $korisnik = $this->getUser();
      $search = [];
      $search['projekat'] = $request->query->get('projekat');
      $projekti = $this->em->getRepository(Prostorija::class)->getProstorijeCheck($search, $korisnik);

      $pagination = $paginator->paginate(
          $projekti, /* query NOT result */
          $request->query->getInt('page', 1), /*page number*/
          15
      );
      $session = new Session();
      $session->set('url', $request->getRequestUri());
      $args['pagination'] = $pagination;
      $args['projekti'] = $this->em->getRepository(Projekat::class)->findBy([],['title' => 'ASC']);
      $mobileDetect = new MobileDetect();
      if($mobileDetect->isMobile()) {
          return $this->render('projekat/phone/prostorije_check_list.html.twig', $args);
      }

      return $this->render('projekat/prostorije_check_list.html.twig', $args);
    }

    #[Route('/list-prostorija/', name: 'app_prostorije_merenje')]
    public function listMerenje(PaginatorInterface $paginator, Request $request): Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        $korisnik = $this->getUser();
        $search = [];
        $search['projekat'] = $request->query->get('projekat');

        $projekti = $this->em->getRepository(Prostorija::class)->getProstorijeMerenje($search, $korisnik);

        $pagination = $paginator->paginate(
            $projekti, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            15
        );

        $session = new Session();
        $session->set('url', $request->getRequestUri());
        $args['pagination'] = $pagination;
        $args['projekti'] = $this->em->getRepository(Projekat::class)->findBy([],['title' => 'ASC']);
        $mobileDetect = new MobileDetect();
        if($mobileDetect->isMobile()) {
            return $this->render('projekat/phone/prostorije_merenje_list.html.twig', $args);
        }

        return $this->render('projekat/prostorije_merenje_list.html.twig', $args);
    }

  #[Route('/form/{id}', name: 'app_projekat_form', defaults: ['id' => 0])]
  #[Entity('projekat', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, Projekat $projekat)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
    return $this->redirect($this->generateUrl('app_login'));
  }

    $korisnik = $this->getUser();
    if (!is_null($projekat->getId())) {
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$projekat->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }
    }

    $form = $this->createForm(ProjekatFormType::class, $projekat, ['attr' => ['action' => $this->generateUrl('app_projekat_form', ['id' => $projekat->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $projekat->setZaposleni($request->get('projekat_form')['obrada']);

        if (is_null($projekat->getId())) {
            $projekat->setStanje('0/0');
            $projekat->setPercent(0);
        }

        $this->em->getRepository(Projekat::class)->save($projekat);

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_projekat_view', ['id' => $projekat->getId()]);

      }
    }
    $args['form'] = $form->createView();
    $args['projekat'] = $projekat;
    $args['zaposleni'] = $this->em->getRepository(User::class)->findBy(['isSuspended' => false, 'userType' => UserRolesData::ROLE_EMPLOYEE], ['prezime' => "ASC"]);
    $args['obrada'] = [];

    if (!is_null($projekat->getZaposleni())) {
        $args['obrada'] = $projekat->getZaposleni();
    }

    return $this->render('projekat/form.html.twig', $args);
  }

  #[Route('/view/{id}', name: 'app_projekat_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Projekat $projekat): Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
        $args = [];
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik, $projekat->getAssigned()->toArray())) {
                if (!in_array($korisnik->getId(), (array)$projekat->getZaposleni())) {
                    return $this->redirect($this->generateUrl('app_home'));
                }
            }
        }
    $args['projekat'] = $projekat;
    $args['lamele'] = $projekat->getLamelas()->toArray();
    $args['obrada'] = [];

    if (!is_null($projekat->getZaposleni())) {
        foreach ($projekat->getZaposleni() as $id) {
            $args['obrada'][] = $this->em->getRepository(User::class)->find($id);
        }
    }

    return $this->render('projekat/view.html.twig', $args);
  }


    #[Route('/form-lamela/{id}', name: 'app_lamela_form', defaults: ['id' => 0])]
    #[Entity('lamela', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function formLamela(Lamela $lamela, Request $request)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $projekat = $this->em->getRepository(Projekat::class)->find($request->get('projekat'));
        $lamela->setProjekat($projekat);

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$lamela->getProjekat()->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }

        $form = $this->createForm(LamelaFormType::class, $lamela, ['attr' => ['action' => $this->generateUrl('app_lamela_form', ['id' => $lamela->getId(), 'projekat' => $projekat->getId()])]]);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {


                if (is_null($lamela->getId())) {
                    $lamela->setStanje('0/0');
                    $lamela->setPercent(0);
                    $lamela->setProjekat($this->em->getRepository(Projekat::class)->find($request->get('projekat')));
                }

                $this->em->getRepository(Lamela::class)->save($lamela);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_lamela_view', ['id' => $lamela->getId()]);

            }
        }
        $args['form'] = $form->createView();
        $args['projekat'] = $projekat;
        $args['lamela'] = $lamela;

        return $this->render('projekat/lamela_form.html.twig', $args);
    }

    #[Route('/edit-lamela/{id}', name: 'app_lamela_edit', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function editLamela(Lamela $lamela, Request $request)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$lamela->getProjekat()->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }

        $form = $this->createForm(LamelaFormType::class, $lamela, ['attr' => ['action' => $this->generateUrl('app_lamela_edit', ['id' => $lamela->getId()])]]);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $this->em->getRepository(Lamela::class)->save($lamela);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_lamela_view', ['id' => $lamela->getId()]);

            }
        }
        $args['form'] = $form->createView();
        $args['lamela'] = $lamela;

        return $this->render('projekat/lamela_edit.html.twig', $args);
    }

    #[Route('/view-lamela/{id}', name: 'app_lamela_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function viewLamela(Lamela $lamela, PaginatorInterface $paginator, Request $request): Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $args = [];
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik, $lamela->getProjekat()->getAssigned()->toArray())) {
                if (!in_array($korisnik->getId(), (array)$lamela->getProjekat()->getZaposleni())) {
                    return $this->redirect($this->generateUrl('app_home'));
                }
            }
        }

        $args['spratovi'] = $lamela->getSprats()->toArray();
        $args['lamela'] = $lamela;

        $mobileDetect = new MobileDetect();

        return $this->render('projekat/lamela_view.html.twig', $args);

    }

    #[Route('/delete-lamela/{id}', name: 'app_lamela_delete', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function deleteLamela(Lamela $lamela, Request $request)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$lamela->getProjekat()->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }

        list($first, $second) = explode('/', $lamela->getStanje());

        if ((int)$first > 0 || (int)$second > 0) {

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->duration(5000)
                ->dismissible(true)
                ->addError(NotifyMessagesData::DELETE_ERROR_PROJEKAT);

            return $this->redirectToRoute('app_projekat_view', ['id' => $lamela->getProjekat()->getId()]);
        } else {

            $projekat = $lamela->getProjekat();
            $projekat->removeLamela($lamela);

            $lamela->setProjekat(null);

//            dd($stan, $sprat->getStans()->toArray());
            $this->em->getRepository(Lamela::class)->remove($lamela);
            $this->em->getRepository(Projekat::class)->save($projekat);

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->duration(5000)
                ->dismissible(true)
                ->addSuccess(NotifyMessagesData::DELETE_SUCCESS);

            return $this->redirectToRoute('app_projekat_view', ['id' => $projekat->getId()]);
        }





    }


    #[Route('/form-sprat/{id}', name: 'app_sprat_form', defaults: ['id' => 0])]
    #[Entity('sprat', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function formSprat(Sprat $sprat, Request $request)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $lamela = $this->em->getRepository(Lamela::class)->find($request->get('lamela'));
        $sprat->setLamela($lamela);

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$sprat->getLamela()->getProjekat()->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }

        $form = $this->createForm(SpratFormType::class, $sprat, ['attr' => ['action' => $this->generateUrl('app_sprat_form', ['id' => $sprat->getId(), 'lamela' => $lamela->getId()])]]);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {


                if (is_null($sprat->getId())) {
                    $sprat->setStanje('0/0');
                    $sprat->setPercent(0);
                    $sprat->setLamela($this->em->getRepository(Lamela::class)->find($request->get('lamela')));
                }

                $this->em->getRepository(Sprat::class)->save($sprat);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_sprat_view', ['id' => $sprat->getId()]);

            }
        }
        $args['form'] = $form->createView();
        $args['lamela'] = $lamela;
        $args['sprat'] = $sprat;
        $deadline = $lamela->getDeadline();
        if (is_null($deadline)) {
            $deadline = $lamela->getProjekat()->getDeadline();
        }
        $args['deadline'] = $deadline;

        return $this->render('projekat/sprat_form.html.twig', $args);
    }

    #[Route('/edit-sprat/{id}', name: 'app_sprat_edit', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function editSprat(Sprat $sprat, Request $request)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$sprat->getLamela()->getProjekat()->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }


        $form = $this->createForm(SpratFormType::class, $sprat, ['attr' => ['action' => $this->generateUrl('app_sprat_edit', ['id' => $sprat->getId()])]]);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $this->em->getRepository(Sprat::class)->save($sprat);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_sprat_view', ['id' => $sprat->getId()]);

            }
        }
        $args['form'] = $form->createView();
        $args['sprat'] = $sprat;
        $deadline = $sprat->getLamela()->getDeadline();
        if (is_null($deadline)) {
            $deadline = $sprat->getLamela()->getProjekat()->getDeadline();
        }
        $args['deadline'] = $deadline;

        return $this->render('projekat/sprat_edit.html.twig', $args);
    }

    #[Route('/view-sprat/{id}', name: 'app_sprat_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function viewSprat(Sprat $sprat, PaginatorInterface $paginator, Request $request): Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $args = [];
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik, $sprat->getLamela()->getProjekat()->getAssigned()->toArray())) {
                if (!in_array($korisnik->getId(), (array)$sprat->getLamela()->getProjekat()->getZaposleni())) {
                    return $this->redirect($this->generateUrl('app_home'));
                }
            }
        }


        $args['sprat'] = $sprat;
        $args['stanovi'] = $sprat->getStans()->toArray();


        return $this->render('projekat/sprat_view.html.twig', $args);

    }

    #[Route('/delete-sprat/{id}', name: 'app_sprat_delete', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function deleteSprat(Sprat $sprat, Request $request)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$sprat->getLamela()->getProjekat()->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }

        list($first, $second) = explode('/', $sprat->getStanje());

        if ((int)$first > 0 || (int)$second > 0) {

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->duration(5000)
                ->dismissible(true)
                ->addError(NotifyMessagesData::DELETE_ERROR_PROJEKAT);

            return $this->redirectToRoute('app_lamela_view', ['id' => $sprat->getLamela()->getId()]);
        } else {

            $lamela = $sprat->getLamela();
            $lamela->removeSprat($sprat);
            $sprat->setLamela(null);

//            dd($stan, $sprat->getStans()->toArray());
            $this->em->getRepository(Sprat::class)->remove($sprat);
            $this->em->getRepository(Lamela::class)->save($lamela);

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->duration(5000)
                ->dismissible(true)
                ->addSuccess(NotifyMessagesData::DELETE_SUCCESS);

            return $this->redirectToRoute('app_lamela_view', ['id' => $lamela->getId()]);
        }





    }

    #[Route('/form-stan/{id}', name: 'app_stan_form', defaults: ['id' => 0])]
    #[Entity('stan', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function formStan(Stan $stan, Request $request, UploadService $uploadService)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $sprat = $this->em->getRepository(Sprat::class)->find($request->get('sprat'));
        $stan->setSprat($sprat);

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$stan->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }

        $sprat = $this->em->getRepository(Sprat::class)->find($request->get('sprat'));

        $form = $this->createForm(StanFormType::class, $stan, ['attr' => ['action' => $this->generateUrl('app_stan_form', ['id' => $stan->getId(), 'sprat' => $sprat->getId()])]]);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $stan->setSprat($this->em->getRepository(Sprat::class)->find($request->get('sprat')));
                $products = $request->get('edit_order_products_form')['product'] ?? [];
                $zidovi = $request->get('zid') ?? [];
                $rezultat = [];
                $i = 0;
//                dd($products, $zidovi);
                foreach ($products as $prostorijaIndex => $productData) {

                    $zidKey = "prostorija_$prostorijaIndex";

                    if (isset($zidovi[$zidKey])) {
                        foreach ($zidovi[$zidKey] as $zid) {
                            $rezultat[$i][$productData['product']][] = [
                                'zid' => $zid['title'] ?? '',
                                'unos' => $zid['unos'] ?? '',
                                'dir' => $zid['dir'] ?? '',
                            ];

                        }
                    }
                    $i++;
                }

                foreach ($rezultat as $key1 => $value1) {
                    foreach ($value1 as $key => $value) {
                        $code = $this->em->getRepository(Sifarnik::class)->find($key);
                        $prostorija = new Prostorija();
                        $prostorija->setStan($stan);
                        $prostorija->setCode($code);
                        $prostorija->setTitle($code->getTitle());
                        $prostorija->setArchive($value);
                        $stan->addProstorija($prostorija);
                    }
                }

                $file = $request->files->all()['stan_form']['image'];
                if (!is_null($file)) {
                    $file = $uploadService->upload($file, $stan->getSprat()->getLamela()->getProjekat()->getUploadPath());
                    $image = $this->em->getRepository(Image::class)->addImage($file, $stan->getSprat()->getLamela()->getProjekat()->getThumbUploadPath(), $this->getParameter('kernel.project_dir'));
                    $stan->setImage($image);
                }

                $stan->setStanje('0/' . count($rezultat));
                $stan->setPercent(0);

                $this->em->getRepository(Stan::class)->save($stan);


                //nivo sprata
                $sprStanje = $sprat->getStanje();

                list($a1, $a2) = explode('/', $sprStanje);
                list($b1, $b2) = explode('/', $stan->getStanje());

                $sum1 = (int)$a1 + (int)$b1;
                $sum2 = (int)$a2 + (int)$b2;

                $sprStanje = "{$sum1}/{$sum2}";
                $sprat->setStanje($sprStanje);
                if ($sum2 > 0) {
                    $sprProcenat = round(($sum1 / $sum2) * 100, 2);
                    $sprat->setPercent($sprProcenat);
                }
                $this->em->getRepository(Sprat::class)->save($sprat);

                //nivo lamele
                $lamela = $sprat->getLamela();
                $lamStanje = $lamela->getStanje();

                list($a1, $a2) = explode('/', $lamStanje);
                list($b1, $b2) = explode('/', $stan->getStanje());

                $sum1 = (int)$a1 + (int)$b1;
                $sum2 = (int)$a2 + (int)$b2;

                $lamStanje = "{$sum1}/{$sum2}";
                $lamela->setStanje($lamStanje);
                if ($sum2 > 0) {
                    $lamProcenat = round(($sum1 / $sum2) * 100, 2);
                    $lamela->setPercent($lamProcenat);
                }
                $this->em->getRepository(Lamela::class)->save($lamela);

                //nivo projekat
                $projekat = $lamela->getProjekat();
                $projStanje = $projekat->getStanje();

                list($a1, $a2) = explode('/', $projStanje);
                list($b1, $b2) = explode('/', $stan->getStanje());

                $sum1 = (int)$a1 + (int)$b1;
                $sum2 = (int)$a2 + (int)$b2;

                $projStanje = "{$sum1}/{$sum2}";
                $projekat->setStanje($projStanje);
                if ($sum2 > 0) {
                    $projProcenat = round(($sum1 / $sum2) * 100, 2);
                    $projekat->setPercent($projProcenat);
                }
                $this->em->getRepository(Projekat::class)->save($projekat);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_stan_view', ['id' => $stan->getId()]);

            }
        }
        $args['form'] = $form->createView();
        $args['sprat'] = $sprat;
        $args['stan'] = $stan;

        return $this->render('projekat/stan_form.html.twig', $args);
    }

    #[Route('/edit-stan/{id}', name: 'app_stan_edit', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function editStan(Stan $stan, Request $request)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$stan->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }

        $form = $this->createForm(StanFormType::class, $stan, ['attr' => ['action' => $this->generateUrl('app_stan_edit', ['id' => $stan->getId()])]]);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $products = $request->get('edit_order_products_form')['product'] ?? [];
                $zidovi = $request->get('zid') ?? [];
                $rezultat = [];

                $i = 0;

                foreach ($products as $prostorijaIndex => $productData) {

                    $zidKey = "prostorija_$prostorijaIndex";

                    if (isset($zidovi[$zidKey])) {
                        foreach ($zidovi[$zidKey] as $zid) {
                            $rezultat[$i][$productData['product']][] = [
                                'zid' => $zid['title'] ?? '',
                                'unos' => $zid['unos'] ?? '',
                                'dir' => $zid['dir'] ?? '',
                            ];

                        }
                    }
                    $i++;
                }

                foreach ($rezultat as $key1 => $value1) {
                    foreach ($value1 as $key => $value) {
                        $code = $this->em->getRepository(Sifarnik::class)->find($key);
                        $prostorija = new Prostorija();
                        $prostorija->setStan($stan);
                        $prostorija->setCode($code);
                        $prostorija->setTitle($code->getTitle());
                        $prostorija->setArchive($value);
                        $stan->addProstorija($prostorija);
                    }
                }

                $stanStanje = $stan->getStanje();

                list($a1, $a2) = explode('/', $stanStanje);
                list($b1, $b2) = explode('/', '0/' . count($rezultat));

                $sum1 = (int)$a1 + (int)$b1;
                $sum2 = (int)$a2 + (int)$b2;

                $stanStanje = "{$sum1}/{$sum2}";
                $stan->setStanje($stanStanje);
                if ($sum2 > 0) {
                    $stanProcenat = round(($sum1 / $sum2) * 100, 2);
                    $stan->setPercent($stanProcenat);
                }

                $this->em->getRepository(Stan::class)->save($stan);


                //nivo sprata
                $sprat = $stan->getSprat();
                $sprStanje = $sprat->getStanje();

                list($a1, $a2) = explode('/', $sprStanje);
                list($b1, $b2) = explode('/', '0/' . count($rezultat));

                $sum1 = (int)$a1 + (int)$b1;
                $sum2 = (int)$a2 + (int)$b2;

                $sprStanje = "{$sum1}/{$sum2}";
                $sprat->setStanje($sprStanje);
                if ($sum2 > 0) {
                    $sprProcenat = round(($sum1 / $sum2) * 100, 2);
                    $sprat->setPercent($sprProcenat);
                }
                $this->em->getRepository(Sprat::class)->save($sprat);

                //nivo lamele
                $lamela = $sprat->getLamela();
                $lamStanje = $lamela->getStanje();

                list($a1, $a2) = explode('/', $lamStanje);
                list($b1, $b2) = explode('/', '0/' . count($rezultat));

                $sum1 = (int)$a1 + (int)$b1;
                $sum2 = (int)$a2 + (int)$b2;

                $lamStanje = "{$sum1}/{$sum2}";
                $lamela->setStanje($lamStanje);
                if ($sum2 > 0) {
                    $lamProcenat = round(($sum1 / $sum2) * 100, 2);
                    $lamela->setPercent($lamProcenat);
                }
                $this->em->getRepository(Lamela::class)->save($lamela);

                //nivo projekat
                $projekat = $lamela->getProjekat();
                $projStanje = $projekat->getStanje();

                list($a1, $a2) = explode('/', $projStanje);
                list($b1, $b2) = explode('/', '0/' . count($rezultat));

                $sum1 = (int)$a1 + (int)$b1;
                $sum2 = (int)$a2 + (int)$b2;

                $projStanje = "{$sum1}/{$sum2}";
                $projekat->setStanje($projStanje);
                if ($sum2 > 0) {
                    $projProcenat = round(($sum1 / $sum2) * 100, 2);
                    $projekat->setPercent($projProcenat);
                }
                $this->em->getRepository(Projekat::class)->save($projekat);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_stan_view', ['id' => $stan->getId()]);

            }
        }
        $args['form'] = $form->createView();
        $args['stan'] = $stan;
        $args['prostorije']= $stan->getProstorijas()->toArray();

        return $this->render('projekat/stan_edit.html.twig', $args);
    }

    #[Route('/slika-stan/{id}', name: 'app_stan_slika', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function slikaStan(Stan $stan, Request $request, UploadService $uploadService)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$stan->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }


        $form = $this->createForm(StanSlikaFormType::class, $stan, ['attr' => ['action' => $this->generateUrl('app_stan_slika', ['id' => $stan->getId()])]]);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $file = $request->files->all()['stan_slika_form']['image'];

                if (!is_null($file)) {
                    $file = $uploadService->upload($file, $stan->getSprat()->getLamela()->getProjekat()->getUploadPath());
                    $image = $this->em->getRepository(Image::class)->addImage($file, $stan->getSprat()->getLamela()->getProjekat()->getThumbUploadPath(), $this->getParameter('kernel.project_dir'));
                    $stan->setImage($image);
                }

                $this->em->getRepository(Stan::class)->save($stan);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_stan_view', ['id' => $stan->getId()]);

            }
        }
        $args['form'] = $form->createView();
        $args['stan'] = $stan;


        return $this->render('projekat/stan_slika.html.twig', $args);
    }

    #[Route('/view-stan/{id}', name: 'app_stan_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function viewStan(stan $stan, PaginatorInterface $paginator, Request $request): Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $args = [];
        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik, $stan->getSprat()->getLamela()->getProjekat()->getAssigned()->toArray())) {
                if (!in_array($korisnik->getId(), (array)$stan->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                    return $this->redirect($this->generateUrl('app_home'));
                }
            }
        }

        $args['stan'] = $stan;
        $prostorije = $stan->getProstorijas()->toArray();
//    $projekti = $this->em->getRepository(Projekat::class)->findAll();

        $pagination = $paginator->paginate(
            $prostorije, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            15
        );

        $args['pagination'] = $pagination;
        $mobileDetect = new MobileDetect();
        if($mobileDetect->isMobile()) {
            return $this->render('projekat/phone/stan_view.html.twig', $args);
        }

        return $this->render('projekat/stan_view.html.twig', $args);

    }

    #[Route('/delete-stan/{id}', name: 'app_stan_delete', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function deleteStan(Stan $stan, Request $request)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$stan->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }

        if ($stan->getProstorijas()->count() > 0) {

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->duration(5000)
                ->dismissible(true)
                ->addError(NotifyMessagesData::DELETE_ERROR_PROJEKAT);

            return $this->redirectToRoute('app_sprat_view', ['id' => $stan->getSprat()->getId()]);
        } else {

            $sprat = $stan->getSprat();
            $sprat->removeStan($stan);
            $stan->setSprat(null);

//            dd($stan, $sprat->getStans()->toArray());
            $this->em->getRepository(Stan::class)->remove($stan);
            $this->em->getRepository(Sprat::class)->save($sprat);

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->duration(5000)
                ->dismissible(true)
                ->addSuccess(NotifyMessagesData::DELETE_SUCCESS);

            return $this->redirectToRoute('app_sprat_view', ['id' => $sprat->getId()]);
        }





    }

    #[Route('/delete-prostorija/{id}', name: 'app_prostorija_delete', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function deleteProstorija(Prostorija $prostorija, Request $request)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik->getId(), (array)$prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }

        $stan = $prostorija->getStan();

        if (!is_null($prostorija->getUnos1()) && !is_null($prostorija->getUnos2()) && !is_null($prostorija->getUnos3())) {
            $stanje = '-1/-1';

        } else {
            $stanje = '0/-1';
        }

        $prosIndex = $prostorija->getArchive();
        [$prosIndex, ] = explode('-', $prosIndex[0]['zid']);
        $proRed = (int)$prosIndex;


        foreach ($stan->getProstorijas()->toArray() as $pro) {
            $zidovi = $pro->getArchive(); // arhiva zidova u formatu array

            if (empty($zidovi)) {
                continue;
            }

            [$roomIndex, ] = explode('-', $zidovi[0]['zid']);

            if ((int)$roomIndex > $proRed) {

                $newRoomIndex = str_pad((string)((int)$roomIndex - 1), 2, '0', STR_PAD_LEFT);

                foreach ($zidovi as &$zid) {
                    [, $wallIndex] = explode('-', $zid['zid']);
                    $zid['zid'] = $newRoomIndex . '-' . $wallIndex;
                }

                $pro->setArchive($zidovi);
                $this->em->getRepository(Prostorija::class)->save($prostorija);
            }
        }

        $stanStanje = $stan->getStanje();

        list($a1, $a2) = explode('/', $stanStanje);
        list($b1, $b2) = explode('/', $stanje);

        $sum1 = (int)$a1 + (int)$b1;
        $sum2 = (int)$a2 + (int)$b2;

        $stanStanje = "{$sum1}/{$sum2}";
        $stan->setStanje($stanStanje);
        if ($sum2 > 0) {
            $stanProcenat = round(($sum1 / $sum2) * 100, 2);
            $stan->setPercent($stanProcenat);
        }



        $stan->removeProstorija($prostorija);
        $this->em->getRepository(Prostorija::class)->remove($prostorija);
        $this->em->getRepository(Stan::class)->save($stan);


                //nivo sprata
                $sprat = $stan->getSprat();
                $sprStanje = $sprat->getStanje();

                list($a1, $a2) = explode('/', $sprStanje);
                list($b1, $b2) = explode('/', $stanje);

                $sum1 = (int)$a1 + (int)$b1;
                $sum2 = (int)$a2 + (int)$b2;

                $sprStanje = "{$sum1}/{$sum2}";
                $sprat->setStanje($sprStanje);
                if ($sum2 > 0) {
                    $sprProcenat = round(($sum1 / $sum2) * 100, 2);
                    $sprat->setPercent($sprProcenat);
                }
                $this->em->getRepository(Sprat::class)->save($sprat);

                //nivo lamele
                $lamela = $sprat->getLamela();
                $lamStanje = $lamela->getStanje();

                list($a1, $a2) = explode('/', $lamStanje);
                list($b1, $b2) = explode('/', $stanje);

                $sum1 = (int)$a1 + (int)$b1;
                $sum2 = (int)$a2 + (int)$b2;

                $lamStanje = "{$sum1}/{$sum2}";
                $lamela->setStanje($lamStanje);
                if ($sum2 > 0) {
                    $lamProcenat = round(($sum1 / $sum2) * 100, 2);
                    $lamela->setPercent($lamProcenat);
                }
                $this->em->getRepository(Lamela::class)->save($lamela);

                //nivo projekat
                $projekat = $lamela->getProjekat();
                $projStanje = $projekat->getStanje();

                list($a1, $a2) = explode('/', $projStanje);
                list($b1, $b2) = explode('/', $stanje);

                $sum1 = (int)$a1 + (int)$b1;
                $sum2 = (int)$a2 + (int)$b2;

                $projStanje = "{$sum1}/{$sum2}";
                $projekat->setStanje($projStanje);
                if ($sum2 > 0) {
                    $projProcenat = round(($sum1 / $sum2) * 100, 2);
                    $projekat->setPercent($projProcenat);
                }
                $this->em->getRepository(Projekat::class)->save($projekat);

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::DELETE_SUCCESS);

                return $this->redirectToRoute('app_stan_view', ['id' => $stan->getId()]);

    }

    #[Route('/edit-prostorija/{id}', name: 'app_prostorija_edit')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function editProstorija(Prostorija $prostorija, Request $request, UploadService $uploadService)    : Response {
      if (!$this->isGranted('ROLE_USER')) {
          return $this->redirect($this->generateUrl('app_login'));
      }
      $korisnik = $this->getUser();
      if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
          if (!in_array($korisnik->getId(), (array)$prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
              return $this->redirect($this->generateUrl('app_home'));
          }
      }


            $form = $this->createForm(ProstorijaFormType::class, $prostorija, ['attr' => ['action' => $this->generateUrl('app_prostorija_edit', ['id' => $prostorija->getId()])]]);
            if ($request->isMethod('POST')) {

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    $prostorija->setArchive($request->get('zid')[0]);
                    $this->em->getRepository(Prostorija::class)->save($prostorija);
                    return $this->redirectToRoute('app_stan_view', ['id' => $prostorija->getStan()->getId()]);

                }
            }
            $args['form'] = $form->createView();
            $args['prostorija'] = $prostorija;
            $args['stan'] = $prostorija->getStan();
            $prosIndex = $prostorija->getArchive();
            [$prosIndex, ] = explode('-', $prosIndex[0]['zid']);
            $proRed = (int)$prosIndex;
            $args['prostorijaRed'] = $proRed;


            return $this->render('projekat/prostorija_edit.html.twig', $args);
        }

    #[Route('/form-prostorija/{id}', name: 'app_prostorija_form', defaults: ['id' => 0])]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function formProstorija(Prostorija $prostorija, Request $request, UploadService $uploadService)    : Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }

        $korisnik = $this->getUser();
        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik, $prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getAssigned()->toArray())) {
                return $this->redirect($this->generateUrl('app_home'));
            }
        }


        $form = $this->createForm(ProstorijaMerenjeFormType::class, $prostorija, ['attr' => ['action' => $this->generateUrl('app_prostorija_form', ['id' => $prostorija->getId()])]]);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                if (is_null($request->get('slobodno'))) {
                    $unos1 = $request->get('zid');
                    $fix = $request->get('fix');
                    $unos2 = [];
                    $isti = true;
                    if (!empty($fix)) {
                        $fixArray = json_decode($fix, true);

                        $i = 0;

                        foreach ($unos1 as $kljuc => $vrednosti) {
                            // Ako nema više vrednosti u fix nizu, prekini
                            if (!isset($fixArray[$i])) {
                                break;
                            }

                            // Zamenjujemo samo merenje
                            $unos2[$kljuc] = [
                                'merenje' => (string) $fixArray[$i],
                                'dir' => $vrednosti['dir'],
                            ];

                            $i++;
                        }
                        foreach ($unos1 as $key => $zid) {
                            if (!isset($unos2[$key])) {
                                $isti = false;
                                break; // prekini jer već nije isti
                            }

                            $staro = number_format((float)$zid['merenje'], 2, '.', '');
                            $novo = number_format((float)$unos2[$key]['merenje'], 2, '.', '');

                            if (bccomp($staro, $novo, 2) !== 0) {
                                $isti = false;
                                break; // nije isto, nema potrebe da nastavljaš
                            }
                        }

                    }
                } else {
                    $fix = $request->get('fix');
                    $custom = $request->get('custom');
                    $unos1 = [];
                    $unos2 = [];
                    $isti = true;
                    $prostorija->setIsCustom(true);
                    foreach ($custom as $stavka) {
                        $zid = $stavka['zid'] ?? null;

                        if ($zid) {
                            $unos1[$zid] = [
                                'merenje' => $stavka['unos'],
                                'dir' => $stavka['dir'],
                            ];
                        }
                    }

                    if (!empty($fix)) {
                        $fixArray = json_decode($fix, true);

                        $i = 0;

                        foreach ($unos1 as $kljuc => $vrednosti) {
                            // Ako nema više vrednosti u fix nizu, prekini
                            if (!isset($fixArray[$i])) {
                                break;
                            }

                            // Zamenjujemo samo merenje
                            $unos2[$kljuc] = [
                                'merenje' => (string) $fixArray[$i],
                                'dir' => $vrednosti['dir'],
                            ];

                            $i++;
                        }
                        foreach ($unos1 as $key => $zid) {
                            if (!isset($unos2[$key])) {
                                $isti = false;
                                break; // prekini jer već nije isti
                            }

                            $staro = number_format((float)$zid['merenje'], 2, '.', '');
                            $novo = number_format((float)$unos2[$key]['merenje'], 2, '.', '');

                            if (bccomp($staro, $novo, 2) !== 0) {
                                $isti = false;
                                break; // nije isto, nema potrebe da nastavljaš
                            }
                        }

                    }

                }


                $d1 = $request->get('d1');
                $d2 = $request->get('d2');
                $d3 = $request->get('d3');
                $d4 = $request->get('d4');

                $d1 = $d1 === '' ? null : $d1;
                $d2 = $d2 === '' ? null : $d2;
                $d3 = $d3 === '' ? null : $d3;
                $d4 = $d4 === '' ? null : $d4;

                $prostorija->setDijagonala1($d1);
                $prostorija->setDijagonala2($d2);
                $prostorija->setDijagonala3($d3);
                $prostorija->setDijagonala4($d4);

                $odstupanje = $request->get('kordinate');
                $prostorija->setOdstupanje($odstupanje);

                if (!empty($fix)) {
                    $x = 0;
                    $y = 0;
                    $koordinate = [];
                    // Generiši sve tačke
                    $koordinate[] = [$x, $y];
                    if ($isti) {

                        foreach ($unos1 as $segment) {
                            $duzina = floatval($segment['merenje']);
                            $dir = intval($segment['dir']);

                            switch ($dir) {
                                case 1: $x += $duzina; break; // X+
                                case 2: $x -= $duzina; break; // X-
                                case 3: $y += $duzina; break; // Y+
                                case 4: $y -= $duzina; break; // Y-
                            }

                            $koordinate[] = [$x, $y];
                        }

                    } else {
                        foreach ($unos2 as $segment) {
                            $duzina = floatval($segment['merenje']);
                            $dir = intval($segment['dir']);

                            switch ($dir) {
                                case 1: $x += $duzina; break; // X+
                                case 2: $x -= $duzina; break; // X-
                                case 3: $y += $duzina; break; // Y+
                                case 4: $y -= $duzina; break; // Y-
                            }

                            $koordinate[] = [$x, $y];
                        }
                    }

                    $n = count($koordinate);
                    $povrsina = 0;

                    for ($i = 0; $i < $n - 1; $i++) {
                        $x1 = $koordinate[$i][0];
                        $y1 = $koordinate[$i][1];
                        $x2 = $koordinate[$i + 1][0];
                        $y2 = $koordinate[$i + 1][1];

                        $povrsina += ($x1 * $y2) - ($x2 * $y1);
                    }

                    $povrsina = round(abs($povrsina / 2), 2);

                    $prostorija->setPovrsina($povrsina);

                    $unosData2 = [
                        'userId' => $korisnik->getId(),
                        'time' => (new \DateTimeImmutable())->format('d.m.Y H:i:s'),
                        'unos' => $unos2,
                    ];


                    $prostorija->setUnos2($unosData2);
                    if (!$isti) {
                      $prostorija->setIsEditAuto(true);
                    }

                } else {
                    $prostorija->setIsEdit(true);
                }

                $unosData1 = [
                    'userId' => $korisnik->getId(),
                    'time' => (new \DateTimeImmutable())->format('d.m.Y H:i:s'),
                    'unos' => $unos1,
                ];

                $prostorija->setUnos1($unosData1);

                $this->em->getRepository(Prostorija::class)->save($prostorija);

                if (!$prostorija->isEdit()) {
                    $stan = $prostorija->getStan();
                    $stanStanje = $stan->getStanje();

                    list($a1, $a2) = explode('/', $stanStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $stanStanje = "{$sum1}/{$sum2}";
                    $stan->setStanje($stanStanje);
                    if ($sum2 > 0) {
                        $stanProcenat = round(($sum1 / $sum2) * 100, 2);
                        $stan->setPercent($stanProcenat);
                    }

                    $this->em->getRepository(Stan::class)->save($stan);


                    //nivo sprata
                    $sprat = $stan->getSprat();
                    $sprStanje = $sprat->getStanje();

                    list($a1, $a2) = explode('/', $sprStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $sprStanje = "{$sum1}/{$sum2}";
                    $sprat->setStanje($sprStanje);
                    if ($sum2 > 0) {
                        $sprProcenat = round(($sum1 / $sum2) * 100, 2);
                        $sprat->setPercent($sprProcenat);
                    }
                    $this->em->getRepository(Sprat::class)->save($sprat);

                    //nivo lamele
                    $lamela = $sprat->getLamela();
                    $lamStanje = $lamela->getStanje();

                    list($a1, $a2) = explode('/', $lamStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $lamStanje = "{$sum1}/{$sum2}";
                    $lamela->setStanje($lamStanje);
                    if ($sum2 > 0) {
                        $lamProcenat = round(($sum1 / $sum2) * 100, 2);
                        $lamela->setPercent($lamProcenat);
                    }
                    $this->em->getRepository(Lamela::class)->save($lamela);

                    //nivo projekat
                    $projekat = $lamela->getProjekat();
                    $projStanje = $projekat->getStanje();

                    list($a1, $a2) = explode('/', $projStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $projStanje = "{$sum1}/{$sum2}";
                    $projekat->setStanje($projStanje);
                    if ($sum2 > 0) {
                        $projProcenat = round(($sum1 / $sum2) * 100, 2);
                        $projekat->setPercent($projProcenat);
                    }
                    $this->em->getRepository(Projekat::class)->save($projekat);
                }

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_stan_view', ['id' => $prostorija->getStan()->getId()]);

            }
        }
        $args['form'] = $form->createView();
        $args['prostorija'] = $prostorija;
        $args['stan'] = $prostorija->getStan();
        $prosIndex = $prostorija->getArchive();
        [$prosIndex, ] = explode('-', $prosIndex[0]['zid']);
        $proRed = (int)$prosIndex;
        $args['prostorijaRed'] = $proRed;


        return $this->render('projekat/prostorija_form.html.twig', $args);
    }

    #[Route('/view-prostorija/{id}', name: 'app_prostorija_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function viewProstorija(Prostorija $prostorija, Request $request): Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $args = [];
        $korisnik = $this->getUser();

        if ($korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            if (!in_array($korisnik, $prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getAssigned()->toArray())) {
                if (!in_array($korisnik->getId(), (array)$prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                    return $this->redirect($this->generateUrl('app_home'));
                }
            }
        }


        $form = $this->createForm(ProstorijaMerenjeFormType::class, $prostorija, ['attr' => ['action' => $this->generateUrl('app_prostorija_view', ['id' => $prostorija->getId()])]]);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                if (is_null($request->get('slobodno'))) {
                    $unos1 = $request->get('zid');
                    $fix = $request->get('fix');
                    $unos2 = [];
                    $isti = true;
                    if (!empty($fix)) {
                        $fixArray = json_decode($fix, true);

                        $i = 0;

                        foreach ($unos1 as $kljuc => $vrednosti) {
                            // Ako nema više vrednosti u fix nizu, prekini
                            if (!isset($fixArray[$i])) {
                                break;
                            }

                            // Zamenjujemo samo merenje
                            $unos2[$kljuc] = [
                                'merenje' => (string) $fixArray[$i],
                                'dir' => $vrednosti['dir'],
                            ];

                            $i++;
                        }
                        foreach ($unos1 as $key => $zid) {
                            if (!isset($unos2[$key])) {
                                $isti = false;
                                break; // prekini jer već nije isti
                            }

                            $staro = number_format((float)$zid['merenje'], 2, '.', '');
                            $novo = number_format((float)$unos2[$key]['merenje'], 2, '.', '');

                            if (bccomp($staro, $novo, 2) !== 0) {
                                $isti = false;
                                break; // nije isto, nema potrebe da nastavljaš
                            }
                        }

                    }
                } else {
                    $fix = $request->get('fix');
                    $custom = $request->get('custom');
                    $unos1 = [];
                    $unos2 = [];
                    $isti = true;
                    $prostorija->setIsCustom(true);
                    foreach ($custom as $stavka) {
                        $zid = $stavka['zid'] ?? null;

                        if ($zid) {
                            $unos1[$zid] = [
                                'merenje' => $stavka['unos'],
                                'dir' => $stavka['dir'],
                            ];
                        }
                    }

                    if (!empty($fix)) {
                        $fixArray = json_decode($fix, true);

                        $i = 0;

                        foreach ($unos1 as $kljuc => $vrednosti) {
                            // Ako nema više vrednosti u fix nizu, prekini
                            if (!isset($fixArray[$i])) {
                                break;
                            }

                            // Zamenjujemo samo merenje
                            $unos2[$kljuc] = [
                                'merenje' => (string) $fixArray[$i],
                                'dir' => $vrednosti['dir'],
                            ];

                            $i++;
                        }
                        foreach ($unos1 as $key => $zid) {
                            if (!isset($unos2[$key])) {
                                $isti = false;
                                break; // prekini jer već nije isti
                            }

                            $staro = number_format((float)$zid['merenje'], 2, '.', '');
                            $novo = number_format((float)$unos2[$key]['merenje'], 2, '.', '');

                            if (bccomp($staro, $novo, 2) !== 0) {
                                $isti = false;
                                break; // nije isto, nema potrebe da nastavljaš
                            }
                        }

                    }

                }


                $d1 = $request->get('d1');
                $d2 = $request->get('d2');
                $d3 = $request->get('d3');
                $d4 = $request->get('d4');

                $d1 = $d1 === '' ? null : $d1;
                $d2 = $d2 === '' ? null : $d2;
                $d3 = $d3 === '' ? null : $d3;
                $d4 = $d4 === '' ? null : $d4;

                $prostorija->setDijagonala1($d1);
                $prostorija->setDijagonala2($d2);
                $prostorija->setDijagonala3($d3);
                $prostorija->setDijagonala4($d4);

                $odstupanje = $request->get('kordinate');
                $prostorija->setOdstupanje($odstupanje);

                if (!empty($fix)) {
                    $x = 0;
                    $y = 0;
                    $koordinate = [];
                    // Generiši sve tačke
                    $koordinate[] = [$x, $y];
                    if ($isti) {

                        foreach ($unos1 as $segment) {
                            $duzina = floatval($segment['merenje']);
                            $dir = intval($segment['dir']);

                            switch ($dir) {
                                case 1: $x += $duzina; break; // X+
                                case 2: $x -= $duzina; break; // X-
                                case 3: $y += $duzina; break; // Y+
                                case 4: $y -= $duzina; break; // Y-
                            }

                            $koordinate[] = [$x, $y];
                        }

                    } else {
                        foreach ($unos2 as $segment) {
                            $duzina = floatval($segment['merenje']);
                            $dir = intval($segment['dir']);

                            switch ($dir) {
                                case 1: $x += $duzina; break; // X+
                                case 2: $x -= $duzina; break; // X-
                                case 3: $y += $duzina; break; // Y+
                                case 4: $y -= $duzina; break; // Y-
                            }

                            $koordinate[] = [$x, $y];
                        }
                    }

                    $n = count($koordinate);
                    $povrsina = 0;

                    for ($i = 0; $i < $n - 1; $i++) {
                        $x1 = $koordinate[$i][0];
                        $y1 = $koordinate[$i][1];
                        $x2 = $koordinate[$i + 1][0];
                        $y2 = $koordinate[$i + 1][1];

                        $povrsina += ($x1 * $y2) - ($x2 * $y1);
                    }

                    $povrsina = round(abs($povrsina / 2), 2);

                    $prostorija->setPovrsina($povrsina);
                    $prostorija->setUnos2($unos2);
                    if (!$isti) {
                        $prostorija->setIsEditAuto(true);
                    }

                } else {
                    $prostorija->setIsEdit(true);
                }


                $prostorija->setUnos1($unos1);

                $this->em->getRepository(Prostorija::class)->save($prostorija);

                if (!$prostorija->isEdit()) {
                    $stan = $prostorija->getStan();
                    $stanStanje = $stan->getStanje();

                    list($a1, $a2) = explode('/', $stanStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $stanStanje = "{$sum1}/{$sum2}";
                    $stan->setStanje($stanStanje);
                    if ($sum2 > 0) {
                        $stanProcenat = round(($sum1 / $sum2) * 100, 2);
                        $stan->setPercent($stanProcenat);
                    }

                    $this->em->getRepository(Stan::class)->save($stan);


                    //nivo sprata
                    $sprat = $stan->getSprat();
                    $sprStanje = $sprat->getStanje();

                    list($a1, $a2) = explode('/', $sprStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $sprStanje = "{$sum1}/{$sum2}";
                    $sprat->setStanje($sprStanje);
                    if ($sum2 > 0) {
                        $sprProcenat = round(($sum1 / $sum2) * 100, 2);
                        $sprat->setPercent($sprProcenat);
                    }
                    $this->em->getRepository(Sprat::class)->save($sprat);

                    //nivo lamele
                    $lamela = $sprat->getLamela();
                    $lamStanje = $lamela->getStanje();

                    list($a1, $a2) = explode('/', $lamStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $lamStanje = "{$sum1}/{$sum2}";
                    $lamela->setStanje($lamStanje);
                    if ($sum2 > 0) {
                        $lamProcenat = round(($sum1 / $sum2) * 100, 2);
                        $lamela->setPercent($lamProcenat);
                    }
                    $this->em->getRepository(Lamela::class)->save($lamela);

                    //nivo projekat
                    $projekat = $lamela->getProjekat();
                    $projStanje = $projekat->getStanje();

                    list($a1, $a2) = explode('/', $projStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $projStanje = "{$sum1}/{$sum2}";
                    $projekat->setStanje($projStanje);
                    if ($sum2 > 0) {
                        $projProcenat = round(($sum1 / $sum2) * 100, 2);
                        $projekat->setPercent($projProcenat);
                    }
                    $this->em->getRepository(Projekat::class)->save($projekat);
                }

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_stan_view', ['id' => $stan->getId()]);

            }
        }
        $args['form'] = $form->createView();
        $args['prostorija'] = $prostorija;

        return $this->render('projekat/prostorija_view.html.twig', $args);

    }

    #[Route('/admin-prostorija/{id}', name: 'app_prostorija_admin')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
    public function adminProstorija(Prostorija $prostorija, Request $request): Response {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('app_login'));
        }
        $args = [];
        $korisnik = $this->getUser();
        if (!in_array($korisnik, $prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getAssigned()->toArray()) && $korisnik->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            return $this->redirect($this->generateUrl('app_home'));
        }
        $form = $this->createForm(ProstorijaMerenjeFormType::class, $prostorija, ['attr' => ['action' => $this->generateUrl('app_prostorija_admin', ['id' => $prostorija->getId()])]]);
        if ($request->isMethod('POST')) {


            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $unos = $request->get('zid');
                $unos3 = [];

                foreach ($unos as $key => $value) {
                    // Uklanja ključ 'zid' ako postoji
                    unset($value['zid']);
                    $unos3[$key] = $value;
                }


                    $x = 0;
                    $y = 0;
                    $koordinate = [];
                    // Generiši sve tačke
                    $koordinate[] = [$x, $y];

                    foreach ($unos3 as $segment) {
                        $duzina = floatval($segment['merenje']);
                        $dir = intval($segment['dir']);

                        switch ($dir) {
                            case 1: $x += $duzina; break; // X+
                            case 2: $x -= $duzina; break; // X-
                            case 3: $y += $duzina; break; // Y+
                            case 4: $y -= $duzina; break; // Y-
                        }
                        $koordinate[] = [$x, $y];
                    }

                    $n = count($koordinate);
                    $povrsina = 0;

                    for ($i = 0; $i < $n - 1; $i++) {
                        $x1 = $koordinate[$i][0];
                        $y1 = $koordinate[$i][1];
                        $x2 = $koordinate[$i + 1][0];
                        $y2 = $koordinate[$i + 1][1];

                        $povrsina += ($x1 * $y2) - ($x2 * $y1);
                    }

                    $povrsina = round(abs($povrsina / 2), 2);

                    $prostorija->setPovrsina($povrsina);


                $unosData = [
                    'userId' => $korisnik->getId(),
                    'time' => (new \DateTimeImmutable())->format('d.m.Y H:i:s'),
                    'unos' => $unos3,
                ];



                $prostorija->setUnos2($unosData);
                    $prostorija->setUnos3($unosData);

                    $prostorija->setIsEditManual(true);

                    $prostorija->setIsEdit(false);



                $this->em->getRepository(Prostorija::class)->save($prostorija);

                if (!$prostorija->isEdit()) {
                    $stan = $prostorija->getStan();
                    $stanStanje = $stan->getStanje();

                    list($a1, $a2) = explode('/', $stanStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $stanStanje = "{$sum1}/{$sum2}";
                    $stan->setStanje($stanStanje);
                    if ($sum2 > 0) {
                        $stanProcenat = round(($sum1 / $sum2) * 100, 2);
                        $stan->setPercent($stanProcenat);
                    }

                    $this->em->getRepository(Stan::class)->save($stan);


                    //nivo sprata
                    $sprat = $stan->getSprat();
                    $sprStanje = $sprat->getStanje();

                    list($a1, $a2) = explode('/', $sprStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $sprStanje = "{$sum1}/{$sum2}";
                    $sprat->setStanje($sprStanje);
                    if ($sum2 > 0) {
                        $sprProcenat = round(($sum1 / $sum2) * 100, 2);
                        $sprat->setPercent($sprProcenat);
                    }
                    $this->em->getRepository(Sprat::class)->save($sprat);

                    //nivo lamele
                    $lamela = $sprat->getLamela();
                    $lamStanje = $lamela->getStanje();

                    list($a1, $a2) = explode('/', $lamStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $lamStanje = "{$sum1}/{$sum2}";
                    $lamela->setStanje($lamStanje);
                    if ($sum2 > 0) {
                        $lamProcenat = round(($sum1 / $sum2) * 100, 2);
                        $lamela->setPercent($lamProcenat);
                    }
                    $this->em->getRepository(Lamela::class)->save($lamela);

                    //nivo projekat
                    $projekat = $lamela->getProjekat();
                    $projStanje = $projekat->getStanje();

                    list($a1, $a2) = explode('/', $projStanje);
                    list($b1, $b2) = explode('/', '1/0');

                    $sum1 = (int)$a1 + (int)$b1;
                    $sum2 = (int)$a2 + (int)$b2;

                    $projStanje = "{$sum1}/{$sum2}";
                    $projekat->setStanje($projStanje);
                    if ($sum2 > 0) {
                        $projProcenat = round(($sum1 / $sum2) * 100, 2);
                        $projekat->setPercent($projProcenat);
                    }
                    $this->em->getRepository(Projekat::class)->save($projekat);
                }

                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->duration(5000)
                    ->dismissible(true)
                    ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

                return $this->redirectToRoute('app_stan_view', ['id' => $stan->getId()]);

            }
        }
        $args['form'] = $form->createView();


        $args['prostorija'] = $prostorija;


        return $this->render('projekat/prostorija_admin.html.twig', $args);

    }

}
