<?php

namespace App\Form;

use App\Classes\Data\PolData;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegistrationFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder
      ->add('ime')
      ->add('prezime')
      ->add('datumRodjenja', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd/MM/yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])
      ->add('pol', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PolData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('jmbg')

      ->add('password')
      ->add('plainPassword')
      ->add('email')
      ->add('userType')

      ->add('slika')
      ->add('adresa')

//      ->add('grad')
      ->add('telefon1')
      ->add('telefon2')
      ->add('vrstaZaposlenja');
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => User::class,
    ]);
  }
}
