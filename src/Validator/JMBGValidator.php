<?php

namespace App\Validator;


use App\Classes\JMBGcheck\JMBGcheck;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class JMBGValidator extends ConstraintValidator {
  public function validate($value, Constraint $constraint): void {
    if (!$constraint instanceof JMBG) {
      throw new UnexpectedTypeException($constraint, JMBG::class);
    }

// custom constraints should ignore null and empty values to allow
// other constraints (NotBlank, NotNull, etc.) to take care of that
    if (null === $value || '' === $value) {
      return;
    }

//    if (!is_string($value)) {
//// throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
//      throw new UnexpectedValueException($value, 'string');
//
//// separate multiple types using pipes
//// throw new UnexpectedValueException($value, 'string|int');
//    }

//// access your configuration options like this:
//    if ('strict' === $constraint->mode) {
//// ...
//    }


    $jmbg = new JMBGcheck($value);
    $test = $jmbg->validateJMBG();

    if (!$test) {
// the argument must be a string or an object implementing __toString()
      $this->context->buildViolation($constraint->message)
        ->addViolation();
    }
  }
}
