<?php

namespace App\Classes;

use App\Classes\DTO\UploadedFileDTO;


class Avatar {

  public const URL_PATH = 'https://ui-avatars.com/api/?name=';
  public const BACKGROUND = 'random';

  public const SIZE = '256';
  public const ROUNDED = 'true';


  public static function getAvatar(string $name, string $lastname, $path, $user): string {

    $fullName = Slugify::slugify($name . $lastname);

    if (file_exists($path)) {
      return $user->getAvatarUploadPath() . $fullName . '.png';
    }
    mkdir($path, 0777, true);

    $path = $path . $fullName . '.png';

    $url = self::URL_PATH . $name . '+' . $lastname . '&background=' . self::BACKGROUND . '&size=' . self::SIZE . '&rounded=' . self::ROUNDED;

    $fp = fopen($path, 'w');
    $ch = curl_init($url);

//    if (!empty($authorization)) {
//      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', $authorization]);
//    }

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

//    if (!empty($username)) {
//      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
//      curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
//    }

    $data = curl_exec($ch);

//    if (curl_errno($ch)) {
//      unlink($path);
//      throw new ApiConnectionException(curl_error($ch) . ' :: ' . $url);
//    }

    curl_close($ch);
    fclose($fp);
//
//    if (!file_exists($path)) {
//      throw new OidlMissingFileException($path);
//    }
//
//    if (!filesize($path)) {
//      unlink($path);
//      throw new OidlEmptyFileException($path);
//    }
    return $user->getAvatarUploadPath() . $fullName . '.png';
  }

}
