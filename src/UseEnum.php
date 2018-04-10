<?php namespace Datlv\Enum;

use DB;
use Enum;
use Datlv\Kit\Support\VnString;

/**
 * Class UseEnum
 * Sử dụng cho các model có thuộc tính là enum, 2 Loại enum attribute: 1-1 (name_id) và nhiều-nhiều (enumable)
 * nhiều-nhiều khi register không có tham số 'attr'
 *
 * @property array $enumGuarded
 * @package Datlv\Enum
 * @mixin \Eloquent
 */
trait UseEnum
{
    /**
     * @var array IDs của các Enum đang sử dụng trong tất cả resource model này
     */
    protected static $enumUsedIds;

    /**
     * Xử lý Enum trước khi save $model
     */
    public static function bootUseEnum()
    {
        static::saving(function ($model) {
            /** @var static $model */
            $model->enumSave();
        });
        static::deleting(function ($model) {
            /** @var static $model */
            if ($enumableTypes = $model->getEnumableAttributes()->keys()->all()) {
                DB::table('enumables')->where('enumable_id', $model->id)
                    ->whereIn('enumable_type', $enumableTypes)->delete();
            }
        });
    }

    /**
     * Thêm '_cmd_create:' phía trước enum để tạo enum mới
     * Nếu attribute thuộc danh sách guarded sẻ không cho phép tạo mới (chỉ admin tạo trong trang Quản lý Enum)
     */
    public function enumSave()
    {
        $guarded = $this->enumGuarded ?: [];
        foreach ($this->getEnumAttributes() as $attr => $type) {
            $value = trim($this->{$attr});
            if (str_is('_cmd_create:*', $value)) {
                if (strlen($value) > 12 && !in_array($attr, $guarded)) {
                    $value = substr($value, 12);
                    /** @var \Datlv\Enum\EnumModel $enum */
                    $enum = EnumModel::firstOrCreate(['title' => $value], [
                        'slug' => VnString::to_slug($value),
                        'type' => $type,
                    ]);
                    $this->{$attr} = $enum->id;
                } else {
                    $this->{$attr} = null;
                }
            }
        }
    }

    /**
     * Lấy các enum attributes một-một
     * @return \Illuminate\Support\Collection
     */
    protected function getEnumAttributes()
    {
        return Enum::attributes(static::class);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function getEnumableAttributes()
    {
        return Enum::enumableAttributes(static::class);
    }

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public static function isEnumAttribute($attribute)
    {
        return Enum::attributes(static::class)->has($attribute);
    }

    /**
     * Shortcut to Enum Manager loadEnums()
     *
     * @param string $key
     * @param string $attribute
     *
     * @return array
     */
    public static function loadEnums($key = 'id', $attribute = 'title')
    {
        return Enum::loadEnums(static::class, $key, $attribute);
    }

    /**
     * @param bool $loadTitle
     * @return array
     */
    public function loadEnumableValues($loadTitle = false)
    {
        $attributes = $this->getEnumableAttributes();
        $result = $attributes->mapWithKeys(function ($attr) {
            return [$attr => []];
        })->all();
        if ($this->exists) {
            $result = DB::table('enumables')
                    ->where('enumable_id', $this->id)
                    ->whereIn('enumable_type', $attributes->keys()->all())
                    ->leftJoin('enums', 'enums.id', '=', 'enumables.enum_id')
                    ->select('enumables.*', 'enums.title')
                    ->get()
                    ->groupBy('enumable_type')
                    ->mapWithKeys(function ($items, $type) use ($attributes, $loadTitle) {
                        /** @var \Illuminate\Support\Collection $items */
                        return ($attr = $attributes->get($type)) ?
                            [
                                $attr => $loadTitle ?
                                    $items->mapWithKeys(function ($enum) {
                                        return [(int)$enum->enum_id => $enum->title];
                                    })->all() :
                                    $items->map(function ($enum) {
                                        return (int)$enum->enum_id;
                                    })->all()
                            ] :
                            [];
                    })->all() + $result;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getEnumUsed()
    {
        if (is_null(static::$enumUsedIds)) {
            $attributes = $this->getEnumAttributes();
            $ids = [];
            foreach ($attributes as $attribute) {
                $ids = array_merge($ids, $this->newQuery()->groupBy($attribute)->pluck($attribute)->all());
            }
            static::$enumUsedIds = array_unique($ids);
        }

        return static::$enumUsedIds;
    }

    /**
     * Lấy 'giá trị' Enum từ id
     *
     * @param string $attr
     * @param string $column
     * @param null $default
     *
     * @return mixed|string
     */
    public function getEnumValue($attr, $column = 'title', $default = null)
    {
        $result = $default;
        $id = $this->{$attr};
        /** @var \Datlv\Enum\EnumModel $enum */
        if ($id && ($enum = EnumModel::find($id))) {
            $result = $column ? $enum->{$column} : $enum;
        }

        return $result;
    }

    /**
     * @param string $attr
     * @param string $key
     * @param string $attribute
     * @param array $default
     * @return array
     */
    public function getEnumableValues($attr, $key = 'id', $attribute = 'title', $default = [])
    {
        if (static::isEnumableAttribute($attr)) {
            return DB::table('enumables')
                ->where('enumable_id', $this->id)
                ->where('enumable_type', Enum::getEnumType(static::class, $attr))
                ->leftJoin('enums', 'enums.id', '=', 'enumables.enum_id')
                ->select(["enums.{$key}", "enums.{$attribute}"])
                ->orderBy('enumables.position')
                ->get()
                ->mapWithKeys(function ($enum) use ($key, $attribute) {
                    return [$enum->{$key} => $enum->{$attribute}];
                })
                ->all();
        }
        return $default;
    }

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public static function isEnumableAttribute($attribute)
    {
        return Enum::enumableAttributes(static::class)->contains($attribute);
    }
    /**
     * Lấy các enum attributes nhiều-nhiều
     */

    /**
     * Cập nhật Tất cả attribute có dạng enum nhiều-nhiều
     * Cập nhật các enumable attribute
     * @param array $input
     */
    public function fillEnumable($input)
    {
        abort_unless($this->id, 500, 'UseEnum: make sure to call fillEnumable() after save()');
        $attributes = $this->getEnumableAttributes();
        foreach ($attributes as $type => $name) {
            $this->saveEnumable($type, isset($input[$name]) ? $input[$name] : []);
        }
    }

    /**
     * Cập nhật MỘT attribute có dạng enum nhiều-nhiều
     * @param string $attrType
     * @param int[] $enumIds
     */
    protected function saveEnumable($attrType, $enumIds)
    {
        DB::table('enumables')->where('enumable_id', $this->id)->where('enumable_type', $attrType)->delete();
        $items = [];
        foreach ((array)$enumIds as $i => $id) {
            $items[] = [
                'enum_id' => $id,
                'enumable_id' => $this->id,
                'enumable_type' => $attrType,
                'position' => $i + 1,
            ];
        }
        DB::table('enumables')->insert($items);
    }

    /**
     * Load title của tất cả các enum attributes,
     * vd: language_id => title: language_title, params: language_params
     * Chú ý: alias của table 'enums' là số nhiều của 'language' => 'languages'
     *
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithEnumTitles($query)
    {
        foreach ($this->getEnumAttributes() as $attr => $type) {
            $name = substr($attr, 0, -3); // Bỏ '_id' ở tên attribute, vd: writer_id  =>  writer
            //$name = explode('.', $type)[1];
            $alias = str_plural($name);
            $query->leftJoin("enums as {$alias}", "{$this->table}.{$attr}", '=', "{$alias}.id")->addSelect([
                "{$alias}.title as {$name}_title",
                "{$alias}.params as {$name}_params",
            ]);
        }

        return $query;
    }
}