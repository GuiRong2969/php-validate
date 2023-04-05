<?php declare(strict_types=1);
/**
 * Created by VsCode.
 * User: Guirong
 * Date: 2023/04/03
 * Time: 09:30
 */

namespace Guirong\ValidateTest\Validator;

use Guirong\Validate\Validator\UserValidators;
use PHPUnit\Framework\TestCase;

/**
 * Class UserValidatorsTest
 *
 * @package Guirong\ValidateTest\Validator
 */
class UserValidatorsTest extends TestCase
{
    public function testBasic(): void
    {
        UserValidators::removeAll();
        UserValidators::setValidators([
            'name1' => static function () {
            },
            'name2' => static function () {
            },
            ''      => static function () {
            },
        ]);

        $this->assertCount(2, UserValidators::getValidators());
        $this->assertTrue(UserValidators::has('name1'));
        $this->assertFalse(UserValidators::has(''));

        $this->assertNotEmpty(UserValidators::get('name2'));
        $this->assertEmpty(UserValidators::get('name3'));

        UserValidators::remove('name1');
        $this->assertFalse(UserValidators::has('name1'));

        UserValidators::removeAll();
        $this->assertCount(0, UserValidators::getValidators());
    }
}
