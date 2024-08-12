<?php

namespace App\Form\Kadrovska;

use App\Classes\AppConfig;
use App\Classes\Data\NivoObrazovanjaData;
use App\Classes\Data\PotvrdaData;
use App\Classes\Data\TipProjektaData;
use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaZaposlenjaData;
use App\Entity\Titula;
use App\Entity\User;
use App\Entity\ZaposleniPozicija;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class EmployeeKadrovskaEditAccountFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {


    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getUser(): ?User {
        return $this->builder->getData();
      }

    };

    $plainUserType = $dataObject->getUser()->getPlainUserType();
    $userType = $dataObject->getUser()->getUserType();


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
      ]);
    if ($userType != UserRolesData::ROLE_SUPER_ADMIN and $userType != UserRolesData::ROLE_ADMIN) {
      $builder
//        ->add('userType', ChoiceType::class, [
//        'placeholder' => '--Izaberite tip korisnika--',
//        'choices' => UserRolesData::formForFormByUserRoleKadrovska($plainUserType),
//        'expanded' => false,
//        'multiple' => false,
//      ])
        ->add('pozicija', EntityType::class, [
          'required' => false,
          'placeholder' => '--Izaberite radno mesto--',
          'class' => ZaposleniPozicija::class,
          'query_builder' => function (EntityRepository $em) {
            return $em->createQueryBuilder('g')
              ->andWhere('g.company = :company')
              ->setParameter(':company', AppConfig::KADROVSKA)
              ->orderBy('g.id', 'ASC');
          },
          'choice_label' => 'title',
          'expanded' => false,
          'multiple' => false,
        ])

        ->add('zvanje', EntityType::class, [
          'required' => false,
          'placeholder' => '--Izaberite zvanje--',
          'class' => Titula::class,
          'query_builder' => function (EntityRepository $em) {
            return $em->createQueryBuilder('g')
              ->andWhere('g.isSuspended = 0')
              ->orderBy('g.id', 'ASC');
          },
          'choice_label' => function ($zvanje) {
            return $zvanje->getForForm();
          },
          'expanded' => false,
          'multiple' => false,
        ])
        ->add('mestoRada', TextType::class, [
          'required' => false
        ])
        ->add('track', ChoiceType::class, [
          'attr' => [
            'data-minimum-results-for-search' => 'Infinity',
          ],
          'choices' => PotvrdaData::form(),
          'expanded' => false,
          'multiple' => false,
        ])
        ->add('pocetakUgovora', DateType::class, [
          'required' => false,
          'widget' => 'single_text',
          'format' => 'dd.MM.yyyy',
          'html5' => false,
          'input' => 'datetime_immutable'
        ])
        ->add('krajUgovora', DateType::class, [
          'required' => false,
          'widget' => 'single_text',
          'format' => 'dd.MM.yyyy',
          'html5' => false,
          'input' => 'datetime_immutable'
        ])
      ->add('isLekarski', ChoiceType::class, [
        'required' => false,
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])

        ->add('nivoObrazovanja', ChoiceType::class, [
          'required' => false,
          'placeholder' => '--Izaberite nivo obrazovanja--',
          'choices' => NivoObrazovanjaData::form(),
          'expanded' => false,
          'multiple' => false,
        ])
        ->add('vrstaZaposlenja', ChoiceType::class, [
          'required' => false,
          'placeholder' => '--Izaberite vrstu zaposlenja--',
          'choices' => VrstaZaposlenjaData::form(),
          'expanded' => false,
          'multiple' => false,
        ]);
    }
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => User::class,
    ]);
  }
}
