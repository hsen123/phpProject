<?php
/**
 * Created by PhpStorm.
 * User: julius
 * Date: 05.06.18
 * Time: 16:39
 */

namespace App\Form\Type;


use App\Entity\Broadcast;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BroadcastCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('content', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Broadcast::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ));
    }
}
