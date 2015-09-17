<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('levels', function(Blueprint $table) {
            $table->increments('id');
            $table->string('title',50);
            $table->string('img_url', 200);
            $table->text('achievement_rule',3);
        });

        Schema::create('user_levels',function(Blueprint $table){
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('level_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('level_id')->references('id')->on('levels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_levels');
        Schema::drop('levels');
    }
}
