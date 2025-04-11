<?php

namespace App\Form;

use App\Classes\Data\PrioritetData;
use App\Entity\Client;
use App\Entity\Element;
use App\Entity\Product;
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


class ProductFormType extends AbstractType {
  private $em;
  public function __construct(ClientRepository $em) {
    $this->em = $em;
  }
  public function buildForm(FormBuilderInterface $builder, array $options): void {


    $builder
      ->add('title')
      ->add('productKey');
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => Product::class,
    ]);
  }
}
