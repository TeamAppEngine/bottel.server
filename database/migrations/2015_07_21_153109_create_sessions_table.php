<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function(Blueprint $table) {
            $table->unsignedInteger('user_a_id');
            $table->unsignedInteger('user_b_id');
            $table->integer('duration'); //in minutes
            $table->timestamps();
            $table->foreign('user_a_id')->references('id')->on('users');
            $table->foreign('user_b_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sessions');
    }
}
