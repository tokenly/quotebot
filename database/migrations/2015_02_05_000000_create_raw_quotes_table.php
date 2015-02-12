<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRawQuotesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_quotes', function(Blueprint $table)
        {
            $table->increments('id');

            $table->string('name')->index();
            $table->string('pair')->index();
            $table->bigInteger('bid')->unsigned();
            $table->bigInteger('ask')->unsigned();
            $table->bigInteger('last')->unsigned();
            $table->integer('timestamp')->index();

            $table->unique(['name','pair','timestamp']); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('raw_quotes');
    }

}
