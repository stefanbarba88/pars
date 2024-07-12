<?php

namespace App\Form;

use App\Classes\Data\PrioritetData;
use App\Entity\Client;
use App\Entity\Ticket;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TicketFormType extends AbstractType {
  private $em;
  public function __construct(ClientRepository $em) {
    $this->em = $em;
  }
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getTask(): ?Ticket {
        return $this->builder->getData();
      }

    };

    $user = $dataObject->getTask()->getCreatedBy();
    $klijenti = $user->getClients()->toArray();

    $builder
      ->add('task')
      ->add('priority', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'placeholder' => '--Izaberite nivo prioriteta--',
        'choices' => PrioritetData::form(),
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
      ->add('client', EntityType::class, [
        'class' => Client::class,
        'choices' => $klijenti,
        'choice_label' => function ($client) {
          return $client->getTitle();
        },
        'expanded' => false,
        'multiple' => false,
      ]);

    ;
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Ticket::class,
    ]);
  }
}
