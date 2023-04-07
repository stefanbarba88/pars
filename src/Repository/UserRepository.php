<?php

namespace App\Repository;

use App\Classes\Data\UserRolesData;
use App\Classes\DTO\UploadedFileDTO;
use App\Entity\Image;
use App\Entity\Project;
use App\Entity\ProjectHistory;
use App\Entity\User;
use App\Entity\UserHistory;
use App\Service\MailService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
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
    $this->save($user);

    $this->mail->registration($user);
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

    if (is_null($user->getId())) {
      //    $user->setEditBy($this->security->getUser());
      $user->setCreatedBy($this->getEntityManager()->getRepository(User::class)->find(1));
    } else {
      $user->setEditBy($this->getEntityManager()->getRepository(User::class)->find(2));
    }

    if (!empty($user->getPlainPassword())) {
      $this->hashPlainPassword($user);
      $this->mail->edit($user);
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
      return new User();
    }
    return $this->getEntityManager()->getRepository(User::class)->find($id);
  }

  public function getAll(): array {
//    $qb = $this->createQueryBuilder('u');
//    $qb->select('u.id','u.ime', 'u.prezime', 'u.slika', 'u.isSuspended', 'u.datumRodjenja', 'u.userType');
//    $qb->orderBy('u.id')->getQuery()->getResult();
    $users = $this->getEntityManager()->getRepository(User::class)->findAll();

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
  public function getEmployees(): array {
//    $qb = $this->createQueryBuilder('u');
//    $qb->select('u.id','u.ime', 'u.prezime', 'u.slika', 'u.isSuspended', 'u.datumRodjenja', 'u.userType');
//    $qb->orderBy('u.id')->getQuery()->getResult();
    $users = $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE]);

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
