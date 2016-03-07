<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInformationServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('information_services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');            
            $table->integer('company_type_id')->unsigned();
            $table->foreign('company_type_id')->references('id')->on('company_types');  
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');        
        Schema::drop('information_services');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');        
    }
}
