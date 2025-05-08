<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Entity\Car;
use App\Entity\City;
use App\Entity\Client;
use App\Entity\Project;
use App\Entity\Signature;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;

class SignatureFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?Signature {
        return $this->builder->getData();
      }

    };

    $signature = $dataObject->getReservation();

    $project = $signature->getRelation();
    $employee = $signature->getEmployee();


    if (!is_null($project)) {
      $builder
        ->add('relation', EntityType::class, [
          'class' => Project::class,
          'query_builder' => function (EntityRepository $em) use ($project) {
            return $em->createQueryBuilder('g')
              ->where('g.id = :project')
              ->setParameter(':project', $project->getId())
              ->orderBy('g.title', 'ASC');
          },
          'choice_label' => 'title',
          'expanded' => false,
          'multiple' => false,
        ]);
    } else {
      $builder
        ->add('relation', EntityType::class, [
          'placeholder' => '--Izaberite projekat--',
          'class' => Project::class,
          'query_builder' => function (EntityRepository $em) {
            return $em->createQueryBuilder('g')
              ->andWhere('g.isSuspended = :isSuspended')
              ->andWhere('g.company = :company')
              ->andWhere('g.isElaboratSigned = 1')
              ->setParameter(':company', 1)
              ->setParameter(':isSuspended', 0)
              ->orderBy('g.title', 'ASC');
          },
          'choice_label' => 'title',
          'expanded' => false,
          'multiple' => false,
        ]);
    }

    if (!is_null($employee)) {
      $builder
      ->add('employee', EntityType::class, [
        'class' => User::class,
        'query_builder' => function (EntityRepository $em) use ($employee) {
          return $em->createQueryBuilder('g')
            ->where('g.id = :employee')
            ->setParameter(':employee', $employee->getId())
            ->orderBy('g.prezime', 'ASC');
        },
        'choice_label' => function ($user) {
          return $user->getFullName();
        },
        'expanded' => false,
        'multiple' => false,
      ]);
    } else {
      $builder
      ->add('employee', EntityType::class, [
        'placeholder' => '--Izaberite zaposlenog--',
        'class' => User::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.userType = :userType')
            ->andWhere('g.isSuspended = 0')
            ->andWhere('g.company = :company')
            ->setParameter(':company', 1)
            ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
            ->orderBy('g.prezime', 'ASC');
        },
        'choice_label' => function ($user) {
          return $user->getFullName();
        },
        'expanded' => false,
        'multiple' => false,
      ]);
    }

    $builder
      ->add('isApproved', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('image', FileType::class, [
        'attr' => ['accept' => 'image/jpeg,image/png,image/jpg,image-gif', 'data-show-upload' => 'false'],
        // unmapped means that this field is not associated to any entity property
        'mapped' => false,
        // make it optional so you don't have to re-upload the PDF file
        // every time you edit the Product details
        'required' => false,
        // unmapped fields can't define their validation using annotations
        // in the associated entity, so you can use the PHP constraint classes
        'constraints' => [
          new Image([
            'maxSize' => '5120k',
            'maxSizeMessage' => 'Veličina slike je prevelika. Dozvoljena veličina je 5Mb'
          ])
        ],
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Signature::class,
    ]);
  }
}
