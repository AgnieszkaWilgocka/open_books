<?php

namespace App\Form\Type;

use App\Entity\Book;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Book $book **/
        $book = $options['data'] ?? null;
        $isEdit = $book && $book->getId();
        
        $imageConstraints = [
            new Image(
                maxSize: '5M'
            ),
        ];

        if (!$isEdit || !$book->getImageFileName()) {
            $imageConstraints[] = new NotNull(
                message: 'Please upload an image'
            );
        }

        $builder->add(
            'title',
            TextType::class,
            [
                'attr' => [
                    'placeholder' => 'e.g Pinokio',
                ]
            ]
        )
        ->add(
            'year_of_release',
            IntegerType::class,
            [
                'attr' => [
                    'min' => 1800,
                    'max' => 2026,
                    'placeholder' => 'Enter the year of book release',
                ]
            ]
        )
        ->add(
            'pages',
            IntegerType::class,
            [
                'attr' => [
                    'min' => 1,
                    'placeholder' => 'Enter pages number',
                ]
            ]
        )
        ->add(
            'save',
            SubmitType::class,
            [
                'label' => 'Save Book'
            ]
        )
        ->add(
            'category',
            EntityType::class,
            [
                'class' => Category::class,
                'choice_label' => 'title'
            ]
        )
        ->add(
            'fileName',
            FileType::class,
            [
                'mapped' => false,
                'required' => false,
                'constraints' => $imageConstraints
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
