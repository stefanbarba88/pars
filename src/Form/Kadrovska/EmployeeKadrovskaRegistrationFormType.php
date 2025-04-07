<?php

namespace App\Form\Kadrovska;

use App\Classes\AppConfig;
use App\Classes\Data\NivoObrazovanjaData;
use App\Classes\Data\PolData;
use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaZaposlenjaData;
use App\Entity\City;
use App\Entity\Company;
use App\Entity\Titula;
use App\Entity\User;
use App\Entity\ZaposleniPozicija;
use App\Validator\JMBG;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;

class EmployeeKadrovskaRegistrationFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getUser(): ?User {
        return $this->builder->getData();
      }

    };

    $plainUserType = $dataObject->getUser()->getPlainUserType();
    $company = $dataObject->getUser()->getCompany();

    if (is_null($company)) {
      $builder
        ->add('company', EntityType::class, [
          'required' => false,
          'placeholder' => '--Izaberite firmu--',
          'class' => Company::class,
          'query_builder' => function (EntityRepository $em) use ($company) {
            return $em->createQueryBuilder('g')
              ->andWhere('g.firma = :firma')
              ->andWhere('g.isSuspended = 0')
              ->setParameter(':company', $company->getFirma())
              ->orderBy('g.id', 'ASC');
          },
          'choice_label' => function ($firma) {
            return $firma->getTitle();
          },
          'expanded' => false,
          'multiple' => false,
        ]);
    }


      $builder->add('track', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ]);


    $builder
      ->add('ime')
      ->add('prezime')
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
      ->add('mestoRada', TextType::class, [
        'required' => false
      ])
      ->add('datumRodjenja', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
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
      ->add('slava', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])
//      ->add('isPrvaPomoc', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'choices' => PotvrdaData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])
//      ->add('neradniDan', ChoiceType::class, [
//        'required' => false,
//        'placeholder' => '--Izaberite neradni dan--',
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'choices' => NeradniDanData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])
      ->add('isLekarski', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
//      ->add('projectType', ChoiceType::class, [
//        'required' => false,
//        'placeholder' => '--Izaberite tip projekta--',
//        'choices' => TipProjektaData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])
//      ->add('vozacki', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'choices' => VozackiData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])
//      ->add('isMobile', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'choices' => PotvrdaData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])
//      ->add('isLaptop', ChoiceType::class, [
//        'attr' => [
//          'data-minimum-results-for-search' => 'Infinity',
//        ],
//        'choices' => PotvrdaData::form(),
//        'expanded' => false,
//        'multiple' => false,
//      ])
      ->add('pol', ChoiceType::class, [
        'placeholder' => '--Izaberite pol--',
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PolData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('jmbg', TextType::class, [
        'constraints' => [
          new Regex('/^\d{13}$/', 'JMBG morate uneti u odgovarajućem formatu'),
//          new JMBG('strict'),
        ],
        'attr' => [
          'maxlength' => '13',
          'minlength' => '13',
        ],
      ])
      ->add('vrstaZaposlenja', ChoiceType::class, [
        'required' => false,
        'placeholder' => '--Izaberite vrstu zaposlenja--',
        'choices' => VrstaZaposlenjaData::form(),
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
        'placeholder' => '--Izaberite tip korisnika--',
        'choices' => UserRolesData::formForFormByUserRoleKadrovskaEmployee($plainUserType),
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('slika', FileType::class, [
        'attr' => ['accept' => 'image/jpeg,image/png,image/jpg,image-gif', 'data-show-upload' => 'false'],
        // unmapped means that this field is not associated to any entity property
        'mapped' => false,
        // make it optional so you don't have to re-upload the PDF file
        // every time you edit the Product details
        'required' => false,
        // unmapped fields can't define their validation using annotations
        // in the associated entity, so you can use the PHP constraint classes
        'constraints' => [
          new Image([
            'maxSize' => '5120k',
            'maxSizeMessage' => 'Veličina slike je prevelika. Dozvoljena veličina je 5MB'
          ])
        ],
      ])

      ->add('grad', EntityType::class, [
        'required' => false,
        'placeholder' => '--Izaberite grad--',
        'class' => City::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => function ($grad) {
          return $grad->getFormTitle();
        },
        'expanded' => false,
        'multiple' => false,
      ])


      ->add('adresa', TextType::class, [
      'required' => false
    ])
      ->add('telefon1',TextType::class, [
        'required' => false,
        'constraints' => [
          new Regex('/^\d{1,10}$/', 'Broj telefona morate uneti u odgovarajućem formatu'),
        ],
          'attr' => [
            'maxlength' => '10'
        ],
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => User::class,
    ]);
  }
}
