<?php

namespace W1\Form;

use W1\Exception\Validation as ValidationException;
use Symfony\Component\Validator\Validation;

abstract class AbstractForm {

	public static function newInstance() {
		return new static;
	}

	public function toArray() {
		return get_object_vars($this);
	}

	public function validate()
	{
		$validator  = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
		$violations = $validator->validate($this);
		if ($violations->count()) throw new ValidationException($violations);

		return $this;
	}
}