<?php

use App\Service\FileUploaderHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class FileUploaderHelperTest extends TestCase
{
    public function testUploadFile(): void
    {
        $image = 'book-adventure.jpg';
        $filesystem = new Filesystem;
        $targetPath = sys_get_temp_dir() . '/' . $image;
        $filesystem->copy(__DIR__ . '/test-images/' . $image, $targetPath, true);
        $sluggerInterfaceMock = $this->createMock(SluggerInterface::class);

        $sluggerInterfaceMock
            ->expects($this->once())
            ->method('slug')
            ->willReturn(new UnicodeString('book-adventure'));
    
        $destination = __DIR__ . '/test-uploads';
        $uploadHelper = new FileUploaderHelper($sluggerInterfaceMock, $destination);

        $result = $uploadHelper->uploadFile(new File($targetPath));

        $this->assertMatchesRegularExpression('/^book-adventure-/', $result);
        $this->assertMatchesRegularExpression('/\.jpg$/', $result);
        $this->assertFileExists($destination . '/book_image/' . $result);
        $this->assertFileDoesNotExist($targetPath);

        $filesystem->remove($destination);
    }
}