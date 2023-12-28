<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\PrioritetData;
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


    $builder
      ->add('description', TextareaType::class, [
        'required' => false
      ])
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
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Task::class,
    ]);
  }
}
