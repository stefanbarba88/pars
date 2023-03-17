<?php

namespace App\Form;

use App\Classes\Data\UserRolesData;
use App\Entity\City;
use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
      ->add('pib')
      ->add('slika', FileType::class, [
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
            'maxSize' => '2048k',
            'maxSizeMessage' => 'Veličina slike je prevelika. Dozvoljena veličina je 2Mb'
          ])
        ],
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
