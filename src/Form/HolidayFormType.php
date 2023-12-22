<?php

namespace App\Form;

use App\Classes\Data\TipNeradnihDanaData;
use App\Entity\Holiday;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class HolidayFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $builder
      ->add('title')
      ->add('type', ChoiceType::class, [
        'placeholder' => '--Izaberite tip--',
        'choices' => TipNeradnihDanaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('datum', DateType::class, [
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
      'data_class' => Holiday::class,
    ]);
  }
}
