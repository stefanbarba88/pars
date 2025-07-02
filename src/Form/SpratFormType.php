<?php

namespace App\Form;

use App\Entity\Sprat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SpratFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?Sprat {
        return $this->builder->getData();
      }

    };

    $builder
      ->add('title')
      ->add('description', TextareaType::class, [
        'required' => false,
      ])
        ->add('povrsina', NumberType::class, [
            'required' => false,
            'html5' => true,
            'attr' => [
                'min' => '0.01',
                'step' => '0.01'
            ],
        ])
      ->add('deadline', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Sprat::class,
      'allow_extra_fields' => true,
    ]);
  }
}
