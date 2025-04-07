<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\PrioritetData;
use App\Classes\Data\RepeatingIntervalData;
use App\Classes\Data\RoundingIntervalData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\Category;
use App\Entity\Label;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\Tool;
use App\Entity\User;
use App\Repository\UserRepository;
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

  private $em;
  public function __construct(UserRepository $em) {
    $this->em = $em;
  }
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getTask(): ?Task {
        return $this->builder->getData();
      }

    };

    $company = $dataObject->getTask()->getCompany();
    $settings = $company->getSettings();
    $datum = $dataObject->getTask()->getDatumKreiranja();

    if (!$settings->isAllUsers()) {
      $builder->add('assignedUsers', EntityType::class, [
        'class' => User::class,
        'choices' => $this->em->getUsersAvailable($datum),
        'choice_label' => function ($user) {
          return $user->getNameForForm();
        },
        'expanded' => false,
        'multiple' => true,
      ]);
    } else {
      $builder->add('assignedUsers', EntityType::class, [
        'class' => User::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.userType = :userType')
            ->andWhere('g.isSuspended = 0')
            ->andWhere('g.company = :company')
            ->setParameter(':company', $company)
            ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
            ->orderBy('g.prezime', 'ASC');
        },
        'choice_label' => function ($user) {
          return $user->getNameForForm();
        },
        'expanded' => false,
        'multiple' => true,
      ]);
    }

    if ($settings->isTool()) {
      $builder
        ->add('oprema', EntityType::class, [
          'required' => false,
          'class' => Tool::class,
          'query_builder' => function (EntityRepository $em) use ($company) {
            return $em->createQueryBuilder('o')
              ->andWhere('o.company = :company')
              ->andWhere('o.isSuspended = 0')
              ->setParameter(':company', $company)
              ->orderBy('o.id', 'ASC');
          },
          'choice_label' => 'title',
          'expanded' => false,
          'multiple' => true,
        ]);
    }
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
      ])
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

      ->add('label', EntityType::class, [
        'required' => false,
        'class' => Label::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.isTaskLabel = :isTaskLabel')
            ->andWhere('g.company = :company')
            ->andWhere('g.isSuspended = 0')
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
            ->andWhere('g.isSuspended = 0')
            ->setParameter(':company', $company)
            ->setParameter(':isTaskCategory', 1)
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => false,
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

      ->add('datumPonavljanja',  DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'input' => 'datetime_immutable',
      ])
      ->add('deadline', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'input' => 'datetime_immutable',
        'attr' => ['min' => date('Y-m-d')] // Postavljamo minimalni datum na trenutni datum
      ])
      ->add('title')
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
      ->add('isExpenses', ChoiceType::class, [
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

      ->add('isTimeRoundUp', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
        'data' => $settings->getIsTimeRoundUp()
      ])
      ->add('isFree', ChoiceType::class, [
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
        'data' => $settings->getMinEntry()
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
        'data' => $settings->getRoundingInterval(),
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
      ->add('activity', EntityType::class, [
        'required' => false,
        'class' => Activity::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('a')
            ->andWhere('a.company = :company')
            ->andWhere('a.isSuspended = 0')
            ->orWhere('a.company IS NULL')
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
              'maxSizeMessage' => 'Veličina fajla je prevelika. Dozvoljena veličina je 5MB.',
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
