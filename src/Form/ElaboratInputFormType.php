<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\PrioritetData;
use App\Classes\Data\StatusElaboratData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\Comment;
use App\Entity\Elaborat;
use App\Entity\ElaboratInput;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

class ElaboratInputFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder
//      ->add('title')
      ->add('percent', NumberType::class, [
        'required' => false,
        'html5' => true,
        'attr' => [
          'min' => '0',
          'max' => '100',
          'step' => '1'
        ],
      ])
//      ->add('pdf', FileType::class, [
//        'attr' => ['accept' => '.pdf', 'data-show-upload' => 'false'],
//        'multiple' => true,
//        // unmapped means that this field is not associated to any entity property
//        'mapped' => false,
//        // make it optional so you don't have to re-upload the PDF file
//        // every time you edit the Product details
//        'required' => false,
//        // unmapped fields can't define their validation using annotations
//        // in the associated entity, so you can use the PHP constraint classes
//        'constraints' => [
//          new All([
//            new File([
//              'mimeTypes' => 'application/pdf',
//              'maxSize' => '5120k',
//              'maxSizeMessage' => 'Veličina fajla je prevelika. Dozvoljena veličina je 5Mb.',
//              'mimeTypesMessage' => 'Molimo Vas postavite dokument u .pdf formatu.'
//            ])
//          ])
//        ],
//      ])
//      ->add('image', FileType::class, [
//        'attr' => ['accept' => 'image/jpeg,image/png,image/jpg,image-gif', 'data-show-upload' => 'false'],
//        // unmapped means that this field is not associated to any entity property
//        'multiple' => true,
//        'mapped' => false,
//        // make it optional so you don't have to re-upload the PDF file
//        // every time you edit the Product details
//        'required' => false,
//        // unmapped fields can't define their validation using annotations
//        // in the associated entity, so you can use the PHP constraint classes
//        'constraints' => [
//          new All([
//            new Image([
//              'maxSize' => '2048k',
//              'maxSizeMessage' => 'Veličina slike je prevelika. Dozvoljena veličina je 2Mb.',
//              'mimeTypesMessage' => 'Molimo Vas postavite dokument u jednom od ponuđenih formata za sliku.'
//            ])
//          ])
//        ],
//      ])

      ->add('deadline', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])
      ->add('send', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])
      ->add('estimate', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])
      ->add('description', TextareaType::class, [
        'required' => false
      ])
      ->add('status', ChoiceType::class, [

        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => StatusElaboratData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => ElaboratInput::class,
    ]);
  }
}
