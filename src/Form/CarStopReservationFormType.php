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
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Security\Core\Security;
class CarStopReservationFormType extends AbstractType {
  private $security;

  public function __construct(Security $security) {
    $this->security = $security;
  }
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }
    
      public function getReservation(): ?CarReservation {
        return $this->builder->getData();
      }

    };

    $minKm = $dataObject->getReservation()->getKmStart();
    $user = $this->security->getUser();

    if ($user->getUserType() != UserRolesData::ROLE_EMPLOYEE) {
      $builder
        ->add('kmStop', NumberType::class, [
          'attr' => [
            'min' => $minKm
          ],
          'required' => true,
          'html5' => true,
        ]);
    } else {
      $builder
        ->add('kmStop', NumberType::class, [
          'attr' => [
            'min' => $minKm,
            'max' => $minKm + 500,
          ],
          'required' => true,
          'html5' => true,
        ]);
    }



    $builder

        ->add('fuelStop', ChoiceType::class, [
          'placeholder' => '--Izaberite nivo goriva--',
          'choices' => FuelData::form(),
          'expanded' => false,
          'multiple' => false,
        ])
        ->add('cleanStop', ChoiceType::class, [
          'placeholder' => '--Izaberite nivo čistoće--',
          'choices' => CleanData::form(),
          'expanded' => false,
          'multiple' => false,
        ])
        ->add('descStop');
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => CarReservation::class,
    ]);
  }
}
