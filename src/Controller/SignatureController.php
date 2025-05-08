<?php

namespace App\Controller;

use App\Classes\Data\ColorsData;
use App\Classes\Data\NotifyMessagesData;
use App\Classes\Data\UserRolesData;
use App\Entity\Image;
use App\Entity\Label;
use App\Entity\Project;
use App\Entity\Signature;
use App\Form\LabelFormType;
use App\Form\SignatureFormType;
use App\Service\MailService;
use App\Service\UploadService;
use Detection\MobileDetect;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/signatures')]
class SignatureController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/list/', name: 'app_signatures')]
  public function list(PaginatorInterface $paginator, Request $request)    : Response {
    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }


    $args = [];
    $user = $this->getUser();

    $labels = $this->em->getRepository(Signature::class)->getSignaturesPaginator($user);

    $pagination = $paginator->paginate(
      $labels, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      15
    );

    $args['pagination'] = $pagination;
    $mobileDetect = new MobileDetect();
    if($mobileDetect->isMobile()) {
      return $this->render('signature/phone/list.html.twig', $args);
    }
    return $this->render('signature/list.html.twig', $args);
  }

  #[Route('/form/{id}', name: 'app_signature_form', defaults: ['id' => 0])]
  #[Entity('signature', expr: 'repository.findForForm(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Signature $signature, UploadService $uploadService, Request $request, MailService $mailService): Response {

    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    if (is_null($signature->getId())) {
      $project = $request->query->get('project') !== null ? $request->query->get('project') : 0;
      if ($this->getUser()->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
        $signature->setEmployee($this->getUser());
      }
      if ($project > 0) {
        $signature->setRelation($this->em->getRepository(Project::class)->find($project));
      }

      $oldSignature = $this->em->getRepository(Signature::class)->findOneBy(['employee' => $signature->getEmployee(), 'relation' => $signature->getRelation()]);
      if (!is_null($oldSignature)) {
        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addError(NotifyMessagesData::SIGNATURE_ERROR);

        return $this->redirectToRoute('app_home');
      }
    }


    $form = $this->createForm(SignatureFormType::class, $signature, ['attr' => ['action' => $this->generateUrl('app_signature_form', ['id' => $signature->getId()])]]);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {


        $oldSignature = $this->em->getRepository(Signature::class)->findOneBy(['employee' => $signature->getEmployee(), 'relation' => $signature->getRelation()]);
        if (!is_null($oldSignature)) {
          notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->duration(5000)
            ->dismissible(true)
            ->addError(NotifyMessagesData::SIGNATURE_ERROR);

          return $this->redirectToRoute('app_signatures');
        }


        $uploadImage = $request->files->all()['signature_form']['image'];

        if (!is_null($uploadImage)) {
            $path = $signature->getRelation()->getUploadPath();
            $pathThumb = $signature->getRelation()->getThumbUploadPath();

            $file = $uploadService->upload($uploadImage, $path);
            $image = $this->em->getRepository(Image::class)->add($file, $pathThumb, $this->getParameter('kernel.project_dir'));
            $signature->setImage($image);
        }

        $this->em->getRepository(Signature::class)->save($signature);

        $mailService->signature($signature, 'marceta.pars@gmail.com');

        notyf()
          ->position('x', 'right')
          ->position('y', 'top')
          ->duration(5000)
          ->dismissible(true)
          ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

        return $this->redirectToRoute('app_signatures');
      }
    }
    $args['form'] = $form->createView();
    $args['signature'] = $signature;

    return $this->render('signature/form.html.twig', $args);
  }
//
  #[Route('/view/{id}', name: 'app_signature_view')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function view(Signature $signature) : Response {

    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $args['signature'] = $signature;

    return $this->render('signature/view.html.twig', $args);
  }

  #[Route('/approve/{id}', name: 'app_signature_approve')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function approve(Signature $signature, MailService $mailService)    : Response {

    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }

    $signature->setIsApproved(true);

    $this->em->getRepository(Signature::class)->save($signature);

    $mailService->signatureApprove($signature);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::EDIT_SUCCESS);

    return $this->redirectToRoute('app_signature_view', ['id' => $signature->getId()]);
  }

  #[Route('/delete/{id}', name: 'app_signature_delete')]
//  #[Security("is_granted('USER_VIEW', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function delete(Signature $signature) : Response {

    if (!$this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_login'));
    }
    $this->em->getRepository(Signature::class)->remove($signature);

    notyf()
      ->position('x', 'right')
      ->position('y', 'top')
      ->duration(5000)
      ->dismissible(true)
      ->addSuccess(NotifyMessagesData::DELETE_SUCCESS);

    return $this->redirectToRoute('app_signatures');
  }

}
