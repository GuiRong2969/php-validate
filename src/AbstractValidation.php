<?php

/**
 * Created by VsCode.
 * User: Guirong
 * Date: 2023/4/3 09:30
 */

namespace Guirong\Validate;

/**
 * Class AbstractValidation
 * - one field to many rules. like Laravel framework
 * ```php
 * [
 *  ['field1', 'rule1, rule2, ...', ...],
 *  ['field2', 'rule1, rule3, ...', ...],
 * ]
 * ```
 * @package Guirong\Validate
 */
abstract class AbstractValidation implements ValidationInterface
{
    use ValidationTrait {
        //set as traitSet;
        get as traitGet;
    }
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array $data
     * @param array $rules
     * @param array $translates
     * @param string $scene
     * @param bool $startValidate 立即开始验证
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct(
        array $data = [],
        array $rules = [],
        array $translates = [],
        $scene = '',
        $startValidate = false
    ) {
        $this->data = $data;
        $this->setRules($rules)->setScene($scene)->setTranslates($translates);
        if ($startValidate) {
            $this->validate();
        }
    }

    /**
     * @param array $data
     * @param array $rules
     * @param array $translates
     * @param string $scene
     * @param bool $startValidate 立即开始验证
     * @return static
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public static function make(
        array $data,
        array $rules = [],
        array $translates = [],
        $scene = '',
        $startValidate = false
    ) {
        return new static($data, $rules, $translates, $scene, $startValidate);
    }

    /**
     * 创建并且立即开始验证
     * @param array $data
     * @param array $rules
     * @param array $translates
     * @param string $scene
     * @return static
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public static function makeAndValidate(array $data, array $rules = [], array $translates = [], $scene = '')
    {
        return new static($data, $rules, $translates, $scene, true);
    }
}