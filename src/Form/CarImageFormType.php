<?php

namespace App\Form;

use App\Classes\Data\CleanData;
use App\Classes\Data\FuelData;
use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\City;
use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;

class CarImageFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

      $builder
        ->add('image', FileType::class, [
          'attr' => ['accept' => 'image/jpeg,image/png,image/jpg,image-gif', 'data-show-upload' => 'false'],
          // unmapped means that this field is not associated to any entity property
          'multiple' => true,
          'mapped' => false,
          // make it optional so you don't have to re-upload the PDF file
          // every time you edit the Product details
          'required' => false,
          // unmapped fields can't define their validation using annotations
          // in the associated entity, so you can use the PHP constraint classes
          'constraints' => [
            new All([
              new Image([
                'maxSize' => '5120k',
                'maxSizeMessage' => 'Veličina slike je prevelika. Dozvoljena veličina je 5MB.',
                'mimeTypesMessage' => 'Molimo Vas postavite dokument u jednom od ponuđenih formata za sliku.'
              ])
            ])
          ],
        ]);

    }



  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => CarReservation::class,
    ]);
  }
}
