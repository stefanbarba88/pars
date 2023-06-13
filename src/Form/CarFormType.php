<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Entity\Car;
use App\Entity\City;
use App\Entity\Client;
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
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;

class CarFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $builder
      ->add('brand')
      ->add('model')
      ->add('plate')
      ->add('price', NumberType::class, [
        'required' => false,
        'html5' => true,
        'attr' => [
          'min' => '0.01',
          'step' => '0.01'
        ],
      ])
      ->add('datumRegistracije', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])
      ->add('datumNaredneRegistracije', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Car::class,
    ]);
  }
}
