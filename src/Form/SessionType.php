<?php

namespace App\Form;

use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $builder
            ->add('start_date', DateTimeType::Class , [
                'data' => new \DateTime(),
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy'
            ])
            ->add('end_date')
            ->add('comment')
            ->add('training')
            ->add('location')
            ->add('instructors')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
