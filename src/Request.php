<?php
namespace Datlv\Enum;

use Datlv\Kit\Extensions\Request as BaseRequest;

/**
 * Class Request
 *
 * @package Datlv\Enum
 */
class Request extends BaseRequest
{
    public $trans_prefix = 'enum::common';
    public $rules = [
        'title'  => 'required|max:255',
        'slug'   => 'required|max:255|alpha_dash',
        'params' => 'max:255',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->rules;
    }

}
