<?php

namespace App\Repository;

use App\Classes\Data\UserRolesData;
use App\Classes\DTO\UploadedFileDTO;
use App\Entity\Availability;
use App\Entity\Car;
use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\ProjectHistory;
use App\Entity\StopwatchTime;
use App\Entity\TaskLog;
use App\Entity\User;
use App\Entity\UserHistory;
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
    if ($user->getUserType() != UserRolesData::ROLE_CLIENT) {
      $this->mail->registration($user);
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
      return $user;
    }
    return $this->getEntityManager()->getRepository(User::class)->find($id);
  }

  public function getAllByLoggedUser(User $loggedUser): array {

    $users = match ($loggedUser->getUserType()) {
      UserRolesData::ROLE_SUPER_ADMIN => $this->getEntityManager()->getRepository(User::class)->findBy([], ['isSuspended' => 'ASC', 'userType' => 'ASC']),
      UserRolesData::ROLE_ADMIN => $this->createQueryBuilder('u')
        ->andWhere('u.userType <> :userType')
        ->andWhere('u.userType <> :userType1')
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

  public function getAllByLoggedUserPaginator(User $loggedUser) {

    $users = match ($loggedUser->getUserType()) {
      UserRolesData::ROLE_SUPER_ADMIN => $this->createQueryBuilder('u')
        ->addOrderBy('u.isSuspended', 'ASC')
        ->addOrderBy('u.userType', 'ASC')
        ->getQuery(),

      UserRolesData::ROLE_ADMIN => $this->createQueryBuilder('u')
        ->andWhere('u.userType <> :userType')
        ->andWhere('u.userType <> :userType1')
        ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
        ->setParameter(':userType1', UserRolesData::ROLE_ADMIN)
        ->addOrderBy('u.isSuspended', 'ASC')
        ->addOrderBy('u.userType', 'ASC')
        ->getQuery(),

      default => $this->createQueryBuilder('u')
        ->andWhere('u.userType <> :userType')
        ->andWhere('u.userType <> :userType1')
        ->andWhere('u.userType <> :userType2')
        ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
        ->setParameter(':userType1', UserRolesData::ROLE_ADMIN)
        ->setParameter(':userType2', UserRolesData::ROLE_MANAGER)
        ->addOrderBy('u.isSuspended', 'ASC')
        ->addOrderBy('u.userType', 'ASC')
        ->getQuery(),

    };

    return $users;

//    $usersList = [];
//    foreach ($users as $user) {
//
//      $usersList [] = [
//        'id' => $user->getId(),
//        'ime' => $user->getIme(),
//        'prezime' => $user->getPrezime(),
//        'slika' => $user->getImage(),
//        'isSuspended' => $user->getBadgeByStatus(),
//        'datumRodjenja' => $user->getDatumRodjenja(),
//        'role' => $user->getBadgeByUserType(),
//      ];
//    }
//
//    return $usersList;
  }

  public function getAllContactsPaginator() {

    return $this->createQueryBuilder('u')
      ->andWhere('u.userType = :userType')
      ->setParameter(':userType', UserRolesData::ROLE_CLIENT)
      ->addOrderBy('u.isSuspended', 'ASC')
      ->getQuery();
  }

  public function getAllContacts(): array {

    $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_CLIENT], ['isSuspended' => 'ASC']);

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

    $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false],['prezime' => 'ASC']);

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

  public function getUsersCarsAvailable(string $dan): array {

    $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false],['prezime' => 'ASC']);

    $usersList = [];
    foreach ($users as $user) {
      $dostupan = $this->getEntityManager()->getRepository(Availability::class)->checkDostupnost($user, $dan);

      if ($dostupan) {
        $ime = $user->getFullName();
        $car = $this->getEntityManager()->getRepository(Car::class)->findOneBy(['id' =>$user->getCar()]);
        if (!is_null($car)) {
          $ime = $ime . ' (' . $car->getPlate() . ')';
        }

        $usersList [] = [
          'id' => $user->getId(),
          'ime' => $ime,
          'slika' => $user->getImage()->getThumbnail100(),
          'pozicija' => $user->getPozicija()->getTitle()
        ];
      }
    }

    return $usersList;
  }

  public function getUsersAvailable(DateTimeImmutable $dan): array {

    $users =  $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false],['prezime' => 'ASC']);
    $dan = $dan->format('d.m.Y');
    $usersList = [];
    foreach ($users as $user) {
      $dostupan = $this->getEntityManager()->getRepository(Availability::class)->checkDostupnost($user, $dan);

      if ($dostupan) {
        $ime = $user->getFullName();

        $usersList [] = [
          'id' => $user->getId(),
          'ime' => $ime
        ];
      }
    }

    return $usersList;
  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function countEmployees(): int{
    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function countEmployeesActive(): int{
    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter(':isSuspended', 0);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }
  public function countEmployeesOnTask(): int{
    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isInTask = :isInTask')
      ->andWhere('u.isSuspended = 0')
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter(':isInTask', 1);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function countEmployeesOffTask(): int{
    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isInTask = :isInTask')
      ->andWhere('u.isSuspended = 0')
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
    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->setParameter(':userType', UserRolesData::ROLE_CLIENT);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function countContactsActive(): int{
    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->setParameter(':userType', UserRolesData::ROLE_CLIENT)
      ->setParameter(':isSuspended', 0);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function countUsersByLoggedUser(User $loggedUser): int {


    $qb = $this->createQueryBuilder('u');
    switch ($loggedUser->getUserType()) {
      case UserRolesData::ROLE_SUPER_ADMIN:
        $qb->select($qb->expr()->count('u'));
        break;
      case UserRolesData::ROLE_ADMIN:
        $qb->select($qb->expr()->count('u'))
          ->andWhere('u.userType <> :userType')
          ->andWhere('u.userType <> :userType1')
          ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
          ->setParameter(':userType1', UserRolesData::ROLE_ADMIN);
        break;
      default:
        $qb->select($qb->expr()->count('u'))
          ->andWhere('u.userType <> :userType')
          ->andWhere('u.userType <> :userType1')
          ->andWhere('u.userType <> :userType2')
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
          ->setParameter(':isSuspended', 0);
        break;
      case UserRolesData::ROLE_ADMIN:
        $qb->select($qb->expr()->count('u'))
          ->andWhere('u.userType <> :userType')
          ->andWhere('u.userType <> :userType1')
          ->andWhere('u.isSuspended = :isSuspended')
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
          ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
          ->setParameter(':userType1', UserRolesData::ROLE_ADMIN)
          ->setParameter(':userType2', UserRolesData::ROLE_MANAGER)
          ->setParameter(':isSuspended', 0);
    }

    $query = $qb->getQuery();
    return $query->getSingleScalarResult();

  }

  public function getDostupni(): array {
    $dostupni = [];
    $danas = new DateTimeImmutable();
    $users = $this->createQueryBuilder('u')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
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

    return $this->createQueryBuilder('u')
      ->select('u.id', 'u.ime', 'u.prezime', 'a.type', 'a.zahtev')
      ->innerJoin(Availability::class, 'a', Join::WITH, 'u = a.User')
      ->andWhere('a.datum = :today')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter('isSuspended', 0)
      ->setParameter('today', new DateTimeImmutable('today'), Types::DATETIME_IMMUTABLE)
      ->orderBy('u.prezime', 'ASC')
      ->getQuery();
  }
  public function getDostupniPaginator() {

    return $this->createQueryBuilder('u')
      ->leftJoin(Availability::class, 'a', Join::WITH, 'u = a.User AND a.datum = :today')
      ->andWhere('a.id IS NULL')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->setParameter('userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter('isSuspended', 0)
      ->setParameter('today', new DateTimeImmutable('today'), Types::DATETIME_IMMUTABLE)
      ->orderBy('u.prezime', 'ASC')
      ->getQuery();

  }

  public function getZaposleni(): array {

    return $this->createQueryBuilder('u')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
      ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
      ->setParameter(':isSuspended', 0)
      ->getQuery()
      ->getResult();
  }

  public function getNedostupni(): array {
    $nedostupni = [];
    $danas = new DateTimeImmutable();
    $users = $this->createQueryBuilder('u')
      ->andWhere('u.userType = :userType')
      ->andWhere('u.isSuspended = :isSuspended')
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
