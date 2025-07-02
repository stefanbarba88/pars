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

//  public static function getAvatar($path, $user): UploadedFileDTO {
//
//    $fullName = Slugify::slugify($user->getIme() . $user->getPrezime());
//    $file = new UploadedFileDTO($path, $user->getAvatarUploadPath(), $fullName . '.png');
//
//    if (file_exists($file->getPath())) {
//      return $file;
//    }
//
//    if (!file_exists($path)) {
//      mkdir($path, 0777, true);
//    }
//
//    if ($user->getPol() == 1) {
//      $url = self::URL_PATH . $user->getPrezime() . '+' . $user->getIme() . '&background=' . self::BACKGROUND_MALE . '&color=' . self::FONT_MALE . '&size=' . self::SIZE . '&rounded=' . self::ROUNDED;
//    } else {
//      $url = self::URL_PATH . $user->getPrezime() . '+' . $user->getIme() . '&background=' . self::BACKGROUND_FEMALE . '&color=' . self::FONT_FEMALE . '&size=' . self::SIZE . '&rounded=' . self::ROUNDED;
//    }
//
//
//    $data = file_get_contents($url);
//
//    if ($data !== false) {
//      file_put_contents($file->getPath(), $data);
//    }
////    $fp = fopen($file->getPath(), 'w');
//
////    $ch = curl_init($url);
////
//////    if (!empty($authorization)) {
//////      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', $authorization]);
//////    }
////
////    curl_setopt($ch, CURLOPT_FILE, $fp);
////    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
////    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
////
//////    if (!empty($username)) {
//////      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
//////      curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
//////    }
////
////    $data = curl_exec($ch);
////
//////    if (curl_errno($ch)) {
//////      unlink($path);
//////      throw new ApiConnectionException(curl_error($ch) . ' :: ' . $url);
//////    }
////
////    curl_close($ch);
////    fclose($fp);
//////
//////    if (!file_exists($path)) {
//////      throw new OidlMissingFileException($path);
//////    }
//////
//////    if (!filesize($path)) {
//////      unlink($path);
//////      throw new OidlEmptyFileException($path);
//////    }
//
//    return $file;
//  }

  public static function generateLocalAvatar(string $path, $user): UploadedFileDTO
    {
        $initials = mb_substr($user->getPrezime(), 0, 1) . mb_substr($user->getIme(), 0, 1);
        $initials = strtoupper($initials);
        $fileName = Slugify::slugify($user->getPrezime() . $user->getIme()) . '.png';
        $fullPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        $bgColor = ($user->getPol() == 1) ? self::BACKGROUND_MALE : self::BACKGROUND_FEMALE;
        $fontColor = ($user->getPol() == 1) ? self::FONT_MALE : self::FONT_FEMALE;

        // Kreiranje foldera ako ne postoji
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($fullPath)) {
            $size = (int) self::SIZE;
            $image = imagecreatetruecolor($size, $size);

            // Konvertuj HEX u RGB
            list($r, $g, $b) = sscanf($bgColor, "%02x%02x%02x");
            $bg = imagecolorallocate($image, $r, $g, $b);

            list($fr, $fg, $fb) = sscanf($fontColor, "%02x%02x%02x");
            $fg = imagecolorallocate($image, $fr, $fg, $fb);

            // Popuni pozadinu
            imagefill($image, 0, 0, $bg);

            // Font i veličina
            $fontSize = $size / 3.5;
            $fontPath = $_SERVER['DOCUMENT_ROOT'] . '/font/arial.ttf';

            // Dobij bounding box
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $initials);

            $textWidth = abs($bbox[2] - $bbox[0]);
            $textHeight = abs($bbox[7] - $bbox[1]);

            // Centriranje
            $x = ($size - $textWidth) / 2 - $bbox[0];
            $y = ($size + $textHeight) / 2;

            // Iscrtavanje teksta
            imagettftext($image, $fontSize, 0, $x, $y, $fg, $fontPath, $initials);

            imagepng($image, $fullPath);
            imagedestroy($image);
        }


        return new UploadedFileDTO($path, $user->getAvatarUploadPath(), $fileName);
    }

}
