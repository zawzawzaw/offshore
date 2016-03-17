<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->string('name');
            $table->date('incorporation_date');
            $table->decimal('price', 10, 2);
            $table->decimal('price_eu', 10, 2);
            $table->boolean('shelf');
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
        Schema::drop('companies');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
