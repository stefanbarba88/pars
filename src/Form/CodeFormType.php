<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Entity\Sifarnik;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CodeFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder
      ->add('title')
        ->add('titleShort')
        ->add('struktura', NumberType::class, [
            'required' => false,
            'html5' => true,
            'attr' => [
                'min' => '0',
                'step' => '0.1'
            ],
        ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Sifarnik::class,
    ]);
  }
}
