<?php

namespace App\Classes\JMBGcheck;

/**
 * Serbian JMBG number (Unique Master Citizen Number, Jedinstveni matični broj građana) number
 * https://www.paragraf.rs/propisi/zakon-o-jedinstvenom-maticnom-broju-gradjana.html
 */
class Person {
  private string $JMBGNumber;
  private array $JMBGNumberArrayOfCharacters;
  private string $DayOfBirth;
  private string $MonthOfBirth;

  private string $YearOfBirthLastThreeDigits;
  private string $RegistrationAreaNumber;
  private string $RegistrationArea;
  private string $BirthSerialNumber;
  private string $BirthSerialNumberDescriptive;
  private string $Sex;
  private string $ControlNumber;
  private string $YearOfBirthAssumed;
  private string $DateOfBirthAssumed;
  private bool $DateOfBirthAssumedValidDate;
  private int $ControlNumberCalculated;
  public bool $ControlNumberMatch;

  public function get(string $key, string $returntype = "array") {

    $tmpString = $this->$key;

    if ($returntype == "array") {
      return $tmpString;
    } elseif ($returntype == "json") {
      $jsonArray = json_encode($tmpString);
      return $jsonArray;
    }
  }

  public function set(string $key, $value) {
    $this->$key = $value;
    return true;
  }

  public function getMultiple(array $keys, string $returntype = "array") {
    $tmpArray = [];
    foreach ($keys as $key) {
      $tmpArray[] = $this->$key;
    }

    if ($returntype == "array") {
      return $tmpArray;
    } elseif ($returntype == "json") {
      $jsonArray = json_encode($tmpArray);
      return $jsonArray;
    }
  }

  public function setMultiple(array $keyValuePairs) {
    foreach ($keyValuePairs as $key => $value) {
      $this->$key = $this->$value;
    }

  }

  public function getAll(string $returntype = "array") {
    $keyValuePairs = get_object_vars($this);

    if ($returntype == "array") {
      return $keyValuePairs;
    } elseif ($returntype == "json") {
      $KeyValuePairsJSON = json_encode($keyValuePairs);
      return $KeyValuePairsJSON;
    }
  }

}