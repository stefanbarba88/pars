<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class TaskAddDocsType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder
      ->add('pdf', FileType::class, [
        'attr' => ['accept' => '.pdf', 'data-show-upload' => 'false'],
        'multiple' => true,
        // unmapped means that this field is not associated to any entity property
        'mapped' => false,
        // make it optional so you don't have to re-upload the PDF file
        // every time you edit the Product details
        'required' => false,
        // unmapped fields can't define their validation using annotations
        // in the associated entity, so you can use the PHP constraint classes
        'constraints' => [
          new All([
            new File([
              'mimeTypes' => 'application/pdf',
              'maxSize' => '5120k',
              'maxSizeMessage' => 'Veličina fajla je prevelika. Dozvoljena veličina je 5MB.',
              'mimeTypesMessage' => 'Molimo Vas postavite dokument u .pdf formatu.'
            ])
          ])
        ],
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Task::class,
    ]);
  }
}
