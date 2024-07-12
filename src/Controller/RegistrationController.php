<?php

namespace App\Controller;

use App\Classes\Avatar;
use App\Classes\Data\UserRolesData;
use App\Entity\Company;
use App\Entity\Project;
use App\Entity\Settings;
use App\Entity\User;
use App\Entity\ZaposleniPozicija;
use App\Form\RegistrationFormType;
use App\Service\UploadService;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController {

  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/registration/{id}', name: 'app_registration_form', defaults: ['id' => 0])]
  #[Entity('usr', expr: 'repository.findForFormRegistration(id)')]
//  #[Security("is_granted('USER_EDIT', usr)", message: 'Nemas pristup', statusCode: 403)]
  public function form(Request $request, User $usr, UploadService $uploadService)    : Response {

    if ($this->isGranted('ROLE_USER')) {
      return $this->redirect($this->generateUrl('app_home'));
    }

//    $korisnik = $this->getUser();
//    if ($korisnik->getUserType() != UserRolesData::ROLE_SUPER_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_ADMIN && $korisnik->getUserType() != UserRolesData::ROLE_MANAGER) {
//      return $this->redirect($this->generateUrl('app_home'));
//    }
//
//    if ($korisnik->getCompany() != $usr->getCompany()) {
//      return $this->redirect($this->generateUrl('app_home'));
//    }

    $usr->setPlainUserType(UserRolesData::ROLE_ADMIN);

    $form = $this->createForm(RegistrationFormType::class, $usr, ['attr' => ['action' => $this->generateUrl('app_registration_form', ['id' => $usr->getId()])]]);
    if ($request->isMethod('POST')) {
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $firma = new Company();
        $firma->setPib($request->request->all('firma')['pib']);
        $firma->setTitle($request->request->all('firma')['title']);
        $firma->setEmail($usr->getEmail());
        $firma = $this->em->getRepository(Company::class)->register($firma);
        $usr->setCompany($firma);
        $usr->setUserType(UserRolesData::ROLE_ADMIN);

        $settings = new Settings();
        $settings->setCompany($firma);
        $settings->setTitle($firma->getTitle());
        $settings->setRadnoVreme(new DateTimeImmutable('16:00'));
        $this->em->getRepository(Settings::class)->save($settings);


        $file = Avatar::getAvatar($this->getParameter('kernel.project_dir') . $usr->getAvatarUploadPath(), $usr);
//
//        dd($usr, $request->request->all('firma'), $file);

        $user = $this->em->getRepository(User::class)->registerNew($usr, $settings,  $file, $this->getParameter('kernel.project_dir'));
        $firma->setCreatedBy($user);
        $this->em->getRepository(Company::class)->saveCompany($firma);


        $project = new Project();
        $project->setCompany($firma);
        $project->setCreatedBy($user);
        $project->setTitle('Osnovni projekat');
        $this->em->getRepository(Project::class)->saveProject($project, $user, null);

        $position = new ZaposleniPozicija();
        $position->setCompany($firma);
        $position->setTitle('Radnik');
        $position->setTitleShort('RDN');
        $this->em->getRepository(ZaposleniPozicija::class)->save($position);

//        notyf()
//          ->position('x', 'right')
//          ->position('y', 'top')
//          ->duration(5000)
//          ->dismissible(true)
//          ->addSuccess(NotifyMessagesData::REGISTRATION_USER_SUCCESS);

        return $this->redirectToRoute('app_home');

      }
    }
    $args['form'] = $form->createView();

    return $this->render('registration/form.html.twig', $args);
  }

}
