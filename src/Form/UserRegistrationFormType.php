<?php

namespace App\Form;

use App\Classes\Data\PolData;
use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaZaposlenjaData;
use App\Entity\City;
use App\Entity\User;
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
    $builder
      ->add('ime')
      ->add('prezime')
      ->add('datumRodjenja', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.mm.yyyy',
        'html5' => false,
        'input' => 'datetime_immutable'
      ])
      ->add('pol', ChoiceType::class, [
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
          new JMBG('strict'),
        ],
        'attr' => [
          'maxlength' => '13',
          'minlength' => '13',
        ],
      ])
      ->add('vrstaZaposlenja', ChoiceType::class, [
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
        'choices' => UserRolesData::formForForm(),
        'expanded' => false,
        'multiple' => false,
      ])

      ->add('slika', FileType::class, [
        'attr' => ['accept' => 'image/jpeg,image/png,image/jpg', 'data-show-upload' => 'false'],

        // unmapped means that this field is not associated to any entity property
        'mapped' => false,
        // make it optional so you don't have to re-upload the PDF file
        // every time you edit the Product details
        'required' => false,
        // unmapped fields can't define their validation using annotations
        // in the associated entity, so you can use the PHP constraint classes
        'constraints' => [
//                    new File([
//                        'maxSize' => '10240k',
//                        'mimeTypes' => [
//                            'text/csv',
//                            'text/plain',
//                            'text/x-csv',
//                            'application/octet-stream'
//                        ],
//                        'mimeTypesMessage' => 'Morate uneti CSV fajl!',
//                    ])
          new Image()
        ],
      ])

      ->add('grad', EntityType::class, [
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
      ->add('adresa')
      ->add('telefon1',TextType::class, [
        'constraints' => [
          new Regex('/^\d{10}$/', 'Broj telefona morate uneti u odgovarajućem formatu'),
        ],
          'attr' => [
          'maxlength' => '10'
        ],
      ])
      ->add('telefon2',TextType::class, [
        'constraints' => [
          new Regex('/^\d{10}$/', 'Broj telefona morate uneti u odgovarajućem formatu'),
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
