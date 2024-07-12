<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\RoundingIntervalData;
use App\Classes\Data\TimerPriorityData;
use App\Classes\Data\TipTroskovaData;
use App\Classes\Data\VrstaPlacanjaData;
use App\Entity\Car;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Currency;
use App\Entity\Expense;
use App\Entity\Label;
use App\Entity\Project;
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
use Symfony\Component\Validator\Constraints\File;

class ExpenseFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?Expense {
        return $this->builder->getData();
      }

    };

    $car = $dataObject->getReservation()->getCar();
    $company = $dataObject->getReservation()->getCompany();

    if (is_null($car)) {
      $builder
        ->add('car', EntityType::class, [
          'class' => Car::class,
          'query_builder' => function (EntityRepository $em) use ($company) {
            return $em->createQueryBuilder('c')
              ->andWhere('c.isSuspended = :isSuspended')
              ->andWhere('c.company = :company')
              ->setParameter(':company', $company)
              ->setParameter(':isSuspended', 0)
              ->orderBy('c.id', 'ASC');
          },
          'choice_label' => function ($car) {
            return $car->getCarName();
          },
          'expanded' => false,
          'multiple' => false,
        ]);
    } else {
      $builder
        ->add('car', EntityType::class, [
          'class' => Car::class,
          'query_builder' => function (EntityRepository $em) use ($car) {
            return $em->createQueryBuilder('c')
              ->andWhere('c.id = :id')
              ->setParameter(':id', $car)
              ->orderBy('c.id', 'ASC');
          },
          'choice_label' => function ($car) {
            return $car->getCarName();
          },
          'expanded' => false,
          'multiple' => false,
        ]);
    }

    $builder
      ->add('description', TextareaType::class,  ['required' => false])
      ->add('type', ChoiceType::class, [
        'placeholder' => '--Izaberite tip troška--',
        'choices' => TipTroskovaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('km', NumberType::class, [
        'required' => true,
        'html5' => true,
      ])
      ->add('price', NumberType::class, [
        'required' => false,
        'html5' => true,
        'attr' => [
          'min' => '0.01',
          'step' => '0.01'
        ],
      ])
      ->add('date', DateType::class, [
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
      'data_class' => Expense::class,
    ]);
  }
}
