<?php

/**
 * Created by VsCode.
 * User: Guirong
 * Date: 2023/4/3 09:30
 */

namespace Guirong\Validate;

/**
 * Interface ValidationInterface
 * @package Guirong\Validate
 */
interface ValidationInterface
{
    /**
     * @return array
     */
    public function rules();

    /**
     * define attribute field translate list
     * @return array
     */
    public function translates();

    /**
     * 自定义验证器的默认错误消息格式
     * custom validator's message, to override default message.
     * @return array
     */
    public function messages();

    /**
     * 进行数据验证
     * @author Guirong
     * @date   2015-08-11
     * @param  array $onlyChecked 可以设置此次需要验证的字段
     * @param  bool|null $stopOnError 是否出现错误即停止验证
     * @return static
     * @throws \RuntimeException
     */
    public function validate(array $onlyChecked = [], $stopOnError = null);

    /**
     * @return bool
     */
    public function fail();

    /**
     * alias of the fail()
     * @return bool
     */
    public function isFail();

    /**
     * @return bool
     */
    public function passed();

    /**
     * @return array
     */
    public function getErrors();

    /**
     * 得到第一个错误信息
     * @author Guirong
     * @param bool $onlyMsg Only return message string.
     * @return array|string
     */
    public function firstError($onlyMsg = true);

    /**
     * 得到最后一个错误信息
     * @author Guirong
     * @param bool $onlyMsg
     * @return array|string
     */
    public function lastError($onlyMsg = true);

    /**
     * @return array
     */
    public function getMessages();

    /**
     * @return array
     */
    public function getSafeData();
}