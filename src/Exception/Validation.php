<?php

namespace W1\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class Validation extends \ErrorException {

	public function __construct(ConstraintViolationListInterface $violations) {

		parent::__construct((string) $violations);
	}
}