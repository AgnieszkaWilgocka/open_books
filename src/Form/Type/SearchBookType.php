<?php

namespace App\Form\Type;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchBookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Search title...'
                ]  
            ]
        )
        ->add(
            'year',
            TextType::class,
            [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Year'
                ]
            ]
        )
        ->add(
            'category',
            EntityType::class,
            [
                'class' => Category::class,
                'required' => false,
                'label' => false,
                'choice_label' => 'title',
                'placeholder' => 'Category'
            ]
        );
    }
}
