<?php

namespace App\Form;

use App\Entity\ProjectFaktura;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FakturaFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $builder
      ->add('description')
      ->add('status', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => [
          'Nefakturisan' => 0,
          'Fakturisan' => 1
        ],
        'expanded' => false,
        'multiple' => false,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => ProjectFaktura::class,
    ]);
  }
}
