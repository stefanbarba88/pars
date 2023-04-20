<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\VrstaPlacanjaData;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Currency;
use App\Entity\Label;
use App\Entity\Project;
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
use Symfony\Component\Validator\Constraints\Image;

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
            ->andWhere('g.isTaskLabel = :isTaskLabel')
            ->setParameter(':isTaskLabel', 0)
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
            ->andWhere('g.isTaskCategory = :isTaskCategory')
            ->setParameter(':isTaskCategory', 0)
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
            ->andWhere('g.isSuspended = :isSuspended')
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
        'html5' => true,
        'attr' => [
          'min' => '0.01',
          'step' => '0.01'
        ],
      ])
      ->add('pricePerHour', NumberType::class, [
        'required' => false,
        'html5' => true,
        'attr' => [
          'min' => '0.01',
          'step' => '0.01'
        ],
      ])
      ->add('pricePerTask', NumberType::class, [
        'required' => false,
        'html5' => true,
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

      ->add('pdf', FileType::class, [
        'attr' => ['accept' => 'pdf', 'data-show-upload' => 'false'],
        'multiple' => true,
        // unmapped means that this field is not associated to any entity property
        'mapped' => false,
        // make it optional so you don't have to re-upload the PDF file
        // every time you edit the Product details
        'required' => false,
        // unmapped fields can't define their validation using annotations
        // in the associated entity, so you can use the PHP constraint classes
        'constraints' => [
          new File([
            'mimeTypes' => 'application/pdf',
            'maxSize' => '5120k',
            'maxSizeMessage' => 'Veličina fajla je prevelika. Dozvoljena veličina je 5Mb.'
          ])
        ],
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Project::class,
    ]);
  }
}
