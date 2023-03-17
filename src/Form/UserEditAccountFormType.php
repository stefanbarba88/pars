<?php

namespace App\Form;

use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaZaposlenjaData;
use App\Entity\User;
use App\Entity\ZaposleniPozicija;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class UserEditAccountFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $builder
      ->add('plainPassword', TextType::class, [
        'required' => false,
        'constraints' => [
          new Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', 'Lozinka mora imati minimum 8 karaktera (obavezno jedno veliko slovo, jedno malo slovo, jedan specijalan karakter i jednu cifru)'),
        ],
        'attr' => [
          'minlength' => '8',
        ],
      ])
      ->add('email', EmailType::class, [
        'constraints' => [
          new Regex('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', 'Email adresu morate uneti u odgovarajućem formatu'),
        ],
      ])
      ->add('userType', ChoiceType::class, [
        'choices' => UserRolesData::formForForm(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('pozicija', EntityType::class, [
        'class' => ZaposleniPozicija::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('vrstaZaposlenja', ChoiceType::class, [
      'choices' => VrstaZaposlenjaData::form(),
      'expanded' => false,
      'multiple' => false,
    ]);
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => User::class,
    ]);
  }
}
