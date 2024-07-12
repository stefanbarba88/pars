<?php

namespace App\Form;

use App\Classes\Data\CleanData;
use App\Classes\Data\FuelData;
use App\Classes\Data\PotvrdaData;
use App\Classes\Data\TipOpremeData;
use App\Classes\Data\UserRolesData;
use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\City;
use App\Entity\Client;
use App\Entity\ToolReservation;
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

class ToolReservationFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {


    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?ToolReservation {
        return $this->builder->getData();
      }

    };

    $tool = $dataObject->getReservation()->getTool();
    $company = $dataObject->getReservation()->getCompany();

    $builder
      ->add('user', EntityType::class, [
        'class' => User::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('d')
            ->andWhere('d.isSuspended = :isSuspended')
            ->andWhere('d.userType = :userType')
            ->andWhere('d.company = :company')
            ->setParameter(':company', $company)
            ->setParameter(':isSuspended', 0)
            ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
            ->orderBy('d.id', 'ASC');
        },
        'choice_label' => function ($user) {
          return $user->getFullName();
        },
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('descStart');
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => ToolReservation::class,
    ]);
  }
}
