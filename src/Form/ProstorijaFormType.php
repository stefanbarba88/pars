<?php

namespace App\Form;

use App\Classes\Data\UserRolesData;
use App\Entity\Prostorija;
use App\Entity\Sifarnik;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;


class ProstorijaFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?Prostorija {
        return $this->builder->getData();
      }

    };


    $builder
      ->add('title')
        ->add('povrs', NumberType::class, [
            'required' => false,
            'html5' => true,
            'attr' => [
                'min' => '0.01',
                'step' => '0.01'
            ],
        ])
      ->add('description', TextareaType::class, [
        'required' => false,
      ])
      ->add('code', EntityType::class, [
            'placeholder' => '--Izaberite prostoriju--',
            'class' => Sifarnik::class,
            'query_builder' => function (EntityRepository $em) {
                return $em->createQueryBuilder('g')
                    ->orderBy('g.title', 'ASC');
            },
            'choice_label' => 'title',
            'expanded' => false,
            'multiple' => false,
        ]);

  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Prostorija::class,
      'allow_extra_fields' => true,
    ]);
  }
}
