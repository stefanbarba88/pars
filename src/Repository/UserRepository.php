<?php

namespace App\Repository;

use App\Entity\User;
use App\Service\MailService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
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

  public function __construct(ManagerRegistry $registry, UserPasswordHasherInterface $passwordHasher, MailService $mail) {
    parent::__construct($registry, User::class);
    $this->passwordHasher = $passwordHasher;
    $this->mail = $mail;
  }

  public function register(User $user): User {

    $this->hashPlainPassword($user);
    $this->getEntityManager()->persist($user);

    $this->mail->registration($user);

    $this->getEntityManager()->flush();

    return $user;
  }

  public function save(User $user): User {
    $this->hashPlainPassword($user);
    $this->getEntityManager()->persist($user);

    $this->mail->registration($user);

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

//  public function save(User $entity, bool $flush = false): void {
//    $this->getEntityManager()->persist($entity);
//
//    if ($flush) {
//      $this->getEntityManager()->flush();
//    }
//  }

  public function remove(User $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
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
    $qb = $this->createQueryBuilder('u');
    return $qb->orderBy('u.id')->getQuery()->getResult();
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
