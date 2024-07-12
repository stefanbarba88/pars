<?php

namespace App\Form\Kadrovska;


use App\Entity\Project;
use App\Entity\Vacation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class VacationKadrovskaFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?Project {
        return $this->builder->getData();
      }

    };



    $builder

      ->add('old', IntegerType::class, [
        'required' => false,

      ])
      ->add('oldUsed', IntegerType::class, [
        'required' => false,

      ])
      ->add('new', IntegerType::class, [
        'required' => false,

      ])
      ->add('used1', IntegerType::class, [
        'required' => false,

      ])

      ->add('other1', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('slava', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])

    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Vacation::class,
      'allow_extra_fields' => true
    ]);
  }
}
