<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlenght' => 2,
                    'maxlenght' => 50
                ],
                'label' => 'Pseudonyme',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 50])
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlenght' => 2,
                    'maxlenght' => 50
                ],
                'label' => 'E-mail',
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
            /*
            ->add('plainpassword', PasswordType::class, [
                'label' => 'Mot de passe requis pour modifier',
                'label_attr' => [
                    'form-label mt-4'
                    ]
            ])
            */
            ->add('Valider', SubmitType::class, [
                'attr' => [
                    'classe' => 'btn btn-primary mt-4'
                    ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
