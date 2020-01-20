<?php

namespace App\Form;

use App\Entity\Goal;
use App\Entity\Training;
use App\Entity\TrainingCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrainingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('platform')
        ;

        $builder->add('training_category', EntityType::class, array(
            'class' => TrainingCategory::class,
            'choice_label' => 'title',
            'multiple' => false
        ));

        $builder->add('goals', EntityType::class, array(
            'class' => Goal::class,
            'choice_label' => 'title',
            'multiple' => true
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Training::class,
        ]);
    }
}
