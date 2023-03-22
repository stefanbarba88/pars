<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Country;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class CityFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $builder
      ->add('title')
      ->add('ptt',TextType::class, [
        'constraints' => [
          new Regex('/^\d{5}$/', 'PTT broj morate uneti u odgovarajućem formatu.'),
        ],
        'attr' => [
          'maxlength' => '5'
        ],
      ])
      ->add('region')
      ->add('municipality')
      ->add('drzava', EntityType::class, [
        'class' => Country::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => function ($drzava) {
          return $drzava->getFormTitle();
        },
        'expanded' => false,
        'multiple' => false,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => City::class,
    ]);
  }
}
