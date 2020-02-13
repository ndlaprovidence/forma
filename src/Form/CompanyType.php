<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('corporate_name')
            ->add('street', null, [
                'required' => true,
            ])
            ->add('postal_code', null, [
                'required' => true,
            ])
            ->add('city', null, [
                'required' => true,
            ])
            ->add('reference_number', null, [
                'required' => true,
            ])
            ->add('phone_number')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
