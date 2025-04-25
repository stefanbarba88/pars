<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\PrioritetData;
use App\Classes\Data\RepeatingIntervalData;
use App\Classes\Data\UserRolesData;
use App\Entity\Category;
use App\Entity\Label;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
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
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class
TaskEditInfoType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getTask(): ?Task {
        return $this->builder->getData();
      }

    };

    $task = $dataObject->getTask();
    $company = $dataObject->getTask()->getCompany();

    $production = $task->getProduction();

    if (is_null($production)) {
        $builder
            ->add('project', EntityType::class, [
                'placeholder' => '--Izaberite projekat--',
                'class' => Project::class,
                'query_builder' => function (EntityRepository $em) use ($company) {
                    return $em->createQueryBuilder('g')
                        ->andWhere('g.isSuspended = :isSuspended')
                        ->andWhere('g.company = :company')
                        ->setParameter(':company', $company)
                        ->setParameter(':isSuspended', 0)
                        ->orderBy('g.title', 'ASC');
                },
                'choice_label' => 'title',
                'expanded' => false,
                'multiple' => false,
            ]);
    }

    $builder
      ->add('description', TextareaType::class, [
        'required' => false
      ])

      ->add('label', EntityType::class, [
        'required' => false,
        'class' => Label::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.isTaskLabel = :isTaskLabel')
            ->andWhere('g.company = :company')
            ->setParameter(':company', $company)
            ->setParameter(':isTaskLabel', 1)
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => true,
      ])

      ->add('repeating', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('repeatingInterval', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'required' => false,
        'placeholder' => '--Izaberite period--',
        'choices' => RepeatingIntervalData::form(),
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('datumPonavljanja', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])

      ->add('category', EntityType::class, [
        'placeholder' => '--Izaberite kategoriju--',
        'required' => false,
        'class' => Category::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.isTaskCategory = :isTaskCategory')
            ->andWhere('g.company = :company')
            ->orWhere('g.company IS NULL')
            ->setParameter(':company', $company)
            ->setParameter(':isTaskCategory', 1)
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('priority', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'placeholder' => '--Izaberite nivo prioriteta--',
        'choices' => PrioritetData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('isExpenses', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('isFree', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('isSeparate', ChoiceType::class, [
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
        'input' => 'datetime_immutable',
        'html5' => false,
        'attr' => ['min' => date('Y-m-d')] // Postavljamo minimalni datum na trenutni datum
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Task::class,
    ]);
  }
}
