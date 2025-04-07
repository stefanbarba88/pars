<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\PrioritetData;
use App\Classes\Data\UserRolesData;
use App\Entity\Category;
use App\Entity\Label;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
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

class ReassignTaskFormType extends AbstractType {
  private $em;
  public function __construct(UserRepository $em) {
    $this->em = $em;
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getTask(): ?Task {
        return $this->builder->getData();
      }

    };

    $company = $dataObject->getTask()->getCompany();
    $settings = $company->getSettings();
    $datum = $dataObject->getTask()->getDatumKreiranja();

    if ($settings->isCalendar()) {
      if ($settings->isAllUsers()) {
        $builder->add('assignedUsers', EntityType::class, [
          'class' => User::class,
          'query_builder' => function (EntityRepository $em) use ($company) {
            return $em->createQueryBuilder('g')
              ->andWhere('g.userType = :userType')
              ->andWhere('g.isSuspended = 0')
              ->andWhere('g.company = :company')
              ->setParameter(':company', $company)
              ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
              ->orderBy('g.prezime', 'ASC');
          },
          'choice_label' => function ($user) {
            return $user->getNameForForm();
          },
          'expanded' => false,
          'multiple' => true,
        ]);
      } else {
        $builder->add('assignedUsers', EntityType::class, [
          'class' => User::class,
          'choices' => $this->em->getUsersAvailable($datum),
          'choice_label' => function ($user) {
            return $user->getNameForForm();
          },
          'expanded' => false,
          'multiple' => true,
        ]);
      }
    } else {
      $builder->add('assignedUsers', EntityType::class, [
        'class' => User::class,
        'query_builder' => function (EntityRepository $em) use ($company) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.userType = :userType')
            ->andWhere('g.isSuspended = 0')
            ->andWhere('g.company = :company')
            ->setParameter(':company', $company)
            ->setParameter(':userType', UserRolesData::ROLE_EMPLOYEE)
            ->orderBy('g.prezime', 'ASC');
        },
        'choice_label' => function ($user) {
          return $user->getNameForForm();
        },
        'expanded' => false,
        'multiple' => true,
      ]);
    }
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Task::class,
      'allow_extra_fields' => true,
    ]);
  }
}
