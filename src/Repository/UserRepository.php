<?php

namespace App\Repository;

use App\Classes\AppConfig;
use App\Classes\Data\TipProjektaData;
use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaZaposlenjaData;
use App\Classes\DTO\UploadedFileDTO;
use App\Entity\Availability;
use App\Entity\Car;
use App\Entity\Category;
use App\Entity\Company;
use App\Entity\Image;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\ProjectHistory;
use App\Entity\Settings;
use App\Entity\StopwatchTime;
use App\Entity\TaskLog;
use App\Entity\User;
use App\Entity\UserHistory;
use App\Entity\Vacation;
use App\Service\MailService;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface {

  private UserPasswordHasherInterface $passwordHasher;
  private MailService $mail;
  private Security $security;

  public function __construct(ManagerRegistry $registry, UserPasswordHasherInterface $passwordHasher, MailService $mail, Security $security) {
    parent::__construct($registry, User::class);
    $this->passwordHasher = $passwordHasher;
    $this->mail = $mail;
    $this->security = $security;
  }

  public function register(User $user, UploadedFileDTO $file, string $kernelPath): User {

    $image = $this->getEntityManager()->getRepository(Image::class)->addImage($file, $user->getThumbUploadPath(), $kernelPath);
    $user->setImage($image);


    if ($user->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
      $user->setPozicija(null);
    }


    if (!empty($user->getPlainPassword())) {
      $this->hashPlainPassword($user);
    }

    if (is_null($user->getId())) {
      $this->getEntityManager()->persist($user);
    }

    $this->getEntityManager()->flush();

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      $vacation = new Vacation();
      $vacation->setCompany($user->getCompany());
      $vacation->setUser($user);
      $this->getEntityManager()->getRepository(Vacation::class)->save($vacation);
//      $user->setVacation($vacation);

    }

    return $user;
  }

  public function registerNew(User $user, Settings $settings, UploadedFileDTO $file, string $kernelPath): User {

    $image = $this->getEntityManager()->getRepository(Image::class)->addImage($file, $user->getThumbUploadPath(), $kernelPath);
    $user->setImage($image);

    if ($user->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
      $user->setPozicija(null);
    }

    if (!empty($user->getPlainPassword())) {
      $this->hashPlainPassword($user);
    }

    if (is_null($user->getId())) {
      $this->getEntityManager()->persist($user);
    }

    $this->getEntityManager()->flush();



    if($settings->getIsClientView()) {
      $this->mail->registration($user);
    } else {
      if ($user->getUserType() != UserRolesData::ROLE_CLIENT) {
        $this->mail->registration($user);
      }
    }

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      $vacation = new Vacation();
      $vacation->setCompany($user->getCompany());
      $vacation->setUser($user);
      $this->getEntityManager()->getRepository(Vacation::class)->save($vacation);
//      $user->setVacation($vacation);

    }

    return $user;
  }

  public function saveHistory(User $user, ?string $history): User {
    $historyUser = new UserHistory();
    $historyUser->setHistory($history);

    $user->addUserHistory($historyUser);

    return $user;
  }

  public function save(User $user, ?string $history = null): User {

    if (!is_null($history)) {
      $this->saveHistory($user, $history);
    }

    if ($user->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
      $user->setPozicija(null);
    }

    if (!is_null($user->getId())) {
      $user->setEditBy($this->security->getUser());
    }

    if (!empty($user->getPlainPassword())) {
      $this->hashPlainPassword($user);
      if ($user->getUserType() != UserRolesData::ROLE_CLIENT) {
        $this->mail->edit($user);
      }
    }

    if (is_null($user->getId())) {
      $this->getEntityManager()->persist($user);
    }

    $this->getEntityManager()->flush();
    return $user;
  }


  public function hashPlainPassword(User $user): User {
    $hashedPassword = $this->passwordHasher->hashPassword(
      $user,
      $user->getPlainPassword()
    );
    $user->setPassword($hashedPassword);
    return $user;
  }

  public function remove(User $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function suspend(User $user, ?string $history): User {
    if ($user->isSuspended()) {
      $this->mail->deactivate($user);
    } else {
      $this->mail->activate($user);
    }
    $this->save($user, $history);

    return $user;

  }
  public function suspendKadrovksa(User $user): User {
    if ($user->isSuspended()) {
      $this->mail->deactivate($user);
    } else {
      $this->mail->activate($user);
    }
    $this->save($user);

    return $user;

  }
    /**
   * Used to upgrade (rehash) the user's password automatically over time.
   */
  public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void {
    if (!$user instanceof User) {
      throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
    }

    $user->setPassword($newHashedPassword);

    $this->save($user, true);
  }

  public function findForForm(int $id = 0): User {
    if (empty($id)) {
      $user = new User();
      $user->setCreatedBy($this->security->getUser());
      $user->setCompany($this->security->getUser()->getCompany());
      if($this->security->getUser()->isKadrovska()) {
        $user->setIsKadrovska(true);
        $user->setUserType(UserRolesData::ROLE_MANAGER);
      }
      return $user;
    }
    return $this->getEntityManager()->getRepository(User::class)->find($id);
  }

  public function findForFormEmployee(int $id = 0): User {
    if (empty($id)) {
      $user = new User();
      $user->setCreatedBy($this->security->getUser());
      if($this->security->getUser()->isKadrovska()) {
        $user->setIsKadrovska(true);
        $user->setUserType(UserRolesData::ROLE_EMPLOYEE);
      }
      return $user;
    }
    return $this->getEntityManager()->getRepository(User::class)->find($id);
  }

  public function findForFormRegistration(int $id = 0): User {
    if (empty($id)) {
      return new User();
    }
    return $this->getEntityManager()->getRepository(User::class)->find($id);
  }

  public function getAllByLoggedUser(User $loggedUser): array {

    $company = $loggedUser->getCompany();

    $users = match ($loggedUser->getUserType()) {
      UserRolesData::ROLE_SUPER_ADMIN => $this->getEntityManager()->getRepository(User::class)->findBy([], ['isSuspended' => 'ASC', 'userType' => 'ASC']),
      UserRolesData::ROLE_ADMIN => $this->createQueryBuilder('u')
        ->andWhere('u.userType <> :userType')
        ->andWhere('u.userType <> :userType1')
        ->andWhere('u.company = :company')
        ->setParameter(':company', $company)
        ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
        ->setParameter(':userType1', UserRolesData::ROLE_ADMIN)
        ->addOrderBy('u.isSuspended', 'ASC')
        ->addOrderBy('u.userType', 'ASC')
        ->getQuery()
        ->getResult(),
      default => $this->createQueryBuilder('u')
        ->andWhere('u.userType <> :userType')
        ->andWhere('u.userType <> :userType1')
        ->andWhere('u.userType <> :userType2')
        ->andWhere('u.company = :company')
        ->setParameter(':company', $company)
        ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
        ->setParameter(':userType1', UserRolesData::ROLE_ADMIN)
        ->setParameter(':userType2', UserRolesData::ROLE_MANAGER)
        ->addOrderBy('u.isSuspended', 'ASC')
        ->addOrderBy('u.userType', 'ASC')
        ->getQuery()
        ->getResult(),
    };

    $usersList = [];
    foreach ($users as $user) {

      $usersList [] = [
        'id' => $user->getId(),
        'ime' => $user->getIme(),
        'prezime' => $user->getPrezime(),
        'slika' => $user->getImage(),
        'isSuspended' => $user->getBadgeByStatus(),
        'datumRodjenja' => $user->getDatumRodjenja(),
        'role' => $user->getBadgeByUserType(),
      ];
    }

    return $usersList;
  }

  public function getAllByLoggedUserPaginator(User $loggedUser, $filter, $suspended) {

    $company = $loggedUser->getCompany();
    $qb = $this->createQueryBuilder('u');

    if ($loggedUser->getUserType() == UserRolesData::ROLE_SUPER_ADMIN) {
      $qb->where('u.company = :company')
        ->setParameter(':company', $company)
        ->andWhere('u.isSuspended = :suspenzija')
        ->setParameter('suspenzija', $suspended);

      if (!empty($filter['ime'])) {
      $qb->andWhere($qb->expr()->orX(
          $qb->expr()->like('u.ime', ':ime'),
        ))
          ->setParameter('ime', '%' . $filter['ime'] . '%');
      }
      if (!empty($filter['prezime'])) {
        $qb->andWhere($qb->expr()->orX(
          $qb->expr()->like('u.prezime', ':prezime'),
        ))
          ->setParameter('prezime', '%' . $filter['prezime'] . '%');
      }
      if (!empty($filter['pozicija'])) {
        $qb->andWhere('u.pozicija = :pozicija');
        $qb->setParameter('pozicija', $filter['pozicija']);
      }
      if (!empty($filter['vrsta'])) {
        $qb->andWhere('u.userType = :vrsta');
        $qb->setParameter('vrsta', $filter['vrsta']);
      }

      $qb
        ->addOrderBy('u.userType', 'ASC')
        ->addOrderBy('u.prezime', 'ASC')
        ->getQuery();
    }

    if ($loggedUser->getUserType() == UserRolesData::ROLE_ADMIN) {
      $qb->where('u.company = :company')
        ->setParameter(':company', $company)
        ->andWhere('u.isSuspended = :suspenzija')
        ->setParameter('suspenzija', $suspended)
        ->andWhere('u.userType <> :userType')
        ->andWhere('u.userType <> :userType1')
        ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
        ->setParameter(':userType1', UserRolesData::ROLE_ADMIN);

      if (!empty($filter['ime'])) {
        $qb->andWhere($qb->expr()->orX(
          $qb->expr()->like('u.ime', ':ime'),
        ))
          ->setParameter('ime', '%' . $filter['ime'] . '%');
      }
      if (!empty($filter['prezime'])) {
        $qb->andWhere($qb->expr()->orX(
          $qb->expr()->like('u.prezime', ':prezime'),
        ))
          ->setParameter('prezime', '%' . $filter['prezime'] . '%');
      }
      if (!empty($filter['pozicija'])) {
        $qb->andWhere('u.pozicija = :pozicija');
        $qb->setParameter('pozicija', $filter['pozicija']);
      }
      if (!empty($filter['vrsta'])) {
        $qb->andWhere('u.userType = :vrsta');
        $qb->setParameter('vrsta', $filter['vrsta']);
      }

      $qb
        ->addOrderBy('u.userType', 'ASC')
        ->addOrderBy('u.prezime', 'ASC')
        ->getQuery();
    }

    if ($loggedUser->getUserType() == UserRolesData::ROLE_MANAGER) {
      $qb->where('u.company = :company')
        ->setParameter(':company', $company)
        ->andWhere('u.isSuspended = :suspenzija')
        ->setParameter('suspenzija', $suspended)
        ->andWhere('u.userType <> :userType')
        ->andWhere('u.userType <> :userType1')
        ->andWhere('u.userType <> :userType2')
        ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
        ->setParameter(':userType1', UserRolesData::ROLE_ADMIN)
        ->setParameter(':userType2', UserRolesData::ROLE_MANAGER);

      if (!empty($filter['ime'])) {
        $qb->andWhere($qb->expr()->orX(
          $qb->expr()->like('u.ime', ':ime'),
        ))
          ->setParameter('ime', '%' . $filter['ime'] . '%');
      }
      if (!empty($filter['prezime'])) {
        $qb->andWhere($qb->expr()->orX(
          $qb->expr()->like('u.prezime', ':prezime'),
        ))
          ->setParameter('prezime', '%' . $filter['prezime'] . '%');
      }
      if (!empty($filter['pozicija'])) {
        $qb->andWhere('u.pozicija = :pozicija');
        $qb->setParameter('pozicija', $filter['pozicija']);
      }
      if (!empty($filter['vrsta'])) {
        $qb->andWhere('u.userType = :vrsta');
        $qb->setParameter('vrsta', $filter['vrsta']);
      }

      $qb
        ->addOrderBy('u.userType', 'ASC')
        ->addOrderBy('u.prezime', 'ASC')
        ->getQuery();
    }


    return $qb;
  }

  public function getEmployeesCompanyPaginator(Company $company, $filter, $suspended) {

    $qb = $this->createQueryBuilder('u');

    $qb->where('u.company = :company')
      ->setParameter(':company', $company)
      ->andWhere('u.isSuspended = :suspenzija')
      ->setParameter('suspenzija', $suspended)
      ->andWhere('u.userType = :userType')
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE);

    if (!empty($filter['ime'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.ime', ':ime'),
      ))
        ->setParameter('ime', '%' . $filter['ime'] . '%');
    }
    if (!empty($filter['prezime'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.prezime', ':prezime'),
      ))
        ->setParameter('prezime', '%' . $filter['prezime'] . '%');
    }
    if (!empty($filter['obrazovanje'])) {
      $qb->andWhere('u.nivoObrazovanja = :obrazovanje');
      $qb->setParameter('obrazovanje', $filter['obrazovanje']);
    }
      if (!empty($filter['vrsta'])) {
        $qb->andWhere('u.vrstaZaposlenja = :vrsta');
        $qb->setParameter('vrsta', $filter['vrsta']);
      }
    if (!empty($filter['pozicija'])) {
      $qb->andWhere('u.pozicija = :pozicija');
      $qb->setParameter('pozicija', $filter['pozicija']);
    }
    if (!empty($filter['zvanje'])) {
      $qb->andWhere('u.zvanje = :zvanje');
      $qb->setParameter('zvanje', $filter['zvanje']);
    }

    $qb
      ->addOrderBy('u.krajUgovora', 'ASC')
      ->getQuery();


    return $qb;
  }

  public function getAllContactsPaginator($filter) {

    $company = $this->security->getUser()->getCompany();

    $qb = $this->createQueryBuilder('u')
      ->where('u.userType = :userType')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_CLIENT);

    if (!empty($filter['ime'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.ime', ':ime'),
      ))
        ->setParameter('ime', '%' . $filter['ime'] . '%');
    }
    if (!empty($filter['prezime'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.prezime', ':prezime'),
      ))
        ->setParameter('prezime', '%' . $filter['prezime'] . '%');
    }


    $qb
      ->addOrderBy('u.isSuspended', 'ASC')
      ->addOrderBy('u.userType', 'ASC')
      ->addOrderBy('u.prezime', 'ASC')
      ->getQuery();

    return $qb;
  }

  public function getAllContacts(): array {

    $company = $this->security->getUser()->getCompany();

    $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_CLIENT, 'company' => $company], ['isSuspended' => 'ASC']);

    $usersList = [];
    foreach ($users as $user) {
      $usersList [] = [
        'id' => $user->getId(),
        'ime' => $user->getFullName(),
        'email' => $user->getEmail(),
        'mobilni' => $user->getTelefon1(),
        'slika' => $user->getImage(),
        'firma' => $user->getClients(),
        'isSuspended' => $user->getBadgeByStatus(),
      ];
    }
    return $usersList;
  }

  public function getReport(array $data): array {
    $dates = explode(' - ', $data['period']);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);

    $user = $this->getEntityManager()->getRepository(User::class)->find($data['zaposleni']);

    if (isset($data['category'])){
      foreach ($data['category'] as $cat) {
        $kategorija [] = $this->getEntityManager()->getRepository(Category::class)->findOneBy(['id' => $cat]);
      }
    } else {
      $kategorija [] = 0;
    }

    if (isset($data['naplativ'])) {
      $naplativ = $data['naplativ'];
      return $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByUser($start, $stop, $user, $kategorija, $naplativ);
    }

    return $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByUser($start, $stop, $user, $kategorija);


  }

  public function getReportXls(string $datum, User $user): array {

    $dates = explode(' - ', $datum);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);

    return $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByUserXls($start, $stop, $user);

  }


  public function getUsersCars(): array {

    $company = $this->security->getUser()->getCompany();

    $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false, 'company' => $company],['prezime' => 'ASC']);

    $usersList = [];
    foreach ($users as $user) {
      $ime = $user->getFullName();
      $car = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' =>$user->getCar()]);
      if (!is_null($car)) {
        $ime = $ime . ' (' . $car->getPlate() . ')';
      }

      $usersList [] = [
        'id' => $user->getId(),
        'ime' => $ime
      ];
    }
    return $usersList;
  }

//  public function getUsersCarsAvailable(string $dan): array {
//
//    $company = $this->security->getUser()->getCompany();
//
//    $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'company' => $company, 'isSuspended' => false, 'ProjectType' => TipProjektaData::LETECE],['prezime' => 'ASC']);
//
//    $usersList = [];
//    foreach ($users as $user) {
//      $dostupan = $this->getEntityManager()->getRepository(Availability::class)->checkDostupnost($user, $dan);
//
//      if ($dostupan) {
//        $ime = $user->getFullName();
//        $car = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $user->getCar()]);
//        if (!is_null($car)) {
//          $ime = $ime . ' (' . $car->getPlate() . ')';
//        }
//
//        $usersList [] = [
//          'id' => $user->getId(),
//          'ime' => $ime,
//          'car' => $car,
//          'user' => $user,
//          'slika' => $user->getImage()->getThumbnail100(),
//          'pozicija' => $user->getPozicija()->getTitle()
//        ];
//      }
//    }
//
//    return $usersList;
//  }

  public function getUsersCarAvailable(DateTimeImmutable $dan): array {

    $company = $this->security->getUser()->getCompany();

//    if ($projectType == 0) {
//      $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'company' => $company, 'isSuspended' => false],['prezime' => 'ASC']);
//    } else {
//      $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'company' => $company, 'isSuspended' => false, 'ProjectType' => $projectType],['prezime' => 'ASC']);
//    }
    $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'company' => $company, 'isSuspended' => false],['prezime' => 'ASC']);

    $dan = $dan->format('d.m.Y');
    $usersList = [];
    foreach ($users as $user) {
      $dostupan = $this->getEntityManager()->getRepository(Availability::class)->checkDostupnost($user, $dan);

      if ($dostupan) {
        $ime = $user->getFullName();
        $car = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' => $user->getCar()]);
        if (!is_null($car)) {
          $ime = $ime . ' (' . $car->getPlate() . ')';
        }

        $usersList [] = [
          'id' => $user->getId(),
          'ime' => $ime,
          'car' => $car,
          'user' => $user,
          'pozicija' => $user->getPozicija()->getTitle()
        ];
      }
    }

    return $usersList;
  }
  public function getUsersAvailable(DateTimeImmutable $dan): array {

    $company = $this->security->getUser()->getCompany();

//    if ($projectType == 0) {
//      $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'company' => $company, 'isSuspended' => false],['prezime' => 'ASC']);
//    } else {
//      $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'company' => $company, 'isSuspended' => false, 'ProjectType' => $projectType],['prezime' => 'ASC']);
//    }
    $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'company' => $company, 'isSuspended' => false],['prezime' => 'ASC']);

    $dan = $dan->format('d.m.Y');
    $usersList = [];
    foreach ($users as $user) {
      $dostupan = $this->getEntityManager()->getRepository(Availability::class)->checkDostupnost($user, $dan);

      if ($dostupan) {
        $usersList[] = $user;
//        $ime = $user->getFullName();
//
//        $usersList [] = [
//          'id' => $user->getId(),
//          'ime' => $ime
//        ];
      }
    }

    return $usersList;
  }

  public function getUsersAvailableChecklist(DateTimeImmutable $dan): array {

    $company = $this->security->getUser()->getCompany();

    $users = $this->getUsersForChecklist();

    $dan = $dan->format('d.m.Y');
    $usersList = [];
    foreach ($users as $user) {
      $dostupan = $this->getEntityManager()->getRepository(Availability::class)->checkDostupnost($user, $dan);

      if ($dostupan) {
        $usersList[] = $user;
      }
    }

    return $usersList;
  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function countEmployees(): int{

    $company = $this->security->getUser()->getCompany();

    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function countEmployeesActive(): int{

    $company = $this->security->getUser()->getCompany();

    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter(':isSuspended', 0);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }
  public function countEmployeesOnTask(): int{

    $company = $this->security->getUser()->getCompany();

    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isInTask = :isInTask')
      ->andWhere('u.isSuspended = 0')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter(':isInTask', 1);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function countEmployeesOffTask(): int{

    $company = $this->security->getUser()->getCompany();

    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isInTask = :isInTask')
      ->andWhere('u.isSuspended = 0')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter(':isInTask', 0);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function countContacts(): int{

    $company = $this->security->getUser()->getCompany();

    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_CLIENT);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function countContactsActive(): int{

    $company = $this->security->getUser()->getCompany();

    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_CLIENT)
      ->setParameter(':isSuspended', 0);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function countUsersByLoggedUser(User $loggedUser): int {

    $company = $this->security->getUser()->getCompany();


    $qb = $this->createQueryBuilder('u');
    switch ($loggedUser->getUserType()) {
      case UserRolesData::ROLE_SUPER_ADMIN:
        $qb->select($qb->expr()->count('u'))
        ->andWhere('u.company = :company')
        ->setParameter(':company', $company);
        break;
      case UserRolesData::ROLE_ADMIN:
        $qb->select($qb->expr()->count('u'))
          ->andWhere('u.userType <> :userType')
          ->andWhere('u.userType <> :userType1')
          ->andWhere('u.company = :company')
          ->setParameter(':company', $company)
          ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
          ->setParameter(':userType1', UserRolesData::ROLE_ADMIN);
        break;
      default:
        $qb->select($qb->expr()->count('u'))
          ->andWhere('u.userType <> :userType')
          ->andWhere('u.userType <> :userType1')
          ->andWhere('u.userType <> :userType2')
          ->andWhere('u.company = :company')
          ->setParameter(':company', $company)
          ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
          ->setParameter(':userType1', UserRolesData::ROLE_ADMIN)
          ->setParameter(':userType2', UserRolesData::ROLE_MANAGER);
    }

    $query = $qb->getQuery();
    return $query->getSingleScalarResult();

  }

  public function countUsersActiveByLoggedUser(User $loggedUser): int {

    $qb = $this->createQueryBuilder('u');
    switch ($loggedUser->getUserType()) {
      case UserRolesData::ROLE_SUPER_ADMIN:
        $qb->select($qb->expr()->count('u'))
          ->andWhere('u.isSuspended = :isSuspended')
          ->andWhere('u.company = :company')
          ->setParameter(':company', $loggedUser->getCompany())
          ->setParameter(':isSuspended', 0);
        break;
      case UserRolesData::ROLE_ADMIN:
        $qb->select($qb->expr()->count('u'))
          ->andWhere('u.userType <> :userType')
          ->andWhere('u.userType <> :userType1')
          ->andWhere('u.isSuspended = :isSuspended')
          ->andWhere('u.company = :company')
          ->setParameter(':company', $loggedUser->getCompany())
          ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
          ->setParameter(':userType1', UserRolesData::ROLE_ADMIN)
          ->setParameter(':isSuspended', 0);
        break;
      default:
        $qb->select($qb->expr()->count('u'))
          ->andWhere('u.userType <> :userType')
          ->andWhere('u.userType <> :userType1')
          ->andWhere('u.userType <> :userType2')
          ->andWhere('u.isSuspended = :isSuspended')
          ->andWhere('u.company = :company')
          ->setParameter(':company', $loggedUser->getCompany())
          ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
          ->setParameter(':userType1', UserRolesData::ROLE_ADMIN)
          ->setParameter(':userType2', UserRolesData::ROLE_MANAGER)
          ->setParameter(':isSuspended', 0);
    }

    $query = $qb->getQuery();
    return $query->getSingleScalarResult();

  }

  public function getDostupni(): array {


    $company = $this->security->getUser()->getCompany();

    $dostupni = [];
    $danas = new DateTimeImmutable();
    $users = $this->createQueryBuilder('u')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter('isSuspended', 0)
      ->getQuery()
      ->getResult();

    if (!empty($users)) {
      foreach ($users as $user) {
        $nedostupnost = $this->getEntityManager()->getRepository(Availability::class)->findOneBy(['User' => $user, 'datum'=> $danas->setTime(0,0) ]);
        if (is_null($nedostupnost)) {
          $dostupni[] = [
            "name" => $user->getFullName(),
            "id" => $user->getId(),
            "task" => $user->isInTask()
          ];
        }
      }
    }

    return $dostupni;
  }

  public function getNedostupniPaginator() {

    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('u')
      ->select('u.id', 'u.ime', 'u.prezime', 'a.type', 'a.zahtev')
      ->innerJoin(Availability::class, 'a', Join::WITH, 'u = a.User')
      ->andWhere('a.datum = :today')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter('isSuspended', 0)
      ->setParameter('today', new DateTimeImmutable('today'), Types::DATETIME_IMMUTABLE)
      ->orderBy('u.prezime', 'ASC')
      ->getQuery();
  }
  public function getDostupniPaginator() {
    $company = $this->security->getUser()->getCompany();

    return $this->createQueryBuilder('u')
      ->leftJoin(Availability::class, 'a', Join::WITH, 'u = a.User AND a.datum = :today')
      ->andWhere('a.id IS NULL')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter('isSuspended', 0)
      ->setParameter('today', new DateTimeImmutable('today'), Types::DATETIME_IMMUTABLE)
      ->orderBy('u.prezime', 'ASC')
      ->getQuery();

  }

  public function getZaposleni(): array {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('u')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter(':isSuspended', 0)
      ->getQuery()
      ->getResult();
  }

  public function getZaposleniCommand(Company $company): array {
    return $this->createQueryBuilder('u')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter(':isSuspended', 0)
      ->getQuery()
      ->getResult();
  }

  public function getUsersForChecklist(): array {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('u')
      ->andWhere('u.userType <> :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_CLIENT)
      ->setParameter(':isSuspended', 0)
      ->orderBy('u.userType', 'ASC')
      ->addOrderBy('u.prezime', 'ASC')
      ->getQuery()
      ->getResult();
  }

  public function getUsersForChecklistArchive(): array {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('u')
      ->andWhere('u.userType <> :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_CLIENT)
      ->setParameter(':isSuspended', 1)
      ->orderBy('u.userType', 'ASC')
      ->addOrderBy('u.prezime', 'ASC')
      ->getQuery()
      ->getResult();
  }

  public function getUsersForQuickChecklist(): array {

    $user = $this->security->getUser();
    $userId = $this->security->getUser()->getId();
    $company = $user->getCompany();

    return $this->createQueryBuilder('u')
      ->where('u.userType <> :userType')
      ->orWhere('u.userType <> :userType1')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->andWhere('u.id <> :id')
      ->setParameter(':id', $userId)
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
      ->setParameter(':userType1', UserRolesData::ROLE_CLIENT)
      ->setParameter(':isSuspended', 0)
      ->orderBy('u.userType', 'ASC')
      ->addOrderBy('u.prezime', 'ASC')
      ->getQuery()
      ->getResult();



//    $user1 = $this->find(1);
//    $user2 = $this->find(26);
//    $user3 = $this->find(27);
//    $user4 = $this->find(40);
//    $user5 = $this->find(47);

//    return [$user2, $user3, $user4, $user5, $user1];
  }

  public function getNedostupni(): array {

    $company = $this->security->getUser()->getCompany();

    $nedostupni = [];
    $danas = new DateTimeImmutable();
    $users = $this->createQueryBuilder('u')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter(':isSuspended', 0)
      ->getQuery()
      ->getResult();

    if (!empty($users)) {
      foreach ($users as $user) {
        $nedostupnost = $this->getEntityManager()->getRepository(Availability::class)->findOneBy(['User' => $user, 'datum'=> $danas->setTime(0,0) ]);
        if (!is_null($nedostupnost)) {
          $nedostupni[] = [
            "name" => $user->getFullName(),
            "id" => $user->getId(),
            "task" => $user->isInTask(),
            "zahtev" => $nedostupnost
          ];
        }
      }
    }

    return $nedostupni;
  }
  public function getEmployees(int $type): array {
    $company = $this->security->getUser()->getCompany();
    if ($type == 1) {
      $users = $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isInTask' => true, 'isSuspended' => false]);
    } elseif ( $type == 2) {
      $users = $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isInTask' => false, 'isSuspended' => false]);
    } else {
      $users = $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE], ['isSuspended' => 'ASC']);
    }



    $usersList = [];
    foreach ($users as $user) {
      $usersList [] = [
        'id' => $user->getId(),
        'ime' => $user->getIme(),
        'prezime' => $user->getPrezime(),
        'slika' => $user->getImage(),
        'isSuspended' => $user->getBadgeByStatus(),
        'datumRodjenja' => $user->getDatumRodjenja(),
        'role' => $user->getBadgeByUserType(),
        'pozicija' => $user->getPozicija()->getTitle(),
      ];
    }
    return $usersList;
  }

//  public function getEmployeesPaginator(int $type) {
//    $company = $this->security->getUser()->getCompany();
//    return match ($type) {
//      1 => $this->createQueryBuilder('u')
//        ->where('u.isInTask = 1')
//        ->andWhere('u.userType = :userType')
//        ->andWhere('u.isSuspended = 0')
//        ->andWhere('u.company = :company')
//        ->setParameter(':company', $company)
//        ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
//        ->orderBy('u.prezime', 'ASC')
//        ->addOrderBy('u.id', 'ASC')
//        ->getQuery(),
//
//      2 => $this->createQueryBuilder('u')
//        ->where('u.isInTask = 0')
//        ->andWhere('u.userType = :userType')
//        ->andWhere('u.isSuspended = 0')
//        ->andWhere('u.company = :company')
//        ->setParameter(':company', $company)
//        ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
//        ->orderBy('u.prezime', 'ASC')
//        ->addOrderBy('u.id', 'ASC')
//        ->getQuery(),
//
//      default => $this->createQueryBuilder('u')
//        ->where('u.userType = :userType')
//        ->andWhere('u.company = :company')
//        ->setParameter(':company', $company)
//        ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
//        ->orderBy('u.isSuspended', 'ASC')
//        ->addOrderBy('u.userType', 'ASC')
//        ->addOrderBy('u.prezime', 'ASC')
//        ->addOrderBy('u.id', 'ASC')
//        ->getQuery(),
//
//    };
//
////    $usersList = [];
////    foreach ($users as $user) {
////
////      $usersList [] = [
////        'id' => $user->getId(),
////        'ime' => $user->getIme(),
////        'prezime' => $user->getPrezime(),
////        'slika' => $user->getImage(),
////        'isSuspended' => $user->getBadgeByStatus(),
////        'datumRodjenja' => $user->getDatumRodjenja(),
////        'role' => $user->getBadgeByUserType(),
////      ];
////    }
////
////    return $usersList;
//  }

  public function getEmployeesPaginator(User $loggedUser, $filter, $suspended) {

    $company = $loggedUser->getCompany();
    $qb = $this->createQueryBuilder('u');


      $qb->where('u.company = :company')
        ->setParameter(':company', $company)
        ->andWhere('u.isSuspended = :suspenzija')
        ->setParameter('suspenzija', $suspended)
        ->andWhere('u.userType = :userType')
        ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE);

      if (!empty($filter['ime'])) {
        $qb->andWhere($qb->expr()->orX(
          $qb->expr()->like('u.ime', ':ime'),
        ))
          ->setParameter('ime', '%' . $filter['ime'] . '%');
      }
      if (!empty($filter['prezime'])) {
        $qb->andWhere($qb->expr()->orX(
          $qb->expr()->like('u.prezime', ':prezime'),
        ))
          ->setParameter('prezime', '%' . $filter['prezime'] . '%');
      }
      if (!empty($filter['pozicija'])) {
        $qb->andWhere('u.pozicija = :pozicija');
        $qb->setParameter('pozicija', $filter['pozicija']);
      }
//      if (!empty($filter['vrsta'])) {
//        $qb->andWhere('u.userType = :vrsta');
//        $qb->setParameter('vrsta', $filter['vrsta']);
//      }

      $qb
        ->addOrderBy('u.prezime', 'ASC')
        ->getQuery();


    return $qb;
  }

  public function getEmployeesPaginatorKadrovska(User $loggedUser, $filter, $suspended) {

    $qb = $this->createQueryBuilder('u');
    $qb->where('u.isKadrovska = 1')
      ->andWhere('u.isSuspended = :suspenzija')
      ->setParameter('suspenzija', $suspended)
      ->andWhere('u.userType = :userType')
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE);

    if (!empty($filter['ime'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.ime', ':ime'),
      ))
        ->setParameter('ime', '%' . $filter['ime'] . '%');
    }
    if (!empty($filter['prezime'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.prezime', ':prezime'),
      ))
        ->setParameter('prezime', '%' . $filter['prezime'] . '%');
    }
    if (!empty($filter['companija'])) {
      $qb->andWhere('u.company = :company');
      $qb->setParameter('company', $filter['kompanija']);
    }

    $qb
      ->orderBy('u.krajUgovora', 'ASC')
      ->getQuery();


    return $qb;
  }

  public function getRazlikaUsers(Company $company, array $users): array {

    $qb = $this->createQueryBuilder('u');

    $korisnici = $qb->where('u.company = :company')
      ->setParameter(':company', $company)
      ->andWhere('u.isSuspended = :suspenzija')
      ->setParameter('suspenzija', 0)
      ->andWhere('u.ProjectType = :projekat')
      ->setParameter('projekat', TipProjektaData::LETECE)
      ->andWhere('u.userType = :userType')
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
      ->andWhere($qb->expr()->notIn('u.id', $users))
      ->getQuery()
      ->getResult();

    return $korisnici;
  }

  public function getPdfsByUser(User $user): array {

    $pdfs = $this->createQueryBuilder('u')
      ->select('i.title', 'i.path', 'i.created')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 'u = tl.user')
      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
      ->innerJoin(Pdf::class, 'i', Join::WITH, 's = i.stopwatchTime')
      ->andWhere('u.id = :userId')
      ->andWhere('s.isDeleted = 0')
      ->setParameter(':userId', $user->getId())
      ->getQuery()
      ->getResult();

    return $pdfs;
//    $pdfsProject = $this->createQueryBuilder('p')
//      ->select('i.title', 'i.path', 'i.created')
//      ->innerJoin(Pdf::class, 'i', Join::WITH, 'p = i.project')
//      ->andWhere('p.id = :projectId')
//      ->andWhere('i.stopwatchTime IS NULL')
//      ->setParameter(':projectId', $project->getId())
//      ->getQuery()
//      ->getResult();
//
//    return array_merge($pdfs, $pdfsProject);

  }

  public function getImagesByUser(User $user): array {

    return $this->createQueryBuilder('u')
      ->select('i.thumbnail100', 'i.thumbnail500', 'i.thumbnail1024')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 'u = tl.user')
      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
      ->innerJoin(Image::class, 'i', Join::WITH, 's = i.stopwatchTime')
      ->andWhere('u.id = :userId')
      ->andWhere('s.isDeleted = 0')
      ->setParameter(':userId', $user->getId())
      ->getQuery()
      ->getResult();

  }

  public function getUsersSearchPaginator($filterBy, User $user){
    $company = $user->getCompany();

    $qb = $this->createQueryBuilder('t');

    $qb->where('t.company = :company');
    $qb->setParameter(':company', $company);

    $qb->andWhere('t.userType = :type');
    $qb->setParameter(':type', UserRolesData::ROLE_EMPLOYEE);


    if ($filterBy['status'] == 1) {

        $qb->andWhere('t.isSuspended = :status');
        $qb->setParameter('status', $filterBy['statusStanje']);

    } else {

        $qb->andWhere('t.isSuspended <> :status');
        $qb->setParameter('status', $filterBy['statusStanje']);

    }


    $keywords = explode(" ", $filterBy['tekst']);


    foreach ($keywords as $key => $keyword) {
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->like('t.ime', ':keyword'.$key),
          $qb->expr()->like('t.prezime', ':keyword'.$key)
        ))
        ->setParameter('keyword'.$key, '%' . $keyword . '%');
    }
    $qb
      ->addOrderBy('t.prezime', 'ASC')
      ->getQuery();


    return $qb;
  }

  public function getUsersSearchPaginatorKadrovskaEmployee($filterBy, $company){

    $qb = $this->createQueryBuilder('t');

    $qb->where('t.isKadrovska = 1');
    $qb->andWhere('t.company = :company');
    $qb->setParameter(':company', $company);
    $qb->andWhere('t.userType = :type');
    $qb->setParameter(':type', UserRolesData::ROLE_EMPLOYEE);


//    if ($filterBy['status'] == 1) {
//
//      $qb->andWhere('t.isSuspended = :status');
//      $qb->setParameter('status', $filterBy['statusStanje']);
//
//    } else {
//
//      $qb->andWhere('t.isSuspended <> :status');
//      $qb->setParameter('status', $filterBy['statusStanje']);
//
//    }


    $keywords = explode(" ", $filterBy['tekst']);


    foreach ($keywords as $key => $keyword) {
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->like('t.ime', ':keyword'.$key),
          $qb->expr()->like('t.prezime', ':keyword'.$key)
        ))
        ->setParameter('keyword'.$key, '%' . $keyword . '%');
    }
    $qb
      ->addOrderBy('t.prezime', 'ASC')
      ->getQuery();


    return $qb;
  }
  public function getUsersSearchPaginatorKadrovska($filterBy){

    $qb = $this->createQueryBuilder('t');

    $qb->where('t.isKadrovska = 1');

    $qb->andWhere('t.userType = :type');
    $qb->setParameter(':type', UserRolesData::ROLE_EMPLOYEE);


    if ($filterBy['status'] == 1) {

      $qb->andWhere('t.isSuspended = :status');
      $qb->setParameter('status', $filterBy['statusStanje']);

    } else {

      $qb->andWhere('t.isSuspended <> :status');
      $qb->setParameter('status', $filterBy['statusStanje']);

    }


    $keywords = explode(" ", $filterBy['tekst']);


    foreach ($keywords as $key => $keyword) {
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->like('t.ime', ':keyword'.$key),
          $qb->expr()->like('t.prezime', ':keyword'.$key)
        ))
        ->setParameter('keyword'.$key, '%' . $keyword . '%');
    }
    $qb
      ->addOrderBy('t.prezime', 'ASC')
      ->getQuery();


    return $qb;
  }

  public function getZaposleniNotMix(): array {
    $company = $this->security->getUser()->getCompany();

    return $this->createQueryBuilder('u')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->andWhere('u.company = :company')
      ->andWhere('u.ProjectType <> :projectType')
      ->setParameter(':company', $company)
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter(':isSuspended', 0)
      ->setParameter(':projectType', TipProjektaData::KOMBINOVANO)
      ->getQuery()
      ->getResult();
  }

  public function getDaysRemaining(User $user): array {
    $poruka = '';
    $klasa = '';
    $now = new DateTimeImmutable();
    $now->setTime(0,0);

    if (!is_null($user->getKrajUgovora())) {
      $contractEndDate = $user->getKrajUgovora();
      // Izračunavanje razlike između trenutnog datuma i datuma kraja ugovora
      $days = (int) $now->diff($contractEndDate)->format('%R%a');

      if ($days > 0 && $days < 15) {
        $poruka = 'Ugovor ističe za ' . $days . ' dana.';
        $klasa = 'bg-info bg-opacity-50';
      } elseif ($days == 0) {
        $poruka = 'Ugovor ističe danas.';
        $klasa = 'bg-warning bg-opacity-50';
      } elseif ($days < 0) {
        $poruka = 'Ugovor je istekao pre ' . abs($days) . ' dana.';
        $klasa = 'bg-danger bg-opacity-50';
      }
    }

    return [
      'klasa' => $klasa,
      'poruka' => $poruka
    ];
  }

  public function getUsersCount(User $user): array {

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      $qb = $this->createQueryBuilder('u');
      $now = new DateTimeImmutable();
      $now->setTime(0,0);

      // Ukupan broj korisnika
      $totalUsers = $this->createQueryBuilder('u')
        ->where('u.isKadrovska = 1')
        ->andWhere('u.company = :company')
        ->setParameter('company', $user->getCompany())
        ->andWhere('u.isSuspended = 0')
        ->andWhere('u.userType = :userType')
        ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
        ->getQuery()
        ->getResult();

      // Broj korisnika kojima ugovor ističe za manje od 15 dana
      $expiresInLessThan15Days = $this->createQueryBuilder('u')
        ->where('u.krajUgovora IS NOT NULL')
        ->andWhere('u.company = :company')
        ->setParameter('company', $user->getCompany())
        ->andWhere('u.isKadrovska = 1')
        ->andWhere('u.isSuspended = 0')
        ->andWhere('u.userType = :userType')
        ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
        ->andWhere('u.krajUgovora > :now')
        ->andWhere('u.krajUgovora <= :futureDate')
        ->setParameter('now', $now)
        ->setParameter('futureDate', $now->modify('+15 days'))
        ->getQuery()
        ->getResult();

      // Broj korisnika kojima ugovor ističe danas
      $expiresToday = $this->createQueryBuilder('u')
        ->where('u.krajUgovora IS NOT NULL')
        ->andWhere('u.company = :company')
        ->setParameter('company', $user->getCompany())
        ->andWhere('u.isKadrovska = 1')
        ->andWhere('u.isSuspended = 0')
        ->andWhere('u.userType = :userType')
        ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
        ->andWhere('u.krajUgovora = :today')
        ->setParameter('today', $now->format('Y-m-d'))
        ->getQuery()
        ->getResult();

      // Broj korisnika kojima je ugovor istekao
      $expiredUsers = $this->createQueryBuilder('u')
        ->where('u.krajUgovora IS NOT NULL')
        ->andWhere('u.company = :company')
        ->setParameter('company', $user->getCompany())
        ->andWhere('u.isKadrovska = 1')
        ->andWhere('u.isSuspended = 0')
        ->andWhere('u.userType = :userType')
        ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
        ->andWhere('u.krajUgovora < :today')
        ->setParameter('today', $now->format('Y-m-d'))
        ->getQuery()
        ->getResult();

      return [
        'totalUsers' => count($totalUsers),
        'expiresInLessThan15Days' => count($expiresInLessThan15Days),
        'expiresToday' => count($expiresToday),
        'expiredUsers' => count($expiredUsers),
      ];
    } else {
      $qb = $this->createQueryBuilder('u');
      $now = new DateTimeImmutable();
      $now->setTime(0,0);

      // Ukupan broj korisnika
      $totalUsers = $this->createQueryBuilder('u')
        ->where('u.isKadrovska = 1')
        ->andWhere('u.isSuspended = 0')
        ->andWhere('u.userType = :userType')
        ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
        ->getQuery()
        ->getResult();

      // Broj korisnika kojima ugovor ističe za manje od 15 dana
      $expiresInLessThan15Days = $this->createQueryBuilder('u')
        ->where('u.krajUgovora IS NOT NULL')
        ->andWhere('u.isKadrovska = 1')
        ->andWhere('u.isSuspended = 0')
        ->andWhere('u.userType = :userType')
        ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
        ->andWhere('u.krajUgovora > :now')
        ->andWhere('u.krajUgovora <= :futureDate')
        ->setParameter('now', $now)
        ->setParameter('futureDate', $now->modify('+15 days'))
        ->getQuery()
        ->getResult();

      // Broj korisnika kojima ugovor ističe danas
      $expiresToday = $this->createQueryBuilder('u')
        ->where('u.krajUgovora IS NOT NULL')
        ->andWhere('u.isKadrovska = 1')
        ->andWhere('u.isSuspended = 0')
        ->andWhere('u.userType = :userType')
        ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
        ->andWhere('u.krajUgovora = :today')
        ->setParameter('today', $now->format('Y-m-d'))
        ->getQuery()
        ->getResult();

      // Broj korisnika kojima je ugovor istekao
      $expiredUsers = $this->createQueryBuilder('u')
        ->where('u.krajUgovora IS NOT NULL')
        ->andWhere('u.isKadrovska = 1')
        ->andWhere('u.isSuspended = 0')
        ->andWhere('u.userType = :userType')
        ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
        ->andWhere('u.krajUgovora < :today')
        ->setParameter('today', $now->format('Y-m-d'))
        ->getQuery()
        ->getResult();

      return [
        'totalUsers' => count($totalUsers),
        'expiresInLessThan15Days' => count($expiresInLessThan15Days),
        'expiresToday' => count($expiresToday),
        'expiredUsers' => count($expiredUsers),
      ];
    }


  }

  public function getUsersCheckEmail(): array {

    $qb = $this->createQueryBuilder('u');
    $now = new DateTimeImmutable();
    $now->setTime(0,0);

    // Broj korisnika kojima ugovor ističe za manje od 15 dana
    $expiresInLessThan15Days = $this->createQueryBuilder('u')
      ->where('u.krajUgovora IS NOT NULL')
      ->andWhere('u.isKadrovska = 1')
      ->andWhere('u.isSuspended = 0')
      ->andWhere('u.userType = :userType')
      ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
      ->andWhere('u.krajUgovora > :now')
      ->andWhere('u.krajUgovora <= :futureDate')
      ->setParameter('now', $now)
      ->setParameter('futureDate', $now->modify('+15 days'))
      ->orderBy('u.company', 'ASC')
      ->addOrderBy('u.krajUgovora', 'ASC')
      ->getQuery()
      ->getResult();

    // Broj korisnika kojima ugovor ističe danas
    $expiresToday = $this->createQueryBuilder('u')
      ->where('u.krajUgovora IS NOT NULL')
      ->andWhere('u.isKadrovska = 1')
      ->andWhere('u.isSuspended = 0')
      ->andWhere('u.userType = :userType')
      ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
      ->andWhere('u.krajUgovora = :today')
      ->setParameter('today', $now->format('Y-m-d'))
      ->orderBy('u.company', 'ASC')
      ->addOrderBy('u.krajUgovora', 'ASC')
      ->getQuery()
      ->getResult();

    // Broj korisnika kojima je ugovor istekao
    $expiredUsers = $this->createQueryBuilder('u')
      ->where('u.krajUgovora IS NOT NULL')
      ->andWhere('u.isKadrovska = 1')
      ->andWhere('u.isSuspended = 0')
      ->andWhere('u.userType = :userType')
      ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
      ->andWhere('u.krajUgovora < :today')
      ->setParameter('today', $now->format('Y-m-d'))
      ->orderBy('u.company', 'ASC')
      ->addOrderBy('u.krajUgovora', 'ASC')
      ->getQuery()
      ->getResult();

    return [
      'soon' => $expiresInLessThan15Days,
      'today' => $expiresToday,
      'expired' => $expiredUsers,
    ];

  }




//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
