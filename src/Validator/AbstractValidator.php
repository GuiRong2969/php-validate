<?php declare(strict_types=1);

namespace Guirong\Validate\Validator;

/**
 * Class AbstractValidator
 * @package Guirong\Validate\Validator
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * 魔术方法,在试图函数式使用对象是调用
     *
     * @param mixed $value
     * @param array $data
     *
     * @return bool
     */
    public function __invoke($value, array $data): bool
    {
        return $this->validate($value, $data);
    }
}
