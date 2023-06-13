<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\City;
use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;

class CarReservationFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $builder
      ->add('driver', EntityType::class, [
        'class' => User::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.userType = :userType')
            ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => function ($user) {
          return $user->getNameForForm();
        },
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('car', EntityType::class, [
        'class' => Car::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('c')
//            ->andWhere('g.userType = :userType')
//            ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
            ->orderBy('c.id', 'ASC');
        },
        'choice_label' => function ($car) {
          return $car->getCarName();
        },
        'expanded' => false,
        'multiple' => false,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => CarReservation::class,
    ]);
  }
}
