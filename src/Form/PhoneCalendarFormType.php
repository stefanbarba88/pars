<?php

namespace App\Form;

use App\Classes\Data\CalendarData;
use App\Entity\Calendar;
use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;


class PhoneCalendarFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {


    $builder
      ->add('note', TextareaType::class, [
        'required' => false
      ])

      ->add('start', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'input' => 'datetime_immutable',

      ])
      ->add('finish', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'input' => 'datetime_immutable',

      ])
      ->add('type', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => CalendarData::form(),
        'expanded' => false,
        'multiple' => false,
      ])



    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Calendar::class,
    ]);
  }
}
