<?php

namespace App\Form;

use App\Classes\Data\PotvrdaData;
use App\Classes\Data\PrioritetData;
use App\Entity\Client;
use App\Entity\Element;
use App\Entity\Nalog;
use App\Entity\Product;
use App\Entity\Production;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\Ticket;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class NalogFormType extends AbstractType {
  private $em;
  public function __construct(ClientRepository $em) {
    $this->em = $em;
  }
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getNalog(): ?Production {
        return $this->builder->getData();
      }

    };

    $company = $dataObject->getNalog()->getCompany();
    $project = $dataObject->getNalog()->getProject();


    if (!is_null($project)) {
      $builder
        ->add('project', EntityType::class, [
          'class' => Project::class,
            'attr' => [
                'data-minimum-results-for-search' => 'Infinity',
            ],
          'query_builder' => function (EntityRepository $em) use ($project) {
            return $em->createQueryBuilder('g')
              ->andWhere('g.id = :project')
              ->setParameter(':project', $project->getId())
              ->orderBy('g.title', 'ASC');
          },
          'choice_label' => 'title',
          'expanded' => false,
          'multiple' => false,
        ]);
    } else {
      $builder
        ->add('project', EntityType::class, [
          'placeholder' => '--Izaberite projekat--',
          'class' => Project::class,
          'query_builder' => function (EntityRepository $em) use ($company) {
            return $em->createQueryBuilder('g')
              ->andWhere('g.isSuspended = :isSuspended')
              ->andWhere('g.company = :company')
              ->setParameter(':company', $company)
              ->setParameter(':isSuspended', 0)
              ->orderBy('g.title', 'ASC');
          },
          'choice_label' => 'title',
          'expanded' => false,
          'multiple' => false,
        ]);
    }

    $builder
      ->add('description')
      ->add('deadline', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'input' => 'datetime_immutable',
        'html5' => false,
      ])
      ->add('productKey');
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' =>Production::class,
      'allow_extra_fields' => true,
    ]);
  }
}
