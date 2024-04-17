<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\RoundingIntervalData;
use App\Classes\Data\TimerPriorityData;
use App\Classes\Data\TipProjektaData;
use App\Classes\Data\VrstaPlacanjaData;
use App\Classes\Data\WorkWeekData;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Currency;
use App\Entity\Label;
use App\Entity\Project;
use App\Entity\Settings;
use App\Entity\Team;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SettingsFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?Project {
        return $this->builder->getData();
      }

    };



    $builder

      ->add('isTimeRoundUp', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('minEntry', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '1',
          'max' => '60'
        ],
      ])
      ->add('roundingInterval', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'required' => false,
        'placeholder' => '--Izaberite model zaokruživanja--',
        'choices' => RoundingIntervalData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('workWeek', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'placeholder' => '--Izaberite broj dana u radnoj nedelji--',
        'choices' => WorkWeekData::form(),
        'expanded' => false,
        'multiple' => false,
      ])





//      ->add('type', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'placeholder' => '--Izaberite tip projekta--',
//        'choices' => TipProjektaData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])



    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Settings::class,
    ]);
  }
}
