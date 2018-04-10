<?php

use Illuminate\Database\Migrations\Migration;

class CreateEnumtestModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enumtest_model1s', function ($table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->integer('author1_id')->nullable();
            $table->integer('author2_id')->nullable();
            $table->integer('security_id')->nullable();
        });
        Schema::create('enumtest_model2s', function ($table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->integer('place_id')->nullable(); // Riêng
            $table->integer('author_id')->nullable(); // Shared từ model1
            $table->integer('security_id')->nullable(); // Shared từ model1
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('enumtest_model1s');
        Schema::drop('enumtest_model2s');
    }
}