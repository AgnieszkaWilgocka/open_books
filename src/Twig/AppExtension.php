<?php

namespace App\Twig;

use App\Repository\BookRepository;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private BookRepository $bookRepository) {}

    public function getGlobals(): array
    {
        $newestBook = $this->bookRepository->queryNewest();

        return [
            'newestBook' => $newestBook
        ];
    }

    
}