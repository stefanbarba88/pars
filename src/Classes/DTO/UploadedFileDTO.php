<?php

namespace App\Classes\DTO;

class UploadedFileDTO {

  private string $uploadPath;
  private string $urlPath;

  private string $fileName;

  /**
   * @param string $uploadPath
   * @param string $urlPath
   * @param string $fileName
   */
  public function __construct(string $uploadPath, string $urlPath, string $fileName) {
    $this->uploadPath = $uploadPath;
    $this->urlPath = $urlPath;
    $this->fileName = $fileName;

  }

  /**
   * @return string
   */
  public function getUploadPath(): string {
    return $this->uploadPath;
  }

  /**
   * @return string
   */
  public function getUrlPath(): string {
    return $this->urlPath;
  }

  /**
   * @return string
   */
  public function getFileName(): string {
    return $this->fileName;
  }

  public function getUrl(): string {
    return $this->urlPath . $this->fileName;
  }

  public function getPath(): string {
    return $this->uploadPath . $this->fileName;
  }

  public function getAssetPath(): string {
    return str_replace("/public","",$this->urlPath . $this->fileName);
  }


}
