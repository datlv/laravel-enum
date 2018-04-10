<?php
namespace Datlv\Enum;

use DB;
use Datlv\Kit\Support\VnString;

/**
 * Class Seeder
 *
 * @package Datlv\Enum
 */
class Seeder
{
    /**
     * @param array $data
     */
    public function seed($data = [])
    {
        DB::table('enums')->truncate();

        $models = [];
        foreach ($data as $resource => $enums) {
            foreach ($enums as $type => $items) {
                foreach ($items as $i => $item) {
                    if (strpos($item, '|')) {
                        list($item, $params) = explode('|', $item, 2);
                    } else {
                        $params = null;
                    }
                    $models[] = [
                        'title'    => $item,
                        'slug'     => VnString::to_slug($item),
                        'position' => $i + 1,
                        'type'     => "{$resource}.{$type}",
                        'params'   => $params,
                    ];
                }
            }
        }
        DB::table('enums')->insert($models);
    }
}