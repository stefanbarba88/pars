<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\RoundingIntervalData;
use App\Classes\Data\TimerPriorityData;
use App\Classes\Data\TipProjektaData;
use App\Classes\Data\TipProstoraData;
use App\Classes\Data\VrstaPlacanjaData;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Currency;
use App\Entity\Label;
use App\Entity\Lamela;
use App\Entity\Project;
use App\Entity\Projekat;
use App\Entity\Team;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class LamelaFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?Lamela {
        return $this->builder->getData();
      }

    };

    $builder
      ->add('title')
      ->add('description', TextareaType::class, [
        'required' => false,
      ])
        ->add('povrsina', NumberType::class, [
            'required' => false,
            'html5' => true,
            'attr' => [
                'min' => '0.01',
                'step' => '0.01'
            ],
        ])
        ->add('prostor', ChoiceType::class, [
            'placeholder' => '--Izaberite tip prostora--',
            'attr' => [
                'data-minimum-results-for-search' => 'Infinity',
            ],
            'choices' => TipProstoraData::form(),
            'expanded' => false,
            'multiple' => false,
        ])
      ->add('deadline', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Lamela::class,
      'allow_extra_fields' => true,
    ]);
  }
}
