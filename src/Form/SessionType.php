<?php

namespace App\Form;

use App\Entity\Session;
use App\Entity\Training;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start_date')
            ->add('end_date')
            ->add('comment')
            ->add('location')
        ;

        $builder->add('training', EntityType::class, array(
            'class' => Training::class,
            'choice_label' => 'title',
            'multiple' => false
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
