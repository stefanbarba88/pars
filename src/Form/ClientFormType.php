<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Entity\City;
use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;

class ClientFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder
      ->add('title')
      ->add('adresa')
      ->add('telefon1',TextType::class, [
        'constraints' => [
          new Regex('/^\d{10}$/', 'Broj telefona#1 morate uneti u odgovarajućem formatu'),
        ],
        'attr' => [
          'maxlength' => '10'
        ],
      ])
      ->add('telefon2',TextType::class, [
        'required' => false,
        'constraints' => [
          new Regex('/^\d{10}$/', 'Broj telefona#2 morate uneti u odgovarajućem formatu'),
        ],
        'attr' => [
          'maxlength' => '10'
        ],
      ])
      ->add('pib',TextType::class, [
        'constraints' => [
          new Regex('/^\d+$/', 'PIB/VAT morate uneti u odgovarajućem formatu'),
        ],
      ])
      ->add('isSerbian', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('grad', EntityType::class, [
        'class' => City::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => function ($grad) {
          return $grad->getFormTitle();
        },
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('kontakt', EntityType::class, [
        'placeholder' => 'Izaberite lice za kontakt',
        'class' => User::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.userType = :userType')
            ->setParameter(':userType', UserRolesData::ROLE_CLIENT)
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => function ($user) {
          return $user->getFullName();
        },
        'expanded' => false,
        'multiple' => false,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Client::class,
    ]);
  }
}
