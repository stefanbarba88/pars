<?php

namespace App\Form\Kadrovska;

use App\Classes\Data\CalendarData;
use App\Entity\Calendarkadr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class KadrovskaCalendarFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
//    $dataObject = new class($builder) {
//
//      public function __construct(private readonly FormBuilderInterface $builder) {
//      }
//
//      public function getCalendar(): ?Calendar {
//        return $this->builder->getData();
//      }
//
//    };
//
//    $calendar = $dataObject->getCalendar();
//
//    if ($calendar->getUser()->isEmpty()) {
//      $builder
//        ->add('user', EntityType::class, [
//          'placeholder' => '--Izaberite zaposlenog--',
//          'class' => User::class,
//          'query_builder' => function (EntityRepository $em) {
//            return $em->createQueryBuilder('g')
//              ->andWhere('g.isSuspended = :isSuspended')
//              ->andWhere('g.userType = :type')
//              ->setParameter(':isSuspended', 0)
//              ->setParameter(':type', UserRolesData::ROLE_EMPLOYEE)
//              ->orderBy('g.prezime', 'ASC');
//          },
//          'choice_label' => 'fullName',
//          'expanded' => false,
//          'multiple' => true,
//        ]);
//    }

    $builder
      ->add('aneks', TextType::class, [
        'required' => false
      ])
      ->add('note', TextareaType::class, [
        'required' => false
      ])

      ->add('start', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])
      ->add('finish', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])
      ->add('type', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => CalendarData::formKadrovska(),
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('days', IntegerType::class, [
        'required' => false,
        'attr' => [
          'min' => '0',
        ],
      ])

    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Calendarkadr::class,
    ]);
  }
}
