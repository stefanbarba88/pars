<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\PrioritetData;
use App\Classes\Data\RoundingIntervalData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\Category;
use App\Entity\Label;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\Tool;
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
use Symfony\Component\Validator\Constraints\NotBlank;

class PhoneTaskFormType extends AbstractType {
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
    $projectType = 0;
//    if (!$task->getAssignedUsers()->isEmpty()) {
//      $user = $task->getAssignedUsers()->first();
//      $projectType = $user->getProjectType();
//    }

    if (is_null($task->getProject())) {
      if ($projectType == 0) {
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
      } else {
        $builder
          ->add('project', EntityType::class, [
            'placeholder' => '--Izaberite projekat--',
            'class' => Project::class,
            'query_builder' => function (EntityRepository $em) use ($projectType, $company) {
              return $em->createQueryBuilder('g')
                ->andWhere('g.isSuspended = :isSuspended')
                ->andWhere('g.company = :company')
                ->andWhere('g.type = :type')
                ->setParameter(':company', $company)
                ->setParameter(':isSuspended', 0)
                ->setParameter(':type', $projectType)
                ->orderBy('g.title', 'ASC');
            },
            'choice_label' => 'title',
            'expanded' => false,
            'multiple' => false,
          ]);
      }

    }

    $builder

      ->add('description', TextareaType::class, [
        'required' => false
      ])
//      ->add('isFree', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'choices' => PotvrdaData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])
      ->add('oprema', EntityType::class, [
        'required' => false,
        'class' => Tool::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('o')
            ->andWhere('o.type <> :laptop')
            ->andWhere('o.type <> :telefon')
            ->andWhere('o.company = :company')
            ->setParameter(':company', $company)
            ->setParameter(':laptop', 1)
            ->setParameter(':telefon', 2)
            ->orderBy('o.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => true,
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
//      ->add('deadline', DateType::class, [
//        'required' => false,
//        'widget' => 'single_text',
//        'input' => 'datetime_immutable',
//        'attr' => ['min' => date('Y-m-d')] // Postavljamo minimalni datum na trenutni datum
//      ])
//      ->add('datumKreiranja', DateType::class, [
//        'required' => true,
//        'widget' => 'single_text',
//        'input' => 'datetime_immutable',
//        'attr' => ['min' => date('Y-m-d')] // Postavljamo minimalni datum na trenutni datum
//      ])
//      ->add('assignedUsers', EntityType::class, [
//        'class' => User::class,
//        'query_builder' => function (EntityRepository $em) {
//          return $em->createQueryBuilder('g')
//            ->andWhere('g.userType = :userType')
//            ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
//            ->orderBy('g.id', 'ASC');
//        },
//        'choice_label' => function ($user) {
//          return $user->getNameForForm();
//        },
//        'expanded' => false,
//        'multiple' => true,
//      ])
//      ->add('priorityUserLog', EntityType::class, [
//        'class' => User::class,
//        'placeholder' => '--Izaberite dnevnik--',
//        'query_builder' => function (EntityRepository $em) {
//          return $em->createQueryBuilder('g')
//            ->andWhere('g.userType = :userType')
//            ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
//            ->orderBy('g.id', 'ASC');
//        },
//        'choice_label' => function ($user) {
//          return $user->getNameForForm();
//        },
//        'expanded' => false,
//        'multiple' => false,
//        'choice_value' => 'id',
//      ])

//      ->add('isTimeRoundUp', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'choices' => PotvrdaData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])
//      ->add('minEntry', IntegerType::class, [
//        'required' => false,
//        'attr' => [
//          'min' => '1',
//          'max' => '60'
//        ],
//      ])
//      ->add('roundingInterval', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'required' => false,
//        'placeholder' => '--Izaberite model zaokruživanja--',
//        'choices' => RoundingIntervalData::form(),
//        'expanded' => false,
//        'multiple' => false,
//        'data' => RoundingIntervalData::MIN_15,
//      ])

//      ->add('isEstimate', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'choices' => PotvrdaData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])
//      ->add('isExpenses', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'choices' => PotvrdaData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])
      ->add('activity', EntityType::class, [
        'required' => false,
        'class' => Activity::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('a')
            ->andWhere('a.company = :company')
            ->setParameter(':company', $company)
            ->orderBy('a.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => true,
      ])
//      ->add('isPriority', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'choices' => PotvrdaData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])

      ->add('pdf', FileType::class, [
        'attr' => ['accept' => '.pdf', 'data-show-upload' => 'false'],
        'multiple' => true,
        // unmapped means that this field is not associated to any entity property
        'mapped' => false,
        // make it optional so you don't have to re-upload the PDF file
        // every time you edit the Product details
        'required' => false,
        // unmapped fields can't define their validation using annotations
        // in the associated entity, so you can use the PHP constraint classes
        'constraints' => [
          new All([
            new File([
              'mimeTypes' => 'application/pdf',
              'maxSize' => '5120k',
              'maxSizeMessage' => 'Veličina fajla je prevelika. Dozvoljena veličina je 5Mb.',
              'mimeTypesMessage' => 'Molimo Vas postavite dokument u .pdf formatu.'
            ])
          ])
        ],
      ])


    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Task::class,
      'allow_extra_fields' => true,
    ]);
  }
}
