<?php namespace Datlv\Enum;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class EnumValidator
 * @package Datlv\Enum
 * @author Minh Bang
 */
class EnumValidationRule implements Rule
{
    /**
     * @var string
     */
    protected $column;

    /**
     * @var bool
     */
    protected $isList;
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $enums;

    /**
     * EnumRule constructor.
     * @param string $column
     * @param string $type
     * @param bool $isList
     */
    public function __construct($type, $column = 'id', $isList = false)
    {
        abort_unless(in_array($column, ['id', 'title']), 500, 'Invalid Enum column parameter');
        abort_unless(app('enum')->has($type), 500, 'Invalid Enum type parameter');
        $this->enums = EnumModel::whereType($type)->get();
        $this->column = $column;
        $this->isList = $isList;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->isList) {
            $values = is_string($value) ? array_map('trim', explode(',', $value)) : $value;
            $values = collect($values)->unique();
            return $values
                ? $values->count() == $this->enums->pluck($this->column)->unique()->intersect($values)->count()
                : false;
        } else {
            return $this->enums->where($this->column, $value)->isNotEmpty();
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans("enum::common.validation_fail");
    }
}