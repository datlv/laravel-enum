<?php namespace Datlv\Enum;

use Illuminate\Support\Collection;
use Kit;

/**
 * Class Manager
 * Enum Zone: giới hạn chỉ thao tác với số models, chứ không phải toàn bộ model đã đăng ký qua register()
 *
 * @package Datlv\Enum
 */
class Manager extends Collection
{
    /**
     * Chỉ được phép thao tác với model aliases này
     *
     * @var array
     */
    protected $models;
    /**
     * @var array
     */
    protected $sharedModels = [];

    /**
     * Load tất cả enums trong db của $model, dùng trong form create/edit model
     * Gom nhóm theo types, tên nhóm type trả về là dạng số nhiều,
     * vd: type = 'car', trả về ['cars' => ['Toyota', 'Camry'],...]
     *
     * @param string|mixed $model
     * @param string $key
     * @param string $attribute
     *
     * @return array
     */
    public function loadEnums($model, $key = 'id', $attribute = 'title')
    {
        $types = $this->filterByModel($model)->map(function ($type) {
            return $type['id'];
        });
        $default = $types->mapWithKeys(function ($id) {
            return [$this->getTypeGroup($id) => []];
        })->all();
        $enums = EnumModel::whereIn('type', $types)->get();

        return $enums->groupBy(function (EnumModel $enum) {
                return $this->getTypeGroup($enum->type);
            })->map(function (Collection $enums) use ($key, $attribute) {
                return $enums->mapWithKeys(function (EnumModel $enum) use ($key, $attribute) {
                    return [$enum->{$key} => $enum->{$attribute}];
                })->all();
            })->all() + $default;
    }

    /**
     * Sử dụng: trong base backend controller contructor của module nào đó!
     *
     * @param array $models
     */
    public function onlyModels($models)
    {
        $this->models = Kit::aliases($models);
    }

    /**
     * Không đăng ký enums mới, enums của $model sẻ sử dụng của model $by, ví dụ:
     * [
     *      $model => [
     *          $by => $attribute: array các name, 2 trường hợp như bên dưới...
     *       ]
     * ]
     * - 1. Bình thường: ['name1',...]
     * - 2. Remap attribute: ['name1' => 'new_attr',...], hoặc ['name1' => ['new_attr1','new_attr2'],...]
     * - 3. Enumable attribute (đặc biệt của dạng 2, empty $attribute): ['name1' => empty(false|''|[]...)]
     *
     * @param string|mixed $model
     * @param string|mixed $by
     * @param array $attributes
     *
     * @return \Datlv\Enum\Manager
     */
    public function shared($model, $by, $attributes)
    {
        $alias = Kit::alias($model);
        if (!isset($this->sharedModels[$alias])) {
            $this->sharedModels[$alias] = [];
        }
        $this->sharedModels[$alias][Kit::alias($by)] = $attributes;
        return $this;
    }

    /**
     * Đăng ký Model có sử dụng Enums
     * $types = [
     *      'type name' => [
     *          'title' => 'trans::....'
     *          'attr' => 'language_id',
     *      ],
     *      ...
     * ]
     *
     * @param string|mixed $model
     * @param array $types
     * @return \Datlv\Enum\Manager
     */
    public function register($model, $types)
    {
        $class = Kit::getClass($model);
        $alias = Kit::alias($class);
        $title = Kit::title($class);
        foreach ($types as $name => $info) {
            $info['model_alias'] = $alias;
            $info['model_title'] = $title;
            $info['id'] = "{$alias}.{$name}";
            $info['name'] = $name;
            $this->put($info['id'], $info);
        }
        return $this;
    }

    /**
     * @param string|mixed $model
     * @param string $attr
     * @return string
     */
    public function getEnumType($model, $attr)
    {
        return $this->getTypeAlias($model, $attr) . '.' . $attr;
    }

    /**
     * Nhóm các type theo model title
     *
     * @return array
     */
    public function groupedTypes()
    {
        return $this->filtered()->groupBy('model_title')->map(function (Collection $types) {
            return $types->mapWithKeys(function ($type) {
                return [$type['id'] => mb_fn_str($type['title'])];
            })->all();
        })->all();
    }

    /**
     * @return \Datlv\Enum\Manager
     */
    public function filtered()
    {
        return $this->models ? $this->filter(function ($type) {
            return in_array($type['model_alias'], $this->models);
        }) : $this;
    }

    /**
     * Lấy type info đầu tiên (Hoặc chỉ lấy 1 $attribute)
     *
     * @param string $attribute
     *
     * @return array|mixed
     */
    public function firstType($attribute = null)
    {
        return array_get($this->filtered()->first(), $attribute);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isRegistered($type)
    {
        return $this->filtered()->has($type);
    }

    /**
     * Lấy tất cả attributes là enum của $model, nếu shared chỉ lấy các attributes của nó
     *
     * @param mixed|string $model
     * @return \Illuminate\Support\Collection
     */
    public function attributes($model)
    {
        $alias = Kit::alias($model);
        return $this->filterByModel($model)->mapWithKeys(function ($type) use ($alias) {
            $result = [];
            $attributes = $this->getSharedAttributes($alias, $type['model_alias'], $type['name'], array_get($type, 'attr', []));
            if ($attributes) {
                foreach ((array)$attributes as $attr) {
                    $result[$attr] = $type['id'];
                }
            }
            return $result;
        });
    }

    /**
     * Lấy các enum attributes nhiều-nhiều
     * @param mixed|string $model
     * @return \Illuminate\Support\Collection
     */
    public function enumableAttributes($model)
    {
        return $this->filterByModel($model)->mapWithKeys(function ($type) {
            return empty($type['attr']) ? [$type['id'] => $type['name']] : [];
        });
    }

    /**
     * @param array|string $titles
     * @param string $type
     * @return \Illuminate\Support\Collection
     */
    public function getIds($titles, $type)
    {
        $titles = is_string($titles) ? array_map('trim', explode(',', $titles)) : $titles;
        return $titles ? EnumModel::whereType($type)->whereIn('title', $titles)->pluck('id') : collect([]);
    }

    /**
     * Lọc chỉ lấy enum type của $model, 2 trường hợp
     * - Attribute của riêng nó (register...):
     *
     * - Attribute được share từ model khác (shared...):
     *
     *
     * @param mixed|string $model
     *
     * @return static
     */
    protected function filterByModel($model)
    {
        $alias = Kit::alias($model);
        return $this->serializeSharedAttribute()->filter(function ($type) use ($alias) {
            return $type['model_alias'] == $alias || $this->hasSharedAttributes($alias, $type['model_alias'], $type['name']);
        });
    }

    /**
     * Chuẩn hóa, nếu dạng 1 => lấy danh sách attribute gốc của model đã share
     * @return \Datlv\Enum\Manager
     */
    protected function serializeSharedAttribute()
    {
        foreach ($this->sharedModels as &$sharedList) {
            foreach ($sharedList as $origin => &$attributes) {
                $result = [];
                foreach ($attributes as $k => $v) {
                    if (is_int($k)) {
                        $result[$v] = array_get($this->get("{$origin}.{$v}", []), 'attr');
                    } else {
                        $result[$k] = $v;
                    }
                }
                $attributes = $result;
            }
        }
        return $this;
    }

    /**
     * @param string $modelAlias
     * @param string $sharedAlias
     * @param string $typeName
     * @return bool
     */
    protected function hasSharedAttributes($modelAlias, $sharedAlias, $typeName)
    {
        return isset($this->sharedModels[$modelAlias][$sharedAlias][$typeName]);
    }

    /**
     * Lấy tên nhóm enum, vd: ebook.language => languages
     *
     * @param string $type
     *
     * @return string
     */
    protected function getTypeGroup($type)
    {
        if (strpos($type, '.') !== false) {
            $type = explode('.', $type)[1];
        }

        return str_plural($type);
    }

    /**
     * @param string $modelAlias
     * @param string $sharedAlias
     * @param string $typeName
     * @param array $default
     * @return array
     */
    protected function getSharedAttributes($modelAlias, $sharedAlias, $typeName, $default = [])
    {
        return isset($this->sharedModels[$modelAlias][$sharedAlias][$typeName]) ?
            $this->sharedModels[$modelAlias][$sharedAlias][$typeName] : $default;
    }

    /**
     * Lấy phần phía trước của type name:
     * Nếu type đó được share từ model2 => model2 alias, ngược lại alias model
     *
     * @param string|mixed $model
     * @param string $typeName
     * @return string
     */
    protected function getTypeAlias($model, $typeName)
    {
        $this->serializeSharedAttribute();
        $modelAlias = Kit::alias($model);
        if (isset($this->sharedModels[$modelAlias])) {
            foreach ($this->sharedModels[$modelAlias] as $alias => $attributes) {
                if (isset($attributes[$typeName])) {
                    return $alias;
                }
            }
        }
        return $modelAlias;
    }
}