<?php namespace Datlv\Enum\Tests;

use Datlv\Enum\EnumModel;
use Datlv\Enum\Tests\Stubs\Model1;
use Datlv\Enum\Tests\Stubs\Model2;
use Datlv\Enum\Tests\Stubs\TestCase;


class UseEnumTest extends TestCase
{
    public function test_Create_model_with_exists_enum_value()
    {
        /** @var  \Datlv\Enum\EnumModel $author */
        $author = EnumModel::create([
            'title' => 'Author 0',
            'slug' => 'author0',
            'type' => 'model1.author',
        ]);
        $data = [
            'name' => 'Model 0',
            'author1_id' => $author->id,
        ];
        /** @var \Datlv\Enum\Tests\Stubs\Model1 $model */
        $model = new Model1();
        $model->fill($data);
        $model->save();
        $this->assertDatabaseHas('enumtest_model1s', $data);
    }

    public function test_Auto_create_new_enum_value()
    {
        /** @var \Datlv\Enum\Tests\Stubs\Model1 $model */
        $model = new Model1();
        $model->fill([
            'name' => 'Model 1',
            'author1_id' => '_cmd_create:Author 1',
        ]);
        $model->save();
        $this->assertDatabaseHas('enums', ['title' => 'Author 1', 'type' => 'model1.author']);
    }

    public function test_Can_not_auto_create_guarded_enum_attribute()
    {
        /** @var \Datlv\Enum\Tests\Stubs\Model1 $model */
        $model = new Model1();
        $model->fill([
            'name' => 'Model 2',
            'security_id' => '_cmd_create:Security 2',
        ]);
        $model->save();
        $this->assertDatabaseMissing('enums', ['title' => 'Security 2', 'type' => 'model1.security']);
    }

    public function test_Can_not_auto_create_guarded_shared_enum_attribute()
    {
        /** @var \Datlv\Enum\Tests\Stubs\Model2 $model */
        $model = new Model2();
        $model->fill([
            'name' => 'Model 2',
            'security_id' => '_cmd_create:Security 2',
        ]);
        $model->save();
        $this->assertDatabaseMissing('enums', ['title' => 'Security 2', 'type' => 'model1.security']);
    }

    public function test_Multi_enum_attributes_but_same_type()
    {
        /** @var \Datlv\Enum\Tests\Stubs\Model1 $model */
        $model = new Model1();
        $model->fill([
            'name' => 'Model 3',
            'author1_id' => '_cmd_create:Author 11',
            'author2_id' => '_cmd_create:Author 22',
        ]);
        $model->save();
        $author1 = EnumModel::where('title', 'Author 11')->where('type', 'model1.author')->exists();
        $author2 = EnumModel::where('title', 'Author 22')->where('type', 'model1.author')->exists();
        $this->assertTrue($author1 && $author2);
    }

    public function test_Create_model_with_enum_attributes()
    {
        /** @var  \Datlv\Enum\EnumModel $security */
        $security = EnumModel::create([
            'title' => 'Security 1',
            'slug' => 'security1',
            'type' => 'model1.security',
        ]);
        /** @var \Datlv\Enum\Tests\Stubs\Model1 $model */
        $model = new Model1();
        $model->fill([
            'name' => 'Model 4',
            'author1_id' => '_cmd_create:Author 111',
            'author2_id' => '_cmd_create:Author 222',
            'security_id' => $security->id,
        ]);
        $model->save();
        /** @var \Datlv\Enum\EnumModel $author1 */
        $author1 = EnumModel::where('title', 'Author 111')->where('type', 'model1.author')->first();
        /** @var \Datlv\Enum\EnumModel $author2 */
        $author2 = EnumModel::where('title', 'Author 222')->where('type', 'model1.author')->first();

        $this->assertDatabaseHas('enumtest_model1s', [
            'name' => 'Model 4',
            'author1_id' => $author1->id,
            'author2_id' => $author2->id,
            'security_id' => $security->id,
        ]);
    }

    public function test_Create_model_with_shared_enum_attributes()
    {
        /** @var \Datlv\Enum\Tests\Stubs\Model1 $model */
        $model = new Model2();
        $model->fill([
            'name' => 'Model 4',
            'author_id' => '_cmd_create:Author 5555',
        ]);
        $model->save();
        $this->assertDatabaseHas('enums', [
            'title' => 'Author 5555',
            'type' => 'model1.author',
        ]);
    }


    public function test_Create_model_with_enumable_attribute()
    {
        /** @var  \Datlv\Enum\EnumModel $multi1 */
        $multi1 = EnumModel::create([
            'title' => 'Multi1',
            'slug' => 'multi1',
            'type' => 'model1.multi',
        ]);
        /** @var  \Datlv\Enum\EnumModel $multi2 */
        $multi2 = EnumModel::create([
            'title' => 'Multi2',
            'slug' => 'multi2',
            'type' => 'model1.multi',
        ]);
        /** @var  \Datlv\Enum\EnumModel $multi3 */
        $multi3 = EnumModel::create([
            'title' => 'Multi3',
            'slug' => 'multi3',
            'type' => 'model1.multi',
        ]);
        /** @var \Datlv\Enum\Tests\Stubs\Model1 $model */
        $model = new Model1();
        $model->fill(['name' => 'Model Multi']);

        $this->assertTrue($model->loadEnumableValues() === ['multi' => []]);

        $model->save();
        $model->fillEnumable([
            'multi' => [$multi1->id, $multi2->id, $multi3->id]
        ]);

        $enumable1 = [
            'enum_id' => $multi1->id,
            'enumable_id' => $model->id,
            'enumable_type' => 'model1.multi',
        ];
        $enumable2 = [
            'enum_id' => $multi2->id,
            'enumable_id' => $model->id,
            'enumable_type' => 'model1.multi',
        ];
        $enumable3 = [
            'enum_id' => $multi3->id,
            'enumable_id' => $model->id,
            'enumable_type' => 'model1.multi',
        ];

        $this->assertDatabaseHas('enumables', $enumable1 + ['position' => 1]);
        $this->assertDatabaseHas('enumables', $enumable2 + ['position' => 2]);
        $this->assertDatabaseHas('enumables', $enumable3 + ['position' => 3]);

        // Truy xuất
        $this->assertTrue($model->getEnumableValues('multi') ===
            [
                $multi1->id => $multi1->title,
                $multi2->id => $multi2->title,
                $multi3->id => $multi3->title,
            ]
        );
        $this->assertTrue($model->loadEnumableValues() === ['multi' => [$multi1->id, $multi2->id, $multi3->id]]);

        // Cập nhật
        $model->fillEnumable([
            'multi' => [$multi3->id, $multi1->id]
        ]);
        $this->assertDatabaseMissing('enumables', $enumable2);
        $this->assertDatabaseHas('enumables', $enumable3 + ['position' => 1]);
        $this->assertDatabaseHas('enumables', $enumable1 + ['position' => 2]);
        $this->assertTrue($model->getEnumableValues('multi') ===
            [
                $multi3->id => $multi3->title,
                $multi1->id => $multi1->title,
            ]
        );
        $this->assertTrue($model->loadEnumableValues() === ['multi' => [$multi3->id, $multi1->id]]);
        $this->assertFalse($model->loadEnumableValues() === ['multi' => [$multi1->id, $multi3->id]]);

        // Cập nhật danh sách rổng
        $model->fillEnumable([
            'multi' => null
        ]);
        $this->assertDatabaseMissing('enumables', $enumable1);
        $this->assertDatabaseMissing('enumables', $enumable2);
        $this->assertDatabaseMissing('enumables', $enumable3);
        // Truy xuất
        $this->assertTrue($model->getEnumableValues('multi') === []);
        $this->assertTrue($model->loadEnumableValues() === ['multi' => []]);

        // Xóa model
        $model->fillEnumable([
            'multi' => [$multi2->id]
        ]);
        $this->assertDatabaseHas('enumables', $enumable2);
        $model->delete();
        $this->assertDatabaseMissing('enumables', $enumable2);
    }

    public function test_Shared_model_enum_attributes()
    {
        /** @var  \Datlv\Enum\EnumModel $multi */
        $multi = EnumModel::create([
            'title' => 'Multi1',
            'slug' => 'multi1',
            'type' => 'model1.multi',
        ]);
        /** @var  \Datlv\Enum\EnumModel $author */
        $author = EnumModel::create([
            'title' => 'Author 0',
            'slug' => 'author0',
            'type' => 'model1.author',
        ]);
        /** @var  \Datlv\Enum\EnumModel $security */
        $security = EnumModel::create([
            'title' => 'Security 1',
            'slug' => 'security1',
            'type' => 'model1.security',
        ]);
        /** @var  \Datlv\Enum\EnumModel $place */
        $place = EnumModel::create([
            'title' => 'Security 1',
            'slug' => 'place1',
            'type' => 'model2.place',
        ]);
        $model2 = new Model2();
        $this->assertTrue($model2->loadEnums() === [
                'multis' => [$multi->id => $multi->title],
                'authors' => [$author->id => $author->title],
                'securities' => [$security->id => $security->title],
                'places' => [$place->id => $place->title],
            ]);

        $model2->fill(['name' => 'Model2 Multi']);

        $this->assertTrue($model2->loadEnumableValues() === ['multi' => []]);

        $model2->save();
        $model2->fillEnumable([
            'multi' => [$multi->id]
        ]);

        $enumable = [
            'enum_id' => $multi->id,
            'enumable_id' => $multi->id,
            'enumable_type' => 'model1.multi',
        ];
        $this->assertDatabaseHas('enumables', $enumable + ['position' => 1]);
    }
}