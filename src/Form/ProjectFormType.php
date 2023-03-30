<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Label;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder
      ->add('title')
      ->add('description')
      ->add('isSuspended', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
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
      ->add('label', EntityType::class, [
        'class' => Label::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => true,
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
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Project::class,
    ]);
  }
}
