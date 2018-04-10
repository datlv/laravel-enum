<?php namespace Datlv\Enum;

/**
 * Trait EnumPresenter
 * @property-read \Datlv\Enum\UseEnum $entity
 * @package Datlv\Enum
 * @mixin \Eloquent
 */
trait EnumPresenter
{
    /**
     * @var array
     */
    protected $enumables;

    /**
     * @param string $attr
     * @param string $begin
     * @param string $end
     * @return string
     */
    public function enumable($attr, $begin = '<span class="label label-success">', $end = '</span>')
    {
        return ($values = array_get($this->getEnumables(), $attr)) ? $begin . implode($end . $begin, $values) . $end : '';
    }

    /**
     * @return array
     */
    protected function getEnumables()
    {
        if (is_null($this->enumables)) {
            $this->enumables = $this->entity->loadEnumableValues(true);
        }
        return $this->enumables;
    }
}