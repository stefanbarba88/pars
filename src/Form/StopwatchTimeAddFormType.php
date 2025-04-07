<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\PrioritetData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Label;
use App\Entity\Project;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
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
use Symfony\Component\Validator\Constraints\Image;

class StopwatchTimeAddFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?StopwatchTime {
        return $this->builder->getData();
      }

    };
    $company = $dataObject->getReservation()->getTaskLog()->getTask()->getCompany();
    $task = $dataObject->getReservation()->getTaskLog()->getTask();
    $client = $task->getProject()->getClient()->toArray();

    if ($task->isIsExpenses()) {
      $builder
        ->add('expencesDesc', TextareaType::class, [
          'required' => false
        ])
        ->add('expencesPrice', NumberType::class, [
          'required' => false,
          'html5' => true,
          'attr' => [
            'min' => '0.01',
            'step' => '0.01'
          ],
        ]);
    }
    if (empty($client)) {
      $builder
        ->add('client', EntityType::class, [
          'placeholder' => '--Izaberite klijenta--',
          'required' => false,
          'class' => Client::class,
          'query_builder' => function (EntityRepository $em) use ($company) {
            return $em->createQueryBuilder('a')
              ->andWhere('a.isSuspended <> 1')
              ->andWhere('a.company = :company')
              ->setParameter(':company', $company)
//            ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
              ->orderBy('a.id', 'ASC');
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

      ->add('additionalActivity', TextareaType::class, [
        'required' => false
      ])
      ->add('additionalDesc', TextareaType::class, [
        'required' => false
      ])

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

      ->add('image', FileType::class, [
        'attr' => ['accept' => 'image/jpeg,image/png,image/jpg,image-gif', 'data-show-upload' => 'false'],
        // unmapped means that this field is not associated to any entity property
        'multiple' => true,
        'mapped' => false,
        // make it optional so you don't have to re-upload the PDF file
        // every time you edit the Product details
        'required' => false,
        // unmapped fields can't define their validation using annotations
        // in the associated entity, so you can use the PHP constraint classes
        'constraints' => [
          new All([
            new Image([
            'maxSize' => '5120k',
            'maxSizeMessage' => 'Veličina slike je prevelika. Dozvoljena veličina je 5MB.',
            'mimeTypesMessage' => 'Molimo Vas postavite dokument u jednom od ponuđenih formata za sliku.'
          ])
          ])
        ],
      ])

      ->add('activity', EntityType::class, [
        'class' => Activity::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('a')
            ->andWhere('a.company = :company or a.company IS NULL')
            ->setParameter(':company', $company)
//            ->andWhere('g.userType = :userType')
//            ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
            ->orderBy('a.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => true,
      ])

    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => StopwatchTime::class,
    ]);
  }
}
