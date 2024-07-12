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
use App\Entity\Tool;
use App\Entity\ToolReservation;
use App\Entity\User;
use App\Repository\ToolRepository;
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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;

class ToolReservationFormDetailsType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {


    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?ToolReservation {
        return $this->builder->getData();
      }

    };

    $company = $dataObject->getReservation()->getCompany();



    $builder
      ->add('tool', EntityType::class, [
        'required' => false,
        'placeholder' => '---Izaberite opremu---',
        'class' => Tool::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('g')
            ->where('g.isReserved IS NULL OR g.isReserved <> 1')
            ->andWhere('g.company = :company')
            ->setParameter(':company', $company)
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => function ($tool) {
          return $tool->getTitle();
        },
        'expanded' => false,
        'multiple' => false
      ])
      ->add('descStart');


  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => ToolReservation::class,
    ]);
  }
}
