<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\RoundingIntervalData;
use App\Classes\Data\TimerPriorityData;
use App\Classes\Data\TipProjektaData;
use App\Classes\Data\VrstaPlacanjaData;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Currency;
use App\Entity\Label;
use App\Entity\Project;
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

class ProjectFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?Project {
        return $this->builder->getData();
      }

    };

    $company = $dataObject->getReservation()->getCompany();

    $builder
      ->add('title')
      ->add('description', TextareaType::class, [
        'required' => false,
      ])
      ->add('important', TextareaType::class, [
        'required' => false,
      ])
      ->add('label', EntityType::class, [
        'required' => false,
        'class' => Label::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.isTaskLabel = :isTaskLabel')
            ->andWhere('g.company = :company')
            ->setParameter(':company', $company)
            ->setParameter(':isTaskLabel', 0)
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => true,
      ])
      ->add('category', EntityType::class, [
        'required' => false,
        'class' => Category::class,
        'placeholder' => '--Izaberite kategoriju--',
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.isTaskCategory = :isTaskCategory')
            ->andWhere('g.company = :company')
            ->setParameter(':company', $company)
            ->setParameter(':isTaskCategory', 0)
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('client', EntityType::class, [
        'class' => Client::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.isSuspended = :isSuspended')
            ->andWhere('g.company = :company')
            ->setParameter(':company', $company)
            ->setParameter(':isSuspended', 0)
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => true,
      ])
      ->add('isClientView', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('isViewLog', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('payment', ChoiceType::class, [
        'placeholder' => '--Izaberite tip finansiranja--',
        'choices' => VrstaPlacanjaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])

//      ->add('team', EntityType::class, [
//        'required' => false,
//        'class' => Team::class,
//        'query_builder' => function (EntityRepository $em) {
//          return $em->createQueryBuilder('g')
//            ->orderBy('g.id', 'ASC');
//        },
//        'choice_label' => 'title',
//        'expanded' => false,
//        'multiple' => true,
//      ])

//      ->add('currency', EntityType::class, [
//        'class' => Currency::class,
//        'query_builder' => function (EntityRepository $em) {
//          return $em->createQueryBuilder('g')
//            ->orderBy('g.id', 'ASC');
//        },
//        'choice_label' => function ($currency) {
//          return $currency->getFormTitle();
//        },
//        'expanded' => false,
//        'multiple' => false,
//      ])
//      ->add('price', NumberType::class, [
//        'required' => false,
//        'html5' => true,
//        'attr' => [
//          'min' => '0.01',
//          'step' => '0.01'
//        ],
//      ])
//      ->add('pricePerHour', NumberType::class, [
//        'required' => false,
//        'html5' => true,
//        'attr' => [
//          'min' => '0.01',
//          'step' => '0.01'
//        ],
//      ])
//      ->add('pricePerTask', NumberType::class, [
//        'required' => false,
//        'html5' => true,
//        'attr' => [
//          'min' => '0.01',
//          'step' => '0.01'
//        ],
//      ])
//      ->add('pricePerDay', NumberType::class, [
//        'required' => false,
//        'html5' => true,
//        'attr' => [
//          'min' => '0.01',
//          'step' => '0.01'
//        ],
//      ])
//      ->add('pricePerMonth', NumberType::class, [
//        'required' => false,
//        'html5' => true,
//        'attr' => [
//          'min' => '0.01',
//          'step' => '0.01'
//        ],
//      ])

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
        'data' => RoundingIntervalData::MIN_15,
      ])

      ->add('timerPriority', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'required' => false,
        'placeholder' => '--Izaberite prioritet dnevnika--',
        'choices' => TimerPriorityData::form(),
        'expanded' => false,
        'multiple' => false,
        'data' => TimerPriorityData::ROLE_GEO
      ])

      ->add('type', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'placeholder' => '--Izaberite tip projekta--',
        'choices' => TipProjektaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])

//      ->add('isEstimate', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'choices' => PotvrdaData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])
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
      'data_class' => Project::class,
    ]);
  }
}
