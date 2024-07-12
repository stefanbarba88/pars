<?php

namespace App\Form;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\CalendarData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Classes\Data\UserRolesData;
use App\Entity\Availability;
use App\Entity\Calendar;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AvailabilityFormType extends AbstractType {
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

      ->add('type', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => AvailabilityData::formTipovi(),
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('zahtev', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => CalendarData::form(),
        'expanded' => false,
        'multiple' => false,
      ])



    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Availability::class,
    ]);
  }
}
