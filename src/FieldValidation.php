<?php declare(strict_types=1);
/**
  * Created by VsCode.
 * User: Guirong
 * Date: 2023/04/03
 * Time: 09:30
 */

namespace Guirong\Validate;

use Guirong\Validate\Traits\MultipleRulesTrait;

/**
 * Class FieldValidation
 * - one field to multiple rules. like Laravel framework
 *
 * ```php
 * $vd = FieldValidation::make($data, $rules, ...);
 * $vd->validate();
 * ```
 *
 * @package Guirong\Validate
 */
class FieldValidation extends AbstractValidation
{
    use MultipleRulesTrait;

    /* examples:
    public function rules()
    {
        return [
            ['field', 'required|string:5,10|...', ...],
            ['field0', ['required', 'string:5,10'], ...],
            ['field1', 'rule1|rule2|...', ...],
            ['field2', 'rule1|rule3|...', ...],
            ['field3', function($val) {}, ...],
        ];
    }
    */
}
