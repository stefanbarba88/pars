<?php

namespace App\Form;

use App\Classes\Data\PolData;
use App\Classes\Data\PotvrdaData;
use App\Classes\Data\UserRolesData;
use App\Classes\Data\VozackiData;
use App\Classes\Data\VrstaZaposlenjaData;
use App\Entity\City;
use App\Entity\User;
use App\Validator\JMBG;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;

class UserEditInfoFormType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options): void {
    $dataObject = new class($builder) {

      public function __construct(private readonly FormBuilderInterface $builder) {
      }

      public function getUser(): ?User {
        return $this->builder->getData();
      }

    };

    $userType = $dataObject->getUser()->getUserType();
    $builder
//      ->add('slava', DateType::class, [
//        'required' => false,
//        'widget' => 'single_text',
//        'format' => 'dd.MM.yyyy',
//        'html5' => false,
//        'input' => 'datetime_immutable'
//      ])
      ->add('ime')
      ->add('prezime')
      ->add('datumRodjenja', DateType::class, [
        'required' => false,
        'widget' => 'single_text',
        'format' => 'dd.MM.yyyy',
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
      ]);
    if ($userType != 1 and $userType != 2) {
      $builder
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
        ->add('telefon1', TextType::class, [
          'required' => false,
          'constraints' => [
            new Regex('/^\d{1,10}$/', 'Broj telefona#1 morate uneti u odgovarajućem formatu'),
          ],
          'attr' => [
            'maxlength' => '10'
          ],
        ])
        ->add('telefon2', TextType::class, [
          'required' => false,
          'constraints' => [
            new Regex('/^\d{1,10}$/', 'Broj telefona#2 morate uneti u odgovarajućem formatu'),
          ],
          'attr' => [
            'maxlength' => '10'
          ],
        ]);
    }
  }

  public function configureOptions(OptionsResolver $resolver): void {
    $resolver->setDefaults([
      'data_class' => User::class,
    ]);
  }
}
