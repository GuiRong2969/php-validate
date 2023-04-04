<?php
/**
 * Created by VsCode.
 * User: Guirong
 * Date: 2023/4/3 09:30
 */

/**
 * Class PageRequest
 */
class PageRequest extends \Guirong\Validate\Validation
{
    public function rules()
    {
        return [
            ['tagId,userId,freeTime', 'required' ],
            ['tagId', 'size', 'min'=>4, 'max'=>567], // 4<= tagId <=567
            ['title', 'min', 'min' => 40],
            ['freeTime', 'number', 'msg' => '{attr} is require number!'],
            ['test', 'number', 'when' => function($data) {
                return isset($data['status']) && $data['status'] > 2;
            }],
            ['userId', 'number', 'on' => 'other' ],
//            ['userId', function($value){ return false;}],
        ];
    }

    public function translates()
    {
        return [
            'userId' => '用户Id',
        ];
    }

    // custom validator message
    public function messages()
    {
        return [
            'required' => '{attr} 是必填项。',
        ];
    }
}
