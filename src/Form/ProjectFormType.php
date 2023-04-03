<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaPlacanjaData;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Currency;
use App\Entity\Label;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class ProjectFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder
      ->add('title')
      ->add('description', TextareaType::class)
      ->add('label', EntityType::class, [
        'placeholder' => 'Izaberite oznaku',
        'class' => Label::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('category', EntityType::class, [
        'class' => Category::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => true,
      ])

      ->add('client', EntityType::class, [
        'class' => Client::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
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

      ->add('payment', ChoiceType::class, [
        'placeholder' => 'Izaberite tip finansiranja',
        'choices' => VrstaPlacanjaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('currency', EntityType::class, [
        'placeholder' => 'Izaberite valutu',
        'class' => Currency::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => function ($currency) {
          return $currency->getFormTitle();
        },
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('price', NumberType::class, [
        'required' => false,
        'attr' => [
          'min' => '0.01',
          'step' => '0.01'
        ],
      ])
      ->add('pricePerHour', NumberType::class, [
        'required' => false,
        'attr' => [
          'min' => '0.01',
          'step' => '0.01'
        ],
      ])
      ->add('pricePerTask', NumberType::class, [
        'required' => false,
        'attr' => [
          'min' => '0.01',
          'step' => '0.01'
        ],
      ])

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
      ->add('roundingInterval', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '1',
          'max' => '60'
        ],
      ])

      ->add('isEstimate', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
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
      'data_class' => Project::class,
    ]);
  }
}
