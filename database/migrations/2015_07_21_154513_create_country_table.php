<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name',50);
            $table->string('iso_code',2);
            $table->string('un_code',3);
            $table->string('img_url', 200)->nullable();
        });

        Schema::table('users', function(Blueprint $table){
            $table->unsignedInteger('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('users',function(Blueprint $table) {
            $table->dropForeign('users_country_id_foreign');
            $table->dropColumn('country_id');
        });
        Schema::drop('countries');
    }
}
