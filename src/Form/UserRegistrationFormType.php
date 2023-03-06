<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegistrationFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder
      ->add('ime')
      ->add('prezime')
      ->add('datumRodjenja')
      ->add('pol')
      ->add('password')
      ->add('plainPassword')
      ->add('email')
      ->add('userType')
      ->add('jmbg')
      ->add('slika')
      ->add('adresa')
//      ->add('grad')
      ->add('telefon1')
      ->add('telefon2')
      ->add('vrstaZaposlenja')
      ->add('isSuspended');
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => User::class,
    ]);
  }
}
