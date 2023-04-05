<?php declare(strict_types=1);
/**
 * Created by VsCode.
 * User: Guirong
 * Date: 2023/04/03
 * Time: 09:30
 */

namespace Guirong\ValidateTest\Example;

use Guirong\Validate\FieldValidation;

/**
 * Class FieldExample
 *
 * @package Guirong\ValidateTest\Example
 */
class FieldExample extends FieldValidation
{
    public function rules(): array
    {
        return [
            ['user', 'required|string:1,12'],
            ['pwd', 'required|string:6,16'],
            ['code', 'lengthEq:4'],
        ];
    }

    public function scenarios(): array
    {
        return [
            'create' => ['user', 'pwd', 'code'],
            'update' => ['user', 'pwd'],
        ];
    }
}
