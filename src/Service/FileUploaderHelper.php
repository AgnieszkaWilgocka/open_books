<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploaderHelper
{
    const BOOK_IMAGE = 'book_image';

    public function __construct(private SluggerInterface $slugger, private string $fileDirection) {}

    public function uploadFile(File $file): string
    {
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        $safeFilename = $this->slugger->slug(pathinfo($originalFilename, PATHINFO_FILENAME));
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $destination = $this->fileDirection . '/' . self::BOOK_IMAGE;

        $file->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }
}
