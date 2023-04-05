<?php declare(strict_types=1);
/**
 * 验证器列表
 *
 * Created by VsCode.
 * User: Guirong
 * Date: 2023/04/03
 * Time: 09:30
 * @note        验证数据; 成功则返回预期的类型， 失败返回 false
 * @description INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV  几个输入数据常量中的值在请求时即固定下来了，
 * 后期通过类似 $_GET['test']='help'; 将不会存在 输入数据常量中(INPUT_GET 没有test项)。
 */

namespace Guirong\Validate;

use Guirong\Validate\Exception\ArrayValueNotExists;
use Guirong\Validate\Traits\NameAliasTrait;
use JsonException;
use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_pop;
use function array_shift;
use function array_unique;
use function checkdate;
use function count;
use function date;
use function explode;
use function filter_var;
use function get_object_vars;
use function in_array;
use function is_array;
use function is_int;
use function is_numeric;
use function is_object;
use function is_scalar;
use function is_string;
use function json_decode;
use function json_last_error;
use function preg_match;
use function stripos;
use function strpos;
use function strtotime;
use function substr;
use function time;
use function trim;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_EMAIL;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;
use const FILTER_VALIDATE_IP;
use const FILTER_VALIDATE_REGEXP;
use const FILTER_VALIDATE_URL;
use const JSON_ERROR_NONE;

/**
 * Class Validators
 *
 * @package Guirong\Validate
 */
class Validators
{
    use NameAliasTrait;

    public const REGEX_DATE = '/^([\d]{4})-((?:0?[\d])|(?:1[0-2]))-((?:0?[\d])|(?:[1-2][\d])|(?:3[01]))( [\d]{2}:[\d]{2}:[\d]{2})?$/';

    public const REGEX_DATE_FMT = '/^([\d]{4})-((0?[\d])|(1[0-2]))-((0?[\d])|([1-2][\d])|(3[01]))( [\d]{2}:[\d]{2}:[\d]{2})?$/';

    public const REGEX_ABS_URL = '/^(https?:)?\/\/[$~:;#,%&_=\(\)\[\]\.\? \+\-@\/a-zA-Z0-9]+$/';

    /**
     * @var array
     */
    private static array $aliases = [
        // alias => real name.
        'int'         => 'integer',
        'num'         => 'number',
        'bool'        => 'boolean',
        'in'          => 'enum',
        'greaterThan' => 'gt',
        'lessThan'    => 'lt',
        'mustBe'      => 'eq',
        'equal'       => 'eq',
        'notBe'       => 'neq',
        'notEqual'    => 'neq',
        'ne'          => 'neq',
        'range'       => 'size',
        'between'     => 'size',
        'len'         => 'length',
        'lenEq'       => 'fixedSize',
        'lengthEq'    => 'fixedSize',
        'sizeEq'      => 'fixedSize',
        //
        'neField'     => 'neqField',
        'diff'        => 'neqField',
        'different'   => 'neqField',
        'equalField'  => 'eqField',
        //
        'map'         => 'isMap',
        'list'        => 'isList',
        'array'       => 'isArray',
        'absUrl'      => 'absoluteUrl',
        'ints'        => 'intList',
        'stringList'  => 'strList',
        'strings'     => 'strList',
    ];

    /*******************************************************************************
     * Validators
     ******************************************************************************/

    /**
     * 判断值是否为空
     * 值符合下方任一条件时即为「空」
     * - 该值为 null.
     * - 该值为空字符串。
     * - 该值为空数组
     * - 该值为空对象 -- 空的 `可数` 对象
     * - 该值为没有路径的上传文件(这里不做判断)
     *
     * @param mixed $val
     *
     * @return bool
     */
    public static function isEmpty(mixed $val): bool
    {
        if (is_string($val)) {
            $val = trim($val);
        } elseif (is_array($val)) {
            // each value must be verified
            foreach ($val as $item) {
                if (($item instanceof ArrayValueNotExists)) {
                    $val = [];
                    break;
                }
            }
        } elseif (is_object($val)) {
            if ($val instanceof ArrayValueNotExists) {
                $val = '';
            } else {
                $val = get_object_vars($val);
            }
        }

        return $val === '' || $val === null || $val === [];
    }

    /*******************************************************************************
     * bool/int/float/string validators
     ******************************************************************************/

    /**
     * 布尔值验证，转换成字符串后是下列的一个，就认为他是个bool值
     *   - "1"、"true"、"on" 和 "yes" (equal TRUE)
     *   - "0"、"false"、"off"、"no" 和 ""(equal FALSE)
     * 注意： NULL 不是标量类型
     *
     * @param mixed $val 要验证的数据
     *
     * @return bool
     */
    public static function boolean(mixed $val): bool
    {
        if (!is_scalar($val)) {
            return false;
        }

        // $ret = filter_var($val, \FILTER_VALIDATE_BOOLEAN, $settings);
        if (($val = (string)$val) === '') {
            return true;
        }

        return false !== stripos(Helper::IS_BOOL, '|' . $val . '|');
    }

    /**
     * @param mixed $val
     *
     * @return bool
     * @see Validators::boolean()
     */
    public static function bool(mixed $val): bool
    {
        return self::boolean($val);
    }

    /**
     * check value is float
     *
     * @param mixed $val 要验证的变量
     * @param float|string|int|null $min 最小值
     * @param string|float|int|null $max 最大值
     *                                      $options = [
     *                                      'default' => 'default value',
     *                                      'decimal' => 2
     *                                      ]
     * @param string|int $flags FILTER_FLAG_ALLOW_THOUSAND
     *
     * @return bool
     */
    public static function float(mixed $val, float|string|int $min = null, string|float|int $max = null, string|int $flags = 0): bool
    {
        if (!is_numeric($val)) {
            return false;
        }

        $options = (int)$flags !== 0 ? ['flags' => (int)$flags] : [];

        // NOTICE: FILTER_VALIDATE_FLOAT not support the 'min_range', 'max_range options.
        if (filter_var($val, FILTER_VALIDATE_FLOAT, $options) === false) {
            return false;
        }

        $minIsNum = is_numeric($min);
        $maxIsNum = is_numeric($max);

        if ($minIsNum && $maxIsNum) {
            if ($max > $min) {
                $minV = $min;
                $maxV = $max;
            } else {
                $minV = $max;
                $maxV = $min;
            }

            return $val >= $minV && $val <= $maxV;
        }

        if ($minIsNum) {
            return $val >= $min;
        }

        if ($maxIsNum) {
            return $val <= $max;
        }

        return true;
    }

    /**
     * int 验证 (所有的最小、最大都是包含边界值的)
     *
     * @param mixed $val 要验证的变量
     * @param int|string|null $min 最小值
     * @param int|string|null $max 最大值
     * @param int|string $flags 标志
     *                                FILTER_FLAG_ALLOW_OCTAL - 允许八进制数值
     *                                FILTER_FLAG_ALLOW_HEX - 允许十六进制数值
     *
     * @return bool false
     * @example
     * $options = [
     *    'min_range' => 0,
     *    'max_range' => 256 // 添加范围限定
     *    // 'default' => 3, // value to return if the filter fails
     * ]
     */
    public static function integer(mixed $val, int|string $min = null, int|string $max = null, int|string $flags = 0): bool
    {
        if (!is_numeric($val)) {
            return false;
        }

        $options  = $settings = [];
        $minIsNum = is_numeric($min);
        $maxIsNum = is_numeric($max);

        if ($minIsNum && $maxIsNum) {
            if ($max > $min) {
                $options['min_range'] = (int)$min;
                $options['max_range'] = (int)$max;
            } else {
                $options['min_range'] = (int)$max;
                $options['max_range'] = (int)$min;
            }
        } elseif ($minIsNum) {
            $options['min_range'] = (int)$min;
        } elseif ($maxIsNum) {
            $options['max_range'] = (int)$max;
        }

        if ($options) {
            $settings['options'] = $options;
        }

        if ($flags !== 0) {
            $settings['flags'] = $flags;
        }

        return filter_var($val, FILTER_VALIDATE_INT, $settings) !== false;
    }

    /**
     * @param int|mixed $val
     * @param int|numeric|null $min
     * @param int|numeric|null $max
     * @param string|int $flags
     *
     * @return bool
     * @see integer()
     */
    public static function int(mixed $val, float|int|string $min = null, float|int|string $max = null, string|int $flags = 0): bool
    {
        return self::integer($val, $min, $max, $flags);
    }

    /**
     * check var is a integer and greater than 0
     *
     * @param mixed $val
     * @param integer|numeric|null $min 最小值
     * @param int|numeric|null $max 最大值
     * @param string|int $flags
     *
     * @return bool
     */
    public static function number(mixed $val, float|int|string $min = null, float|int|string $max = null, string|int $flags = 0): bool
    {
        if (!is_numeric($val)) {
            return false;
        }

        if ($val <= 0) {
            return false;
        }

        return self::integer($val, $min, $max, $flags);
    }

    /**
     * @param int|mixed $val
     * @param int|numeric|null $min
     * @param int|numeric|null $max
     * @param string|int $flags
     *
     * @return bool
     * @see number()
     */
    public static function num(mixed $val, float|int|string $min = null, float|int|string $max = null, string|int $flags = 0): bool
    {
        return self::number($val, $min, $max, $flags);
    }

    /**
     * check val is a string
     *
     * @param mixed $val
     * @param int|numeric $minLen
     * @param int|numeric|null $maxLen
     *
     * @return bool
     */
    public static function string(mixed $val, float|int|string $minLen = 0, float|int|string $maxLen = null): bool
    {
        if (!is_string($val)) {
            return false;
        }

        // only type check.
        if ($minLen < 1 && $maxLen === null) {
            return true;
        }

        return self::integer(Helper::strlen($val), $minLen, $maxLen);
    }

    /**
     * 验证的字段必须为 yes, on, 1, true 这在确认「服务条款」是否同意时相当有用。
     *
     * @from laravel
     *
     * @param mixed $val
     *
     * @return bool
     */
    public static function accepted(mixed $val): bool
    {
        if (!is_scalar($val)) {
            return false;
        }

        return false !== stripos(Helper::IS_TRUE, '|' . $val . '|');
    }

    /**
     * 验证字段值是否仅包含字母字符
     *
     * @param string|mixed $val
     *
     * @return bool
     */
    public static function alpha(mixed $val): bool
    {
        return is_string($val) && preg_match('/^(?:[a-zA-Z]+)$/', $val) === 1;
    }

    /**
     * 验证字段值是否仅包含字母、数字
     *
     * @param string|mixed $val
     *
     * @return bool
     */
    public static function alphaNum(mixed $val): bool
    {
        if (!is_string($val) && !is_numeric($val)) {
            return false;
        }

        return 1 === preg_match('/^(?:[a-zA-Z0-9]+)$/', (string)$val);
    }

    /**
     * 验证字段值是否仅包含字母、数字、破折号（ - ）以及下划线（ _ ）
     *
     * @param string|numeric $val
     *
     * @return bool
     */
    public static function alphaDash(float|int|string $val): bool
    {
        if (!is_string($val) && !is_numeric($val)) {
            return false;
        }

        return 1 === preg_match('/^(?:[\w-]+)$/', $val);
    }

    /*******************************************************************************
     * value size compare validators
     ******************************************************************************/

    /**
     * @param $val
     * @param $expected
     *
     * @return bool
     */
    public static function same($val, $expected): bool
    {
        return self::eq($val, $expected);
    }

    /**
     * Must be equal to the given value
     *
     * @param mixed $val
     * @param mixed $expected
     * @param mixed|bool $strict
     *
     * @return bool
     */
    public static function eq(mixed $val, mixed $expected, mixed $strict = true): bool
    {
        /** @noinspection TypeUnsafeComparisonInspection */
        return $strict ? $val === $expected : $val == $expected;
    }

    /**
     * Cannot be equal to a given value
     *
     * @param mixed $val
     * @param mixed $expected
     * @param mixed $strict
     *
     * @return bool
     */
    public static function neq(mixed $val, mixed $expected, mixed $strict = true): bool
    {
        /** @noinspection TypeUnsafeComparisonInspection */
        return $strict ? $val !== $expected : $val != $expected;
    }

    /**
     * Greater than expected value
     *
     * @param mixed $val
     * @param mixed $expected
     *
     * @return bool
     * @see Helper::compareSize()
     */
    public static function gt(mixed $val, mixed $expected): bool
    {
        return Helper::compareSize($val, '>', $expected);
    }

    /**
     * Greater than or equal expected value
     *
     * @param mixed $val
     * @param mixed $expected
     *
     * @return bool
     */
    public static function gte(mixed $val, mixed $expected): bool
    {
        return Helper::compareSize($val, '>=', $expected);
    }

    /**
     * Less than expected value
     *
     * @param mixed $val
     * @param mixed $expected
     *
     * @return bool
     * @see Helper::compareSize()
     */
    public static function lt(mixed $val, mixed $expected): bool
    {
        return Helper::compareSize($val, '<', $expected);
    }

    /**
     * Less than or equal expected value
     *
     * @param mixed $val
     * @param mixed $expected
     *
     * @return bool
     */
    public static function lte(mixed $val, mixed $expected): bool
    {
        return Helper::compareSize($val, '<=', $expected);
    }

    /**
     * 最小值检查
     *
     * @param array|int|string $val
     * @param int|string $minRange
     *
     * @return bool
     */
    public static function min(array|int|string $val, int|string $minRange): bool
    {
        return self::size($val, $minRange);
    }

    /**
     * 最大值检查
     *
     * @param array|int|string $val
     * @param int|string $maxRange
     *
     * @return bool
     */
    public static function max(array|int|string $val, int|string $maxRange): bool
    {
        return self::size($val, null, $maxRange);
    }

    /*******************************************************************************
     * size/range/length validators
     ******************************************************************************/

    /**
     * 范围检查
     * $min $max 即使传错位置也会自动调整
     *
     * @param float|array|int|string $val 待检测的值。 数字检查数字范围； 字符串、数组则检查长度
     * @param float|int|string|null $min 最小值
     * @param float|int|string|null $max 最大值
     *
     * @return bool
     */
    public static function size(float|array|int|string $val, float|int|string $min = null, float|int|string $max = null): bool
    {
        if (!is_numeric($val)) {
            if (is_string($val)) {
                $val = Helper::strlen(trim($val));
            } elseif (is_array($val)) {
                $val = count($val);
            } else {
                return false;
            }
        }

        // fix: $val maybe an float.
        // return self::integer($val, $min, $max);
        return self::float($val, $min, $max);
    }

    /**
     * @param float|array|int|string $val
     * @param int|string|null $min
     * @param int|string|null $max
     *
     * @return bool
     * @see Validators::size()
     */
    public static function between(float|array|int|string $val, int|string $min = null, int|string $max = null): bool
    {
        return self::size($val, $min, $max);
    }

    /**
     * @param float|array|int|string $val
     * @param int|string|null $min
     * @param int|string|null $max
     *
     * @return bool
     * @see Validators::size()
     */
    public static function range(float|array|int|string $val, int|string $min = null, int|string $max = null): bool
    {
        return self::size($val, $min, $max);
    }

    /**
     * 字符串/数组长度检查
     *
     * @param array|string $val 字符串/数组
     * @param int|numeric $minLen 最小长度
     * @param int|numeric|null $maxLen 最大长度
     *
     * @return bool
     */
    public static function length(array|string $val, float|int|string $minLen = 0, float|int|string $maxLen = null): bool
    {
        if (!is_string($val) && !is_array($val)) {
            return false;
        }

        return self::size($val, (int)$minLen, $maxLen);
    }

    /**
     * 固定的长度
     *
     * @param mixed $val
     * @param int|numeric $size
     *
     * @return bool
     */
    public static function fixedSize(mixed $val, float|int|string $size): bool
    {
        if (!is_int($val)) {
            if (is_string($val)) {
                $val = Helper::strlen(trim($val));
            } elseif (is_array($val)) {
                $val = count($val);
            } else {
                return false;
            }
        }

        return $val === (int)$size;
    }

    /**
     * @param mixed $val
     * @param int|numeric $size
     *
     * @return bool
     */
    public static function lengthEq(mixed $val, float|int|string $size): bool
    {
        return self::fixedSize($val, $size);
    }

    /**
     * @param mixed $val
     * @param int|numeric $size
     *
     * @return bool
     */
    public static function sizeEq(mixed $val, float|int|string $size): bool
    {
        return self::fixedSize($val, $size);
    }

    /*******************************************************************************
     * extra string validators
     ******************************************************************************/

    /**
     * 值是否包含给的数据
     *
     * @param string|mixed $val
     * @param array|int|string $needle
     *
     * @return bool
     */
    public static function contains(mixed $val, array|int|string $needle): bool
    {
        if (!$val || !is_string($val)) {
            return false;
        }

        if (is_string($needle) || is_int($needle)) {
            return stripos($val, (string)$needle) !== false;
        }

        if (is_array($needle)) {
            foreach ($needle as $item) {
                if (stripos($val, $item) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 用正则验证数据
     *
     * @param string|numeric $val 要验证的数据
     * @param string $regexp 正则表达式 "/^M(.*)/"
     * @param null $default
     *
     * @return bool
     */
    public static function regexp(float|int|string $val, string $regexp, $default = null): bool
    {
        $options = [
            'regexp' => $regexp
        ];

        if ($default !== null) {
            $options['default'] = $default;
        }

        return (bool)filter_var($val, FILTER_VALIDATE_REGEXP, ['options' => $options]);
    }

    /**
     * alias of the 'regexp()'
     *
     * @param string|numeric $val
     * @param string $regexp
     * @param null $default
     *
     * @return bool
     */
    public static function regex(float|int|string $val, string $regexp, $default = null): bool
    {
        return self::regexp($val, $regexp, $default);
    }

    /**
     * url地址验证
     *
     * @param string|mixed $val 要验证的数据
     * @param mixed|null $default 设置验证失败时返回默认值
     * @param int|numeric $flags 标志
     *                              FILTER_FLAG_SCHEME_REQUIRED - 要求 URL 是 RFC 兼容 URL（比如 http://example）
     *                              FILTER_FLAG_HOST_REQUIRED - 要求 URL 包含主机名（比如 http://www.example.com）
     *                              FILTER_FLAG_PATH_REQUIRED - 要求 URL 在域名后存在路径（比如 www.example.com/example1/test2/）
     *                              FILTER_FLAG_QUERY_REQUIRED - 要求 URL 存在查询字符串（比如 "example.php?name=Peter&age=37"）
     *
     * @return bool
     */
    public static function url(mixed $val, mixed $default = null, float|int|string $flags = 0): bool
    {
        $settings = (int)$flags !== 0 ? ['flags' => (int)$flags] : [];

        if ($default !== null) {
            $settings['options']['default'] = $default;
        }

        return (bool)filter_var($val, FILTER_VALIDATE_URL, $settings);
    }

    /**
     * email 地址验证
     *
     * @param string $val 要验证的数据
     * @param mixed|null $default 设置验证失败时返回默认值
     *
     * @return bool
     */
    public static function email(string $val, mixed $default = null): bool
    {
        $options = [];

        if ($default !== null) {
            $options['default'] = $default;
        }

        return (bool)filter_var($val, FILTER_VALIDATE_EMAIL, ['options' => $options]);
    }

    /**
     * IP 地址验证
     *
     * @param string|mixed $val 要验证的数据
     * @param mixed|null $default 设置验证失败时返回默认值
     * @param int|numeric $flags 标志
     *                              FILTER_FLAG_IPV4 - 要求值是合法的 IPv4 IP（比如 255.255.255.255）
     *                              FILTER_FLAG_IPV6 - 要求值是合法的 IPv6 IP（比如 2001:0db8:85a3:08d3:1319:8a2e:0370:7334）
     *                              FILTER_FLAG_NO_PRIV_RANGE - 要求值不在 RFC 指定的私有范围 IP 内（比如 192.168.0.1）
     *                              FILTER_FLAG_NO_RES_RANGE - 要求值不在保留的 IP 范围内。该标志接受 IPV4 和 IPV6 值
     *
     * @return bool
     */
    public static function ip(mixed $val, mixed $default = null, float|int|string $flags = 0): bool
    {
        if (!is_string($val)) {
            return false;
        }

        $settings = (int)$flags !== 0 ? ['flags' => (int)$flags] : [];

        if ($default !== null) {
            $settings['options']['default'] = (bool)$default;
        }

        return (bool)filter_var($val, FILTER_VALIDATE_IP, $settings);
    }

    /**
     * IPv4 地址验证
     *
     * @param string|mixed $val 要验证的数据
     *
     * @return bool
     */
    public static function ipv4(mixed $val): bool
    {
        return self::ip($val, false, FILTER_FLAG_IPV4);
    }

    /**
     * IPv6 地址验证
     *
     * @param string|mixed $val 要验证的数据
     *
     * @return bool
     */
    public static function ipv6(mixed $val): bool
    {
        return self::ip($val, false, FILTER_FLAG_IPV6);
    }

    /**
     * mac Address
     *
     * @param string|mixed $input
     *
     * @return bool
     */
    public static function macAddress(mixed $input): bool
    {
        if (!is_string($input) || !$input) {
            return false;
        }

        return 1 === preg_match('/^(([0-9a-fA-F]{2}-){5}|([0-9a-fA-F]{2}:){5})[0-9a-fA-F]{2}$/', $input);
    }

    /**
     * english chars string
     *
     * @param string|mixed $val
     *
     * @return bool
     */
    public static function english(mixed $val): bool
    {
        if (!$val || !is_string($val)) {
            return false;
        }

        return 1 === preg_match('/^[A-Za-z]+$/', $val);
    }

    /**
     * 验证字段值是否是一个有效的 JSON 字符串。
     *
     * @param mixed $val
     * @param string|int|bool $strict
     *
     * @return bool
     */
    public static function json(mixed $val, string|int|bool $strict = true): bool
    {
        if (!$val || !is_string($val)) {
            return false;
        }

        // must start with: { OR [
        if ($strict && '[' !== $val[0] && '{' !== $val[0]) {
            return false;
        }

        try {
            json_decode($val, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return false;
        }
        return json_last_error() === JSON_ERROR_NONE;
    }

    /*******************************************************************************
     * array(list/map/enum) validators
     ******************************************************************************/

    /**
     * 验证值是否是一个数组
     *
     * @param mixed $val
     *
     * @return bool
     */
    public static function isArray(mixed $val): bool
    {
        return is_array($val);
    }

    /**
     * 验证值是否是一个非自然数组 map (key不是自然增长的 OR key - value 形式的)
     *
     * @param mixed $val
     *
     * @return bool
     */
    public static function isMap(mixed $val): bool
    {
        if (!is_array($val)) {
            return false;
        }

        $keys = array_keys($val);
        return array_keys($keys) !== $keys;
    }

    /**
     * 验证值是否是一个自然数组 list (key是从0自然增长的)
     *
     * @param array|mixed $val
     *
     * @return bool
     */
    public static function isList(mixed $val): bool
    {
        if (!is_array($val) || !isset($val[0])) {
            return false;
        }

        $keys = array_keys($val);
        return array_keys($keys) === $keys;
    }

    /**
     * 验证字段值是否是一个 int list(key是从0自然增长的, val是数字)
     *
     * @param array|mixed $val
     *
     * @return bool
     */
    public static function intList(mixed $val): bool
    {
        if (!is_array($val) || !isset($val[0])) {
            return false;
        }

        $lastK = -1;
        foreach ($val as $k => $v) {
            if (!is_int($k) || $k !== $lastK + 1) {
                return false;
            }

            if (!is_numeric($v)) {
                return false;
            }

            $lastK = $k;
        }

        return true;
    }

    /**
     * 验证字段值是否是一个 number list(key是从0自然增长的, val是大于0的数字)
     *
     * @param array|mixed $val
     *
     * @return bool
     */
    public static function numList(mixed $val): bool
    {
        if (!is_array($val) || !isset($val[0])) {
            return false;
        }

        $lastK = -1;
        foreach ($val as $k => $v) {
            if (!is_int($k) || $k !== $lastK + 1) {
                return false;
            }

            if (!is_numeric($v) || $v <= 0) {
                return false;
            }

            $lastK = $k;
        }

        return true;
    }

    /**
     * 验证字段值是否是一个 string list(key是从0自然增长的, val是 string)
     *
     * @param array|mixed $val
     *
     * @return bool
     */
    public static function strList(mixed $val): bool
    {
        if (!$val || !is_array($val)) {
            return false;
        }

        $lastK = -1;
        foreach ($val as $k => $v) {
            if (!is_int($k) || $k !== $lastK + 1) {
                return false;
            }

            if (!is_string($v)) {
                return false;
            }

            $lastK = $k;
        }

        return true;
    }

    /**
     * 验证字段值是否是一个 array list, 多维数组
     *
     * @param array|mixed $val
     *
     * @return bool
     */
    public static function arrList(mixed $val): bool
    {
        if (!$val || !is_array($val)) {
            return false;
        }

        foreach ($val as $k => $v) {
            if (!is_array($v)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array|mixed $val
     * @param array|int|string $key
     *
     * @return bool
     */
    public static function hasKey(mixed $val, array|int|string $key): bool
    {
        if (!$val || !is_array($val)) {
            return false;
        }

        if (is_string($key) || is_int($key)) {
            return array_key_exists($key, $val);
        }

        if (is_array($key)) {
            $keys = array_keys($val);

            return !array_diff($key, $keys);
        }

        return false;
    }

    /**
     * 验证数组时，指定的字段不能有任何重复值。
     * `['foo.*.id', 'distinct']`
     *
     * @param mixed $val
     *
     * @return bool
     */
    public static function distinct(mixed $val): bool
    {
        if (!$val || !is_array($val)) {
            return false;
        }

        return array_unique($val) === $val;
    }

    /**
     * @param mixed $val
     * @param array|string $dict
     * @param bool|string|int $strict Use strict check, will check data type.
     *
     * @return bool
     */
    public static function enum(mixed $val, array|string $dict, bool|string|int $strict = false): bool
    {
        if (is_string($dict)) {
            // $dict = array_map('trim', explode(',', $dict));
            return false !== ($strict ? strpos($dict, (string)$val) : stripos($dict, (string)$val));
        }

        return in_array($val, $dict, $strict);
    }

    /**
     * alias of 'enum()'
     *
     * @param mixed $val
     * @param array|string $dict
     * @param bool|string|int $strict
     *
     * @return bool
     */
    public static function in(mixed $val, array|string $dict, bool|string|int $strict = false): bool
    {
        return self::enum($val, $dict, $strict);
    }

    /**
     * @param mixed $val
     * @param array|string $dict
     * @param bool|string|int $strict
     *
     * @return bool
     */
    public static function notIn(mixed $val, array|string $dict, bool|string|int $strict = false): bool
    {
        if (is_string($dict) && strpos($dict, ',')) {
            $dict = array_map('trim', explode(',', $dict));
        }

        return !in_array($val, (array)$dict, $strict);
    }

    /*******************************************************************************
     * mixed data validators
     ******************************************************************************/

    /**
     * @param mixed $val
     * @param string|numeric $start
     * @param string|int|bool $strict
     *
     * @return bool
     */
    public static function startWith(mixed $val, float|int|string $start, string|int|bool $strict = true): bool
    {
        if (($start = (string)$start) === '') {
            return false;
        }

        if (is_string($val)) {
            return ($strict ? strpos($val, $start) : stripos($val, $start)) === 0;
        }

        if (is_array($val)) {
            $first = array_shift($val);

            return $strict ? $first === $start : (string)$first === $start;
        }

        return false;
    }

    /**
     * @param mixed $val
     * @param string|numeric $end
     * @param string|int|bool $strict
     *
     * @return bool
     */
    public static function endWith(mixed $val, float|int|string $end, string|int|bool $strict = true): bool
    {
        $last = null;
        $end  = (string)$end;

        if (is_string($val)) {
            $last = substr($val, -Helper::strlen($end));
        } elseif (is_array($val)) {
            $last = array_pop($val);
        } else {
            return false;
        }

        return $strict ? $last === $end : (string)$last === $end;
    }

    /*******************************************************************************
     * date validators
     ******************************************************************************/

    /**
     * 校验字段值是否是日期格式
     *
     * @param string|mixed $val 日期
     * @param bool|string $shouldGt0
     *
     * @return boolean
     */
    public static function date(mixed $val, bool|string $shouldGt0 = false): bool
    {
        if (!$val) {
            return false;
        }

        // strtotime 转换不对，日期格式显然不对
        $time = strtotime((string)$val);

        return $shouldGt0 ? $shouldGt0 > 1 : $time !== false;
    }

    /**
     * 校验字段值是否是等于给定日期
     *
     * @param string|mixed $val
     * @param string|mixed $date 给定日期
     *
     * @return boolean
     */
    public static function dateEquals(mixed $val, mixed $date): bool
    {
        if (!$val || (!$time = strtotime((string)$val))) {
            return false;
        }

        return $date && $time === strtotime((string)$date);
    }

    /**
     * 校验字段值是否是日期并且是否满足设定格式
     *
     * @param string|mixed $val 日期
     * @param string $format 需要检验的格式数组
     *
     * @return bool
     */
    public static function dateFormat(mixed $val, string $format = 'Y-m-d'): bool
    {
        if (!$val || !($unixTime = strtotime($val))) {
            return false;
        }

        // 校验日期的格式有效性
        return date($format, $unixTime) === $val;
    }

    /**
     * 字段值必须是给定日期之前的值
     *
     * @param string|mixed $val
     * @param string|numeric $beforeDate 若为空，将使用当前时间
     * @param string $symbol allow '<' '<='
     *
     * @return bool
     */
    public static function beforeDate(mixed $val, float|int|string $beforeDate = '', string $symbol = '<'): bool
    {
        if (!$val || !is_string($val)) {
            return false;
        }

        if (!$valueTime = strtotime($val)) {
            return false;
        }

        $beforeTime = $beforeDate ? strtotime($beforeDate) : time();

        if ($symbol === '<') {
            return $valueTime < $beforeTime;
        }

        return $valueTime <= $beforeTime;
    }

    /**
     * 字段值必须是小于或等于给定日期的值
     *
     * @param string|mixed $val
     * @param string|numeric $beforeDate
     *
     * @return bool
     */
    public static function beforeOrEqualDate(mixed $val, float|int|string $beforeDate): bool
    {
        return self::beforeDate($val, $beforeDate, '<=');
    }

    /**
     * 字段值必须是给定日期之后的值
     *
     * @param string|mixed $val
     * @param string|mixed $afterDate
     * @param string $symbol allow: '>' '>='
     *
     * @return bool
     */
    public static function afterDate(mixed $val, mixed $afterDate, string $symbol = '>'): bool
    {
        if (!$val || !is_string($val)) {
            return false;
        }

        if (!$valueTime = strtotime($val)) {
            return false;
        }

        $afterTime = $afterDate ? strtotime($afterDate) : time();

        if ($symbol === '>') {
            return $valueTime > $afterTime;
        }

        return $valueTime >= $afterTime;
    }

    /**
     * 字段值必须是大于或等于给定日期的值
     *
     * @param string|mixed $val
     * @param string|mixed $afterDate
     *
     * @return bool
     */
    public static function afterOrEqualDate(mixed $val, mixed $afterDate): bool
    {
        return self::afterDate($val, $afterDate, '>=');
    }

    /**
     * Check for date format
     *
     * @param string|mixed $date Date to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isDateFormat(mixed $date): bool
    {
        if (!$date || !is_string($date)) {
            return false;
        }

        return 1 === preg_match(self::REGEX_DATE_FMT, $date);
    }

    /**
     * Check for date validity
     *
     * @param string|mixed $date Date to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isDate(mixed $date): bool
    {
        if (!$date || !is_string($date)) {
            return false;
        }

        if (!preg_match(self::REGEX_DATE, $date, $matches)) {
            return false;
        }

        return checkdate((int)$matches[2], (int)$matches[3], (int)$matches[1]);
    }

    /*******************************************************************************
     * extension validators
     ******************************************************************************/

    /**
     * @param string|mixed $val
     *
     * @return bool
     */
    public static function phone(mixed $val): bool
    {
        return 1 === preg_match('/^1[2-9]\d{9}$/', (string)$val);
    }

    // public static function telNumber($val)
    // {}

    /**
     * Check for postal code validity
     *
     * @param string|mixed $val Postal code to validate
     *
     * @return bool Validity is ok or not
     */
    public static function postCode(mixed $val): bool
    {
        return $val && is_string($val) && preg_match('/^\d{6}$/', $val);
    }

    /**
     * Check for price validity
     *
     * @param string|mixed $price Price to validate
     *
     * @return bool Validity is ok or not
     */
    public static function price(mixed $price): bool
    {
        return 1 === preg_match('/^[\d]{1,10}(\.[\d]{1,9})?$/', (string)$price);
    }

    /**
     * Check for price validity (including negative price)
     *
     * @param string|mixed $price Price to validate
     *
     * @return bool Validity is ok or not
     */
    public static function negativePrice(mixed $price): bool
    {
        return 1 === preg_match('/^[-]?[\d]{1,10}(\.[\d]{1,9})?$/', (string)$price);
    }

    /**
     * Check for a float number validity
     *
     * @param float|mixed $float Float number to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isFloat(mixed $float): bool
    {
        if (!is_scalar($float)) {
            return false;
        }

        return (string)((float)$float) === (string)$float;
    }

    /**
     * @param float|mixed $float
     *
     * @return bool
     */
    public static function isUnsignedFloat(mixed $float): bool
    {
        if (!is_scalar($float)) {
            return false;
        }

        return (string)((float)$float) === (string)$float && $float >= 0;
    }

    /**
     * Check for an integer validity
     *
     * @param int|float|string $value Integer to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isInt(int|float|string $value): bool
    {
        return (string)(int)$value === (string)$value;
    }

    /**
     * Check for an integer validity (unsigned)
     *
     * @param int|numeric $value Integer to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isUnsignedInt(float|int|string $value): bool
    {
        return ((string)(int)$value === (string)$value && $value < 4294967296 && $value >= 0);
    }

    /**
     * Check for MD5 string validity
     *
     * @param string|mixed $val MD5 string to validate
     *
     * @return bool Validity is ok or not
     */
    public static function md5(mixed $val): bool
    {
        if (!$val || !is_string($val)) {
            return false;
        }

        return 1 === preg_match('/^[a-f0-9A-F]{32}$/', $val);
    }

    /**
     * Check for SHA1 string validity
     *
     * @param string|mixed $val SHA1 string to validate
     *
     * @return bool Validity is ok or not
     */
    public static function sha1(mixed $val): bool
    {
        if (!$val || !is_string($val)) {
            return false;
        }

        return 1 === preg_match('/^[a-fA-F0-9]{40}$/', $val);
    }

    /**
     * Check object validity
     *
     * @param string|mixed $val e.g '#dedede'
     *
     * @return bool Validity is ok or not
     */
    public static function color(mixed $val): bool
    {
        if (!$val || !is_string($val)) {
            return false;
        }

        return 1 === preg_match('/^(#[0-9a-fA-F]{6}|[a-zA-Z0-9-]*)$/', $val);
    }

    /**
     * Check if URL is absolute
     *
     * @param string|mixed $url URL to validate
     *
     * @return bool Validity is ok or not
     */
    public static function absoluteUrl(mixed $url): bool
    {
        if (!$url || !is_string($url)) {
            return false;
        }

        return 1 === preg_match(self::REGEX_ABS_URL, $url);
    }

    /**
     * Check for standard name file validity
     *
     * @param string|mixed $name Name to validate
     *
     * @return bool Validity is ok or not
     */
    public static function fileName(mixed $name): bool
    {
        if (!$name || !is_string($name)) {
            return false;
        }

        return 1 === preg_match('/^[a-zA-Z0-9_.-]+$/', $name);
    }

    /**
     * Check for standard name directory validity
     *
     * @param string|mixed $dir Directory to validate
     *
     * @return bool Validity is ok or not
     */
    public static function dirName(mixed $dir): bool
    {
        if (!$dir || !is_string($dir)) {
            return false;
        }

        return 1 === preg_match('/^[a-zA-Z0-9_.-]*$/', $dir);
    }
}
