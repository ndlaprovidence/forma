<?php

namespace App\Form;

use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $builder
            ->add('training')
            ->add('location', null, [
                'required' => true,
            ])
            ->add('instructors', null, [
                'required' => true,
            ])
            ->add('date', DateType::Class , [
                'widget' => 'single_text',
            ])
            ->add('start_time_am', TimeType::Class , [
                'widget' => 'single_text',
            ])
            ->add('end_time_am', TimeType::Class , [
                'widget' => 'single_text',
            ])
            ->add('start_time_pm', TimeType::Class , [
                'widget' => 'single_text',
            ])
            ->add('end_time_pm', TimeType::Class , [
                'widget' => 'single_text',
            ])
            ->add('comment')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
