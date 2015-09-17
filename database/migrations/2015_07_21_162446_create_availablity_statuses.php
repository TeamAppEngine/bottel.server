<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAvailablityStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('availability_statuses', function(Blueprint $table) {
            $table->increments('id');
            $table->string('statuses',50);
        });

        Schema::create('user_availability_statuses',function(Blueprint $table){
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('status_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('status_id')->references('id')->on('availability_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_availability_statuses');
        Schema::drop('availability_statuses');
    }
}
