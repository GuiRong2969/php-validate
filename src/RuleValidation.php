<?php declare(strict_types=1);
/**
 * Created by VsCode.
 * User: Guirong
 * Date: 2023-04-03
 * Time: 09:30
 */

namespace Guirong\Validate;

/**
 * Class RuleValidation
 * - alias of the Validation
 * - one rule to many fields. like Yii 1/2 framework
 *
 * ```php
 * [
 *  ['field1, field2, ... ', 'validator', ...],
 *  ['field1, field3, ... ', 'validator', ...]
 * ]
 * ```
 *
 * @package Guirong\Validate
 */
class RuleValidation extends Validation
{
}
