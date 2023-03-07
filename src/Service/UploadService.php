<?php

namespace App\Service;

use App\Classes\DTO\UploadedFileDTO;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadService {

  public function __construct(private readonly ParameterBagInterface $params, private readonly SluggerInterface $slugger) {
  }

  public function upload(UploadedFile $file, string $path): UploadedFileDTO {
    $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $cleanFileName = $this->slugger->slug($originalFileName);
    $fileName = $cleanFileName . '.' . $file->guessExtension();

    try {

      $file->move($this->getTargetDirectory($path), $fileName);
    } catch (FileException $e) {
      // ... handle exception if something happens during file upload
    }

    return new UploadedFileDTO($this->getTargetDirectory($path), $path, $fileName);
  }

  public function getTargetDirectory($path): string {

    return $this->params->get('kernel.project_dir') . $path;
  }


}
