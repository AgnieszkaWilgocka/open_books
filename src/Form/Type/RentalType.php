<?php

namespace App\Form\Type;

use App\Entity\Book;
use App\Entity\Rental;
use App\Repository\BookRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RentalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'book',
            EntityType::class,
            [
                'class' => Book::class,
                'label' => false,
                'query_builder' => fn(BookRepository $bookRepository) => 
                    $bookRepository->queryAvailable(),
                'choice_label' => 'title',
                // 'disabled' => $options['lock_book']
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rental::class,
        ]);
    }
}