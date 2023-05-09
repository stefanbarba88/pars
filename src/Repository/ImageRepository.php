<?php

namespace App\Repository;

use App\Classes\DTO\UploadedFileDTO;
use App\Classes\Thumb;
use App\Entity\Client;
use App\Entity\Image;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Image>
 *
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Image::class);
  }


  public function remove(Image $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function addImage(UploadedFileDTO $file, string $thumbnailPath, string $kernelPath): Image {
    return $this->save($this->add($file, $thumbnailPath, $kernelPath));
  }

  public function add(UploadedFileDTO $file, string $thumbnailPath, string $kernelPath): Image {

    $image = new Image();
    $image->setOriginal($file->getAssetPath());

    $thumb = new Thumb();
    $savepath = $kernelPath . $thumbnailPath;

    if (!file_exists($savepath)) {
      mkdir($savepath, 0777, true);
    }

    $param1 = [
      "sourcefile" => $file->getPath(),
      "savepath" => $savepath . '100' .$file->getFileName(),
      "max_width" => "100",
      "max_height" => "100"
    ];
    $param2 = [
      "sourcefile" => $file->getPath(),
      "savepath" => $savepath . '500' .$file->getFileName(),
      "max_width" => "500",
      "max_height" => "500"
    ];
    $param3 = [
      "sourcefile" => $file->getPath(),
      "savepath" => $savepath . '1024' .$file->getFileName(),
      "max_width" => "1024",
      "max_height" => "768"
    ];

    $thumb->thumbnail($param1);
    $thumb->thumbnail($param2);
    $thumb->thumbnail($param3);


    $image->setThumbnail100(str_replace("/public","",$thumbnailPath . '100' .$file->getFileName()));
    $image->setThumbnail500(str_replace("/public","",$thumbnailPath . '500' .$file->getFileName()));
    $image->setThumbnail1024(str_replace("/public","",$thumbnailPath . '1024' .$file->getFileName()));

    return $image;
  }

  public function addImagesClient(Client $client): Image {

    $image = new Image();
    $image->setClient($client);
    $image->setOriginal('/assets/images/clients/client.svg');
    $image->setThumbnail100('/assets/images/clients/client.svg');
    $image->setThumbnail500('/assets/images/clients/client.svg');

    return $this->save($image);
  }

  public function save(Image $image): Image {
    if (is_null($image->getId())) {
      $this->getEntityManager()->persist($image);
    }
    $this->getEntityManager()->flush();

    return $image;
  }

//    /**
//     * @return Image[] Returns an array of Image objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Image
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
