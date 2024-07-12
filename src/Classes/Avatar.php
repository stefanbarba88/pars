<?php

namespace App\Classes;

use App\Classes\DTO\UploadedFileDTO;


class Avatar {

  public const URL_PATH = 'https://ui-avatars.com/api/?name=';

  public const BACKGROUND_MALE = '00233f';
  public const BACKGROUND_FEMALE = 'c4dfea';

  public const FONT_MALE = 'FFFFFF';
  public const FONT_FEMALE = '00233F';



  public const SIZE = '512';
  public const ROUNDED = 'false';

  public static function getAvatar($path, $user): UploadedFileDTO {

    $fullName = Slugify::slugify($user->getIme() . $user->getPrezime());
    $file = new UploadedFileDTO($path, $user->getAvatarUploadPath(), $fullName . '.png');

    if (file_exists($file->getPath())) {
      return $file;
    }

    if (!file_exists($path)) {
      mkdir($path, 0777, true);
    }

    if ($user->getPol() == 1) {
      $url = self::URL_PATH . $user->getPrezime() . '+' . $user->getIme() . '&background=' . self::BACKGROUND_MALE . '&color=' . self::FONT_MALE . '&size=' . self::SIZE . '&rounded=' . self::ROUNDED;
    } else {
      $url = self::URL_PATH . $user->getPrezime() . '+' . $user->getIme() . '&background=' . self::BACKGROUND_FEMALE . '&color=' . self::FONT_FEMALE . '&size=' . self::SIZE . '&rounded=' . self::ROUNDED;
    }


    $data = file_get_contents($url);

    if ($data !== false) {
      file_put_contents($file->getPath(), $data);
    }
//    $fp = fopen($file->getPath(), 'w');
//    $ch = curl_init($url);
//
////    if (!empty($authorization)) {
////      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', $authorization]);
////    }
//
//    curl_setopt($ch, CURLOPT_FILE, $fp);
//    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
//    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//
////    if (!empty($username)) {
////      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
////      curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
////    }
//
//    $data = curl_exec($ch);
//
////    if (curl_errno($ch)) {
////      unlink($path);
////      throw new ApiConnectionException(curl_error($ch) . ' :: ' . $url);
////    }
//
//    curl_close($ch);
//    fclose($fp);
////
////    if (!file_exists($path)) {
////      throw new OidlMissingFileException($path);
////    }
////
////    if (!filesize($path)) {
////      unlink($path);
////      throw new OidlEmptyFileException($path);
////    }

    return $file;
  }

}
