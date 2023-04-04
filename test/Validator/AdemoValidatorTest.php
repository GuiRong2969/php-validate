<?php declare(strict_types=1);

namespace Guirong\ValidateTest\Validator;

use Guirong\Validate\Validator\AbstractValidator;

/**
 * Class ClassValidator
 *
 * @package Guirong\ValidateTest\Validator
 */
class AdemoValidatorTest extends AbstractValidator
{
    /**
     * @param mixed $value
     * @param array $data
     *
     * @return bool
     */
    public function validate(mixed $value, array $data): bool
    {
        return $value === 1;
    }
}
