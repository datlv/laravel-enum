<?php namespace Datlv\Enum;

use Enum;
use Kit;
use Datlv\Kit\Extensions\Model;
use Datlv\Kit\Traits\Model\PositionTrait;

/**
 * Class EnumModel
 *
 * @package Datlv\Enum
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int $position
 * @property string $type
 * @property string $params
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Kit\Extensions\Model except($ids)
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Kit\Extensions\Model findText($column, $text)
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Enum\EnumModel for ($model, $type = null)
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Enum\EnumModel forModels($models)
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Enum\EnumModel forAliases($aliases)
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Enum\EnumModel orderPosition($direction = 'asc')
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Kit\Extensions\Model whereAttributes($attributes)
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Enum\EnumModel whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Enum\EnumModel whereParams($value)
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Enum\EnumModel wherePosition($value)
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Enum\EnumModel whereSlug($value)
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Enum\EnumModel whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\Datlv\Enum\EnumModel whereType($value)
 * @mixin \Eloquent
 */
class EnumModel extends Model
{
    use PositionTrait;
    public $timestamps = false;
    protected $table = 'enums';
    protected $fillable = ['title', 'slug', 'position', 'model', 'type', 'params'];

    /**
     * So sánh các position của 2 Enums CÙNG TYPE có id là $id1 và $id2
     *
     * @param int $id1
     * @param int $id2
     * @param string $operator
     * @param int $error
     *
     * @return bool|-1
     */
    public static function compare($id1, $id2, $operator = '=', $error = -1)
    {
        $enum1 = static::find($id1);
        $enum2 = static::find($id2);
        if ($enum1 && $enum2 && ($enum1->type === $enum2->type)) {
            switch ($operator) {
                case '>':
                    return $enum1->position > $enum2->position;
                    break;
                case '>=':
                    return $enum1->position >= $enum2->position;
                    break;
                case '<':
                    return $enum1->position < $enum2->position;
                    break;
                case '<=':
                    return $enum1->position <= $enum2->position;
                    break;
                default:
                    return $enum1->position === $enum2->position;
            }
        } else {
            return $error;
        }
    }

    /**
     * Kiểm tra enum có đang sử dụng trong các resource không
     *
     * @return bool
     */
    public function isUsed()
    {
        //TODO: kiểm tra sự làm việc của HasEnum + các model sử dụng enum
        return false;
        /** @var \Datlv\Enum\HasEnum[] $models */
        $models = Enum::filtered()->pluck('model');
        foreach ($models as $model) {
            if (in_array($this->id, $model->getEnumUsed())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enums của $model $type
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|mixed $model
     * @param string $type
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeFor($query, $model, $type = null)
    {
        $value = Kit::alias($model) . '.' . ($type ?: '%');
        $operator = $type ? '=' : 'like';

        return $query->where('type', $operator, $value);
    }

    /**
     * Enums của $model $type
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $models
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForModels($query, $models)
    {
        return $this->scopeForAliases($query, Kit::aliases($models));
    }

    /**
     * Enums của $model $type
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $aliases
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForAliases($query, $aliases)
    {
        $values = array_map(function ($alias) {
            return "{$alias}.%";
        }, $aliases);

        return $query->where(function ($q) use ($values) {
            foreach ($values as $value) {
                $q->orWhere('type', 'like', $value);
            }
        });
    }

    /**
     * @return string
     */
    public function modelTitle()
    {
        list($model,) = explode('.', $this->type);

        return Kit::isAlias($model) ? Kit::title($model) : 'Unknow';
    }
}
