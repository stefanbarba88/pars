<?php

namespace App\Classes;
class Downloader {

  public static function getFileContents(string $url, string $path, string $username = null, string $password = null, string $authorization = null): bool {
    if (file_exists($path)) {
      return false;
    }

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

    return true;
  }

}
