<?php

namespace App\Form\FOSUserBundle;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('username');
        $builder
            ->add('email', EmailType::class,
                [
                    'label' => false,
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => [
                        'placeholder' => 'form.email',
                        'class' => 'input-transparent',
                    ],
                ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => ['translation_domain' => 'FOSUserBundle'],
                'first_options' => [
                    'label' => false,
                    'attr' => ['placeholder' => 'form.password', 'class' => 'input-transparent'],
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => ['placeholder' => 'form.password_confirmation', 'class' => 'input-transparent'],
                ],
                'invalid_message' => 'fos_user.password.mismatch',
            ])
            ->add('displayName', TextType::class,
                [
                    'label' => false,
                    'required' => false,
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => [
                        'placeholder' => 'form.display_name',
                        'class' => 'input-transparent',
                    ],
                ])
            ->add('firstName', TextType::class,
                [
                    'label' => false,
                    'required' => false,
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => [
                        'placeholder' => 'form.first_name',
                        'class' => 'input-transparent',
                    ],
                ])
            ->add('lastName', TextType::class,
                [
                    'label' => false,
                    'required' => false,
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => [
                        'placeholder' => 'form.last_name',
                        'class' => 'input-transparent',
                    ],
                ])
            ->add('company', TextType::class,
                [
                    'label' => false,
                    'required' => false,
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => [
                        'placeholder' => 'form.company',
                        'class' => 'input-transparent',
                    ],
                ])
            ->add('segment', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'translation_domain' => 'FOSUserBundle',
                'placeholder' => 'form.segment',
                'attr' => [
                    'class' => 'dropdown yellow',
                ],
                'choices' => [
                    'segment.0' => 0,
                    'segment.1' => 1,
                    'segment.2' => 2,
                    'segment.3' => 3,
                    'segment.4' => 4,
                    'segment.5' => 5,
                    'segment.6' => 6,
                    'segment.7' => 7,
                    'segment.8' => 8,
                    'segment.9' => 9,
                    'segment.10' => 10,
                    'segment.11' => 11,
                    'segment.12' => 12,
                ],
            ])
            ->add('tos', CheckboxType::class, [
                'mapped' => false,
                'attr' => [
                    'class' => 'checkbox on-yellow-inactive',
                ],
                'translation_domain' => 'FOSUserBundle',
                'required' => true,
            ])
            ->add('newsletter', CheckboxType::class, [
                'mapped' => false,
                'attr' => [
                    'class' => 'checkbox on-yellow-inactive',
                ],
                'translation_domain' => 'FOSUserBundle',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefined(['color']);

        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_token_id' => 'registration',
            // BC for SF < 2.8
            'intention' => 'registration',
        ]);
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }
}
