<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchRentalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'bookTitle',
            TextType::class,
            [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Title'
                ]
            ]
        )
        ->add(
            'writer',
            TextType::class,
            [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Writer'
                ]
            ]
        )
        ->add(
            'deadline',
            DateTimeType::class,
            [
                'required' => false,
                'label' => false,
            ]
        )
        ->add(
            'bookCategory',
            TextType::class,
            [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Category'
                ]
            ]
        );
    }
}
