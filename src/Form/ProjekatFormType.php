<?php

namespace App\Form;


use App\Classes\Data\UserRolesData;
use App\Entity\Client;
use App\Entity\Projekat;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjekatFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?Projekat {
        return $this->builder->getData();
      }

    };

    $builder
      ->add('title')
      ->add('description', TextareaType::class, [
        'required' => false,
      ])
      ->add('client', EntityType::class, [
        'class' => Client::class,
        'placeholder' => '--Izaberite klijenta--',
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.isSuspended = :isSuspended')
            ->setParameter(':isSuspended', 0)
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => false,
      ])
        ->add('povrsina', NumberType::class, [
            'required' => false,
            'html5' => true,
            'attr' => [
                'min' => '0.01',
                'step' => '0.01'
            ],
        ])
              ->add('assigned', EntityType::class, [
        'class' => User::class,
        'placeholder' => '--Izaberite zadužene na zadatku--',
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.userType = :userType')
            ->andWhere('g.isSuspended = 0')
            ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => function ($user) {
          return $user->getNameForForm();
        },
        'expanded' => false,
        'multiple' => true,
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
      'data_class' => Projekat::class,
      'allow_extra_fields' => true,
    ]);
  }
}
