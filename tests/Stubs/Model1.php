<?php namespace Datlv\Enum\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Datlv\Enum\UseEnum;

/**
 * Class Model1
 *
 * @property integer $id
 * @property string $name
 * @property integer $author1_id
 * @property integer $author2_id
 * @property integer $security_id
 *
 * @package Datlv\Enum\Test\Models
 */
class Model1 extends Model
{
    use UseEnum;

    public $table = 'enumtest_model1s';
    public $timestamps = false;
    protected $fillable = ['name', 'author1_id', 'author2_id', 'security_id'];
    protected $enumGuarded = ['security_id'];
}