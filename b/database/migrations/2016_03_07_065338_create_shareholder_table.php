<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShareholderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('shareholders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name_rules');
            $table->decimal('price', 10, 2);  
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
        Schema::drop('shareholders');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
