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

    $builder
      ->add('task', TextareaType::class)
      ->add('user', EntityType::class, [
        'class' => User::class,
        'placeholder' => "---Izaberite zaposlenog---",
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->andWhere('g.userType = :userType')
            ->setParameter(':userType', UserRolesData::ROLE_MANAGER)
            ->orderBy('g.id', 'ASC');
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
