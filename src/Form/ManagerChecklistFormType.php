<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\PrioritetData;
use App\Classes\Data\RoundingIntervalData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\Category;
use App\Entity\Label;
use App\Entity\ManagerChecklist;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
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
use Symfony\Component\Validator\Constraints\NotBlank;

class ManagerChecklistFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getReservation(): ?ManagerChecklist {
        return $this->builder->getData();
      }

    };

    $company = $dataObject->getReservation()->getCompany();

    $builder
      ->add('task', TextareaType::class)
      ->add('priority', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'placeholder' => '---Izaberite nivo prioriteta---',
        'choices' => PrioritetData::form(),
        'expanded' => false,
        'multiple' => false,
        'data' => PrioritetData::MEDIUM,
      ])
      ->add('user', EntityType::class, [
        'class' => User::class,
        'placeholder' => "---Izaberite zaposlenog---",
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('u')
            ->where('u.userType = :userType')
            ->orWhere('u.userType = :userType1')
            ->orWhere('u.userType = :userType2')
            ->andWhere('u.isSuspended = :isSuspended')
            ->andWhere('u.company = :company')
            ->setParameter(':company', $company)
            ->setParameter(':userType', UserRolesData::ROLE_SUPER_ADMIN)
            ->setParameter(':userType1', UserRolesData::ROLE_ADMIN)
            ->setParameter(':userType2', UserRolesData::ROLE_MANAGER)
            ->setParameter(':isSuspended', 0)
            ->orderBy('u.userType', 'ASC')
            ->addOrderBy('u.prezime', 'ASC')
            ->getQuery()
            ->getResult();

        },
        'choice_label' => function ($user) {
          return $user->getFullName();
        },
        'expanded' => false,
        'multiple' => false,
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => ManagerChecklist::class,
    ]);
  }
}
