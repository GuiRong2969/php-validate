<?php

/**
 * Created by VsCode.
 * User: Guirong
 * Date: 2023/4/3 09:30
 */

namespace Guirong\Validate;

/**
 * Class Validation
 * @package Guirong\Validate
 * usage:
 * $vd = Validation::make($_POST, [
 *      ['tagId,userId,name,email,freeTime', 'required'],
 *      ['email', 'email'],
 *      ['userId', 'number'],
 *      ['name', 'regexp' ,'/^[a-z]\w{2,12}$/'],
 * ])->validate();
 * $vd->fail();// bool
 * $vd->firstError(); // get first error message.
 * $vd->passed();// bool
 */
class Validation extends AbstractValidation
{
    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @param string $key
     * @param null $value
     * @return mixed
     */
    public function get($key, $value = null)
    {
        return $this->traitGet($key, $value);
    }
}