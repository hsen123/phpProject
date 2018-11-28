<?php

namespace App\Form\Type\UserFormTypes;

use App\Entity\User;
use App\Enums\Countries;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserCompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company')
            ->add('companyAdress')
            ->add('companyCity')
            ->add('companyPostalCode')
            ->add('companyCountry', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'choice_translation_domain' => 'countries',
                'placeholder' => 'pages.profile.country',
                'attr' => [
                    'class' => 'dropdown purple',
                ],
                'choices' => Countries::getallCountryCodesAsFormTypeChoices(),
            ])
            ->add('companyPhone')
            ->add('save', SubmitType::class, ['label' => 'Save']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
