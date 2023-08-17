<?php

namespace App\Form;

use App\Classes\Data\PolData;
use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Classes\Data\VozackiData;
use App\Classes\Data\VrstaZaposlenjaData;
use App\Entity\City;
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

class UserRegistrationFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {

    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getUser(): ?User {
        return $this->builder->getData();
      }

    };

    $plainUserType = $dataObject->getUser()->getPlainUserType();

    $builder
      ->add('ime')
      ->add('prezime')
      ->add('datumRodjenja', DateType::class, [
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
      ->add('isPrvaPomoc', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('isLekarski', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('vozacki', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => VozackiData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('isMobile', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('isLaptop', ChoiceType::class, [
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PotvrdaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
      ->add('pol', ChoiceType::class, [
        'placeholder' => 'Izaberite pol',
        'attr' => [
          'data-minimum-results-for-search' => 'Infinity',
        ],
        'choices' => PolData::form(),
        'expanded' => false,
        'multiple' => false,
      ])
//      ->add('jmbg', TextType::class, [
//        'constraints' => [
//          new Regex('/^\d{13}$/', 'JMBG morate uneti u odgovarajućem formatu'),
//          new JMBG('strict'),
//        ],
//        'attr' => [
//          'maxlength' => '13',
//          'minlength' => '13',
//        ],
//      ])
      ->add('vrstaZaposlenja', ChoiceType::class, [
        'placeholder' => 'Izaberite vrstu zaposlenja',
        'choices' => VrstaZaposlenjaData::form(),
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('plainPassword', TextType::class, [
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
        'choices' => UserRolesData::formForFormByUserRole($plainUserType),
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
            'maxSize' => '2048k',
            'maxSizeMessage' => 'Veličina slike je prevelika. Dozvoljena veličina je 2Mb'
          ])
        ],
      ])

      ->add('grad', EntityType::class, [
        'placeholder' => 'Izaberite grad',
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
      ->add('pozicija', EntityType::class, [
        'required' => false,
        'placeholder' => 'Izaberite poziciju',
        'class' => ZaposleniPozicija::class,
        'query_builder' => function (EntityRepository $em) {
          return $em->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC');
        },
        'choice_label' => 'title',
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('adresa')
      ->add('telefon1',TextType::class, [
        'required' => false,
        'constraints' => [
          new Regex('/^\d{1,10}$/', 'Broj službenog telefona morate uneti u odgovarajućem formatu'),
        ],
          'attr' => [
            'maxlength' => '10'
        ],
      ])
      ->add('telefon2',TextType::class, [
        'required' => false,
        'constraints' => [
          new Regex('/^\d{1,10}$/', 'Broj privatnog telefona morate uneti u odgovarajućem formatu'),
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
