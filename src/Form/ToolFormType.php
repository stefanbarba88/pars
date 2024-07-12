<?php

namespace App\Form;

use App\Classes\Data\CleanData;
use App\Classes\Data\OcenaData;
use App\Classes\Data\PotvrdaData;
use App\Classes\Data\TipOpremeData;
use App\Classes\Data\UserRolesData;
use App\Entity\Car;
use App\Entity\City;
use App\Entity\Client;
use App\Entity\Tool;
use App\Entity\ToolType;
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

class ToolFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $builder
      ->add('title')
      ->add('serial')
      ->add('sertifikat',ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('model')
      ->add('type', EntityType::class, [
        'placeholder' => '--Izaberite tip opreme--',
        'class' => ToolType::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->orderBy('g.title', 'ASC');
        },
        'choice_label' => function ($tool) {
          return $tool->getTitle();
        },
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('ocena', ChoiceType::class, [
        'required' => false,
        'placeholder' => '--Ocenite uređaj--',
        'choices' => OcenaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('description')
      ->add('datumSertifikata', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ]);

  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Tool::class,
    ]);
  }
}
