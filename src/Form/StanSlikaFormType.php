<?php

namespace App\Form;

use App\Entity\Stan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;


class StanSlikaFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?Stan {
        return $this->builder->getData();
      }

    };

    $stan = $dataObject->getReservation();


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
                            'maxSize' => '2048k',
                            'maxSizeMessage' => 'Veličina slike je prevelika. Dozvoljena veličina je 2Mb.',
                            'mimeTypesMessage' => 'Molimo Vas postavite dokument u jednom od ponuđenih formata za sliku.'
                        ])
                    ])
                ],
            ]);

  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Stan::class,
      'allow_extra_fields' => true,
    ]);
  }
}
