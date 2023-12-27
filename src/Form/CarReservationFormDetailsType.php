<?php

namespace App\Form;

use App\Classes\Data\CleanData;
use App\Classes\Data\FuelData;
use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\City;
use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;

class CarReservationFormDetailsType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {


    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?CarReservation {
        return $this->builder->getData();
      }

    };

    $car = $dataObject->getReservation()->getCar();
    $driver = $dataObject->getReservation()->getDriver();
    $company = $dataObject->getReservation()->getCompany();

      $builder
        ->add('car', EntityType::class, [
          'class' => Car::class,
          'query_builder' => function (EntityRepository $em) use ($company) {
            return $em->createQueryBuilder('c')
              ->andWhere('c.isReserved = :isReserved')
              ->andWhere('c.isSuspended = :isSuspended')
              ->andWhere('c.company = :company')
              ->setParameter(':company', $company)
              ->setParameter(':isReserved', 0)
              ->setParameter(':isSuspended', 0)
              ->orderBy('c.id', 'ASC');
          },
          'choice_label' => function ($car) {
            return $car->getCarName();
          },
          'expanded' => false,
          'multiple' => false,
          'data' => $car
        ])
        ->add('fuelStart', ChoiceType::class, [
          'placeholder' => '--Izaberite nivo goriva--',
          'choices' => FuelData::form(),
          'expanded' => false,
          'multiple' => false,
        ])
        ->add('cleanStart', ChoiceType::class, [
          'placeholder' => '--Izaberite nivo čistoće--',
          'choices' => CleanData::form(),
          'expanded' => false,
          'multiple' => false,
        ])
        ->add('descStart')
        ->add('kmStart', NumberType::class, [
          'attr' => [
            'min' => $car->getKm(),
            'max' => $car->getKm() + 500,
          ],
          'required' => true,
          'html5' => true,
        ]);

    if (is_null($driver)) {
      $builder
        ->add('driver', EntityType::class, [
          'class' => User::class,
          'query_builder' => function (EntityRepository $em) use ($company) {
            return $em->createQueryBuilder('d')
              ->andWhere('d.car IS NULL')
              ->andWhere('d.isSuspended = :isSuspended')
              ->andWhere('d.company = :company')
              ->setParameter(':company', $company)
              ->setParameter(':isSuspended', 0)
              ->orderBy('d.id', 'ASC');
          },
          'choice_label' => function ($user) {
            return $user->getFullName();
          },
          'expanded' => false,
          'multiple' => false,
        ]);
    } else {
      $builder
        ->add('driver', EntityType::class, [
          'class' => User::class,
          'query_builder' => function (EntityRepository $em) use ($company, $driver) {
            return $em->createQueryBuilder('d')
              ->andWhere('d.id = :id')
              ->andWhere('d.company = :company')
              ->setParameter(':company', $company)
              ->setParameter(':id', $driver->getId())
              ->orderBy('d.id', 'ASC');
          },
          'choice_label' => function ($user) {
            return $user->getFullName();
          },
          'expanded' => false,
          'multiple' => false,
        ]);
    }
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => CarReservation::class,
    ]);
  }
}
