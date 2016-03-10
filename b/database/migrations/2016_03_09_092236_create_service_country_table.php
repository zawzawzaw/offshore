<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceCountryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
         Schema::create('service_country', function (Blueprint $table) {            
            $table->integer('service_id')->unsigned();        
            $table->integer('country_id')->unsigned();
            $table->decimal('price', 10, 2);            
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('service_country');
    }
}
