<?php namespace Datlv\Enum\Tests;

use Enum;
use Datlv\Enum\EnumModel;
use Datlv\Enum\Tests\Stubs\Model1;
use Datlv\Enum\Tests\Stubs\Model2;
use Datlv\Enum\Tests\Stubs\TestCase;

class ManagerTest extends TestCase
{
    public function test_register_enum_type()
    {
        $this->assertTrue(Enum::has('model1.security'));
        $this->assertTrue(Enum::has('model1.author'));
        $this->assertTrue(Enum::has('model1.multi'));
    }

    public function test_register_model_enum_attributes()
    {
        $this->assertTrue(Enum::attributes(Model1::class)->all() == [
                'security_id' => 'model1.security',
                'author1_id' => 'model1.author',
                'author2_id' => 'model1.author',
            ]);
        $this->assertTrue(Enum::enumableAttributes(Model1::class)->all() == [
                'model1.multi' => 'multi',
            ]);
    }

    public function test_Shared_model_enum_attributes()
    {
        $this->assertTrue(Enum::attributes(Model2::class)->all() == [
                'security_id' => 'model1.security',
                'author_id' => 'model1.author',
                'place_id' => 'model2.place',
            ]);
    }

    public function test_Shared_model_enumable_attributes()
    {
        $this->assertTrue(Enum::enumableAttributes(Model2::class)->all() == [
                'model1.multi' => 'multi',
            ]);
    }

    public function test_Titles_to_Ids()
    {
        $enum1 = EnumModel::create([
            'title' => 'Author 1',
            'slug' => 'author-1',
            'type' => 'model1.author'
        ]);
        $enum2 = EnumModel::create([
            'title' => 'Author 2',
            'slug' => 'author-2',
            'type' => 'model1.author'
        ]);
        $enum3 = EnumModel::create([
            'title' => 'Author 3',
            'slug' => 'author-3',
            'type' => 'model1.author'
        ]);
        $this->assertTrue(Enum::getIds($enum1->title, 'model1.author')->all() == [$enum1->id]);
        $this->assertTrue(Enum::getIds([$enum1->title], 'model1.author')->all() == [$enum1->id]);
        $this->assertTrue(Enum::getIds([$enum1->title, $enum2->title], 'model1.author')->all() == [$enum1->id, $enum2->id]);
        $this->assertTrue(
            Enum::getIds("Author 1, Author 2,{$enum3->title}", 'model1.author')->all() == [$enum1->id, $enum2->id, $enum3->id]
        );
        $this->assertTrue(Enum::getIds('', 'model1.author')->isEmpty());
        $this->assertTrue(Enum::getIds('Abc', 'model1.author')->isEmpty());
    }
}