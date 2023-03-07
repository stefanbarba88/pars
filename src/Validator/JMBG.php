<?php

namespace App\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class JMBG extends Constraint {
  public string $message = 'JMBG nije u redu, molimo proverite!';
  public string $mode;

  #[HasNamedArguments]
  public function __construct(string $mode, array $groups = null, mixed $payload = null) {
    parent::__construct([], $groups, $payload);
    $this->mode = $mode;
  }
}