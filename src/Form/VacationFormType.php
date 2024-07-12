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
use App\Entity\Vacation;
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

class VacationFormType extends AbstractType {
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
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('used1', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('used2', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('vacation1', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('vacationd1', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('vacationk1', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('vacation2', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('vacationd2', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('vacationk2', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('other1', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('other2', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('stopwatch1', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])
      ->add('stopwatch2', IntegerType::class, [
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
    ]);
  }
}
