<?php declare(strict_types=1);
/**
 * Created by VsCode.
 * User: Guirong
 * Date: 2023/04/03
 * Time: 09:30
 */

namespace Guirong\Validate;

/**
 * Class Validation
 *
 * @package Guirong\Validate
 *
 * Usage:
 * $vd = Validation::make($_POST, [
 *      ['tagId,userId,name,email,freeTime', 'required'],
 *      ['email', 'email'],
 *      ['userId', 'number'],
 *      ['name', 'regexp' ,'/^[a-z]\w{2,12}$/'],
 * ])->validate();
 *
 * $vd->isFail();// bool
 * $vd->firstError(); // get first error message.
 * $vd->isOk();// bool
 */
class Validation extends AbstractValidation
{
    /* examples:
    public function rules()
    {
        return [
            ['fields', 'validator', arg0, arg1, something ...]
            ['tagId,userId,name,email,freeTime', 'required'],
            ['userId', 'number'],
        ];
    }
    */

    /**
     * @param string     $key
     * @param mixed|null $value
     *
     * @return mixed
     */
    public function get(string $key, $value = null): mixed
    {
        return $this->traitGet($key, $value);
    }
}
