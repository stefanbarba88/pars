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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Regex;

class SettingsFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?Settings {
        return $this->builder->getData();
      }

    };

    $settings = $dataObject->getReservation();

    if ($settings->isCalendar()) {
      $builder
        ->add('isAllUsers', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
        ->add('isPlanEmployee', ChoiceType::class, [
          'attr' => [
            'data-minimum-results-for-search' => 'Infinity',
          ],
          'choices' => PotvrdaData::form(),
          'expanded' => false,
          'multiple' => false,
        ]);
    }
    if ($settings->isCar()) {
      $builder
        ->add('minKm', NumberType::class, [
          'required' => true,
          'html5' => true,
          'attr' => [
            'min' => 0,
            'step' => '1'
          ],
        ]);
    }
//    if ($settings->isPlan()) {
//      $builder
//        ->add('isPlanToday', ChoiceType::class, [
//          'attr' => [
//            'data-minimum-results-for-search' => 'Infinity',
//          ],
//          'choices' => PotvrdaData::form(),
//          'expanded' => false,
//          'multiple' => false,
//        ])
//        ->add('isPlanTomorrow', ChoiceType::class, [
//          'attr' => [
//            'data-minimum-results-for-search' => 'Infinity',
//          ],
//          'choices' => PotvrdaData::form(),
//          'expanded' => false,
//          'multiple' => false,
//        ]);
//    }

    $builder

      ->add('isTimeRoundUp', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('isWidgete', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('email', EmailType::class, [
        'required' => false,
        'constraints' => [
          new Regex('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', 'Email adresu morate uneti u odgovarajućem formatu'),
        ],
      ])


      ->add('isGeolocation', ChoiceType::class, [
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
//      ->add('workWeek', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'placeholder' => '--Izaberite broj dana u radnoj nedelji--',
//        'choices' => WorkWeekData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])





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
      'allow_extra_fields' => true,
    ]);
  }
}
