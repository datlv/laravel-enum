<?php namespace Datlv\Enum\Tests;

use Datlv\Enum\EnumModel;
use Datlv\Enum\EnumValidationRule;
use Datlv\Enum\Tests\Stubs\TestCase;

/**
 * Class EnumValidatorTest
 * @package Datlv\Enum\Tests
 * @author Minh Bang
 */
class EnumValidatorTest extends TestCase
{
    public function test_wrong_enum_validation_rule_parameter()
    {
        $this->assertHTTPExceptionStatus(500, function () {
            app('validator')->make(
                ['a1' => null],
                ['a1' => new EnumValidationRule('wrong_enum_type.name')]
            )->passes();
        });

        $this->assertHTTPExceptionStatus(500, function () {
            app('validator')->make(
                ['a1' => null],
                ['a1' => new EnumValidationRule('model1.author', 'wrong_column_name')]
            )->passes();
        });
    }

    public function test_enum_validation_rule()
    {
        $enum1 = EnumModel::create([
            'title' => 'Author 1',
            'slug' => 'author-1',
            'type' => 'model1.author'
        ]);
        $rule1 = new EnumValidationRule('model1.author');
        $this->assertTrue(app('validator')->make(
            ['a1' => $enum1->id],
            ['a1' => $rule1]
        )->passes());

        $fake_id = 111;
        $this->assertFalse(app('validator')->make(
            ['a1' => $fake_id, 'a2' => null],
            ['a1' => ['required', $rule1], 'a2' => 'nullable']
        )->passes());
    }

    public function test_enum_list_validation_rule()
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
        $rule1 = new EnumValidationRule('model1.author', 'id', true);
        $rule2 = new EnumValidationRule('model1.security', 'id', true);

        $this->assertTrue(app('validator')->make(
            ['a1' => "{$enum1->id},{$enum2->id},{$enum3->id}"],
            ['a1' => $rule1]
        )->passes());
        $this->assertTrue(app('validator')->make(
            ['a2' => "{$enum3->id}  , {$enum1->id} "],
            ['a2' => $rule1]
        )->passes());
        $this->assertTrue(app('validator')->make(
            ['a3' => $enum2->id],
            ['a3' => $rule1]
        )->passes());

        $this->assertFalse(app('validator')->make(
            ['a4' => "{$enum1->id},{$enum2->id},{$enum3->id}"],
            ['a4' => $rule2]
        )->passes());
    }

    public function test_enum_title_validation_rule()
    {
        $enum1 = EnumModel::create([
            'title' => 'Author 1',
            'slug' => 'author-1',
            'type' => 'model1.author'
        ]);
        $rule1 = new EnumValidationRule('model1.author', 'title');
        $this->assertTrue(app('validator')->make(
            ['a1' => $enum1->title],
            ['a1' => $rule1]
        )->passes());

        $this->assertFalse(app('validator')->make(
            ['a1' => 'Wrong title'],
            ['a1' => $rule1]
        )->passes());
    }

    public function test_enum_title_list_validation_rule()
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
        $rule1 = new EnumValidationRule('model1.author', 'title', true);
        $rule2 = new EnumValidationRule('model1.security', 'title', true);
        $this->assertTrue(app('validator')->make(
            ['a1' => "Author 1, Author 2, {$enum3->title}"],
            ['a1' => $rule1]
        )->passes());
        $this->assertTrue(app('validator')->make(
            ['a2' => "{$enum3->title}  , {$enum1->title} "],
            ['a2' => $rule1]
        )->passes());
        $this->assertTrue(app('validator')->make(
            ['a3' => $enum2->title],
            ['a3' => $rule1]
        )->passes());

        $this->assertFalse(app('validator')->make(
            ['a4' => "{$enum1->title},{$enum2->title},{$enum3->title}"],
            ['a4' => $rule2]
        )->passes());
    }
}