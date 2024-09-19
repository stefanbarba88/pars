<?php

namespace App\Classes;

use nusoap_client;

class Soap {
  public const CID = '00000000-0000-0000-0000-000000000000';
  public const CCODE = '0';
  public const NAME = '';
  public const CITY = '';
  public const START = '1';
  public const END = '100';
  public const MB = '0';
  public const USERNAME = 'erkom';
  public const PASSWORD = 'Muchiqew1';
  public const LICENCE = 'a2c7c883-5b90-476c-9f54-511766741cee';
  public const WSDL = 'https://webservices.nbs.rs/CommunicationOfficeService1_0/CoreXmlService.asmx?WSDL';
  public const NAMESPACE = 'http://communicationoffice.nbs.rs';

  public static function getSoap($pib): array  {

    $rezultat = [];

    $client = new nusoap_client(self::WSDL, true);
    $client->soap_defencoding = 'UTF-8';
    $client->decode_utf8 = FALSE;

    $header =
      '<AuthenticationHeader xmlns="' . self::NAMESPACE . '">
      <UserName>' . self::USERNAME . '</UserName>
      <Password>' . self::PASSWORD . '</Password>
      <LicenceID>' . self::LICENCE . '</LicenceID>
      </AuthenticationHeader>';
    $client->setHeaders($header);
//dd($client->getError());
    $params = [
      'companyID' => self::CID,
      'companyCode' => self::CCODE,
      'name' => self::NAME,
      'city' => self::CITY,
      'nationalIdentificationNumber' => self::MB,
      'taxIdentificationNumber' => $pib,
      'startItemNumber' => self::START,
      'endItemNumber' => self::END
    ];

    $call = $client->call ('GetCompany', $params);

    $result = $call['GetCompanyResult'];

    $json = json_encode(simplexml_load_string($result));
    $res = json_decode($json, true);

    if (isset($res['Company']['Name'])) {
      $naziv = $res['Company']['Name'];
    } else {
      $naziv = '';
    }
    if (isset($res['Company']['Address'])) {
      $adresa = $res['Company']['Address'];
    } else {
      $adresa = '';
    }
    if (isset($res['Company']['PostalCode'])) {
      $ptt = $res['Company']['PostalCode'];
    } else {
      $ptt = '';
    }
    if (isset($res['Company']['City'])) {
      $grad = $res['Company']['City'];
    } else {
      $grad = '';
    }

    if (isset($res['Company']['NationalIdentificationNumber'])) {
      $mb = $res['Company']['NationalIdentificationNumber'];
    } else {
      $mb = '';
    }
    if (isset($res['Company']['TaxIdentificationNumber'])) {
      $pib = $res['Company']['TaxIdentificationNumber'];
    }

    if (isset($res['Company']['Phone'])) {
      $tel1 = $res['Company']['Phone'];
    } else {
      $tel1 = '';
    }
    if (isset($res['Company']['Phone2'])) {
      $tel2 = $res['Company']['Phone2'];
    } else {
      $tel2 = '';
    }
    if (isset($res['Company']['Fax'])) {
      $fax = $res['Company']['Fax'];
    } else {
      $fax = '';
    }
    if (isset($res['Company']['Email'])) {
      $email = $res['Company']['Email'];
    } else {
      $email = '';
    }

    $rezultat = [
      'naziv' => $naziv,
      'adresa' => $adresa,
      'ptt' => $ptt,
      'grad' => $grad,
      'mb' => $mb,
      'pib' => $pib,
      'tel1' => $tel1,
      'tel2' => $tel2,
      'fax' => $fax,
      'email' => $email,
    ];

    return $rezultat;

  }

}
