<?php namespace Datlv\Enum\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Datlv\Enum\UseEnum;

/**
 * Class Model2
 *
 * @property integer $id
 * @property string $name
 * @property integer $author_id
 *
 * @package Datlv\Enum\Test\Models
 */
class Model2 extends Model
{
    use UseEnum;

    public $table = 'enumtest_model2s';
    public $timestamps = false;
    protected $fillable = ['name', 'author_id', 'security_id'];
    protected $enumGuarded = ['security_id'];
}