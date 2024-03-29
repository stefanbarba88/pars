<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Entity\City;
use App\Entity\Client;
use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;

class CompanyFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

//    $dataObject = new class($builder) {
//
//      public function __construct(private readonly FormBuilderInterface $builder) {
//      }
//
//      public function getClient(): ?Client {
//        return $this->builder->getData();
//      }
//
//    };
//
//    $clientId = $dataObject->getClient()->getId();

    $builder
      ->add('title')
      ->add('adresa')
      ->add('email', EmailType::class, [
        'constraints' => [
          new Regex('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', 'Email adresu morate uneti u odgovarajućem formatu'),
        ],
      ])
      ->add('telefon1',TextType::class, [
        'constraints' => [
          new Regex('/^\d{1,10}$/', 'Broj telefona#1 morate uneti u odgovarajućem formatu'),
        ],
        'attr' => [
          'maxlength' => '10'
        ],
      ])
      ->add('telefon2',TextType::class, [
        'required' => false,
        'constraints' => [
          new Regex('/^\d{1,10}$/', 'Broj telefona#2 morate uneti u odgovarajućem formatu'),
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
            'maxSize' => '2048k',
            'maxSizeMessage' => 'Veličina slike je prevelika. Dozvoljena veličina je 2Mb'
          ])
        ],
      ]);

  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Company::class,
    ]);
  }
}
