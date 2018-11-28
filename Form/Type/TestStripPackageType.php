<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02.03.2018
 * Time: 16:13.
 */

namespace App\Form\Type;

use App\Entity\TestStripPackage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestStripPackageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lotNumber', TextType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TestStripPackage::class,
        ]);
    }
}
