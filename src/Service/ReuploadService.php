<?php
declare(strict_types=1);


namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ReuploadService {

  public function __construct(private readonly ParameterBagInterface $params, private readonly UploadService $uploadService) {
  }

  public function getDocs(string $path, array $files = null, array $docs = null): array {
    $docsNew = [];

    if (!empty($docs)) {
      foreach ($docs as $doc) {
        $file = new File($this->params->get('kernel.project_dir') . $doc);
        $docsNew[] = [
          'putanja' => $doc,
          'naziv' => $file->getBasename()
        ];
      }
    }

    if (!empty($files)) {
      foreach ($files as $file) {
        if ($file) {
          $fileName = $this->uploadService->upload($file, $path);
          $docsNew[] = [
            'putanja' => $fileName->getUrl(),
            'naziv' => $fileName->getFileName()
          ];
        }
      }
    }
    return $docsNew;
  }

}
