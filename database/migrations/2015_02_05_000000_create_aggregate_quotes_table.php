<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAggregateQuotesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aggregate_quotes', function(Blueprint $table)
        {
            $table->increments('id');

            $table->string('name')->index();
            $table->string('pair')->index();
            $table->bigInteger('bid_low')->unsigned();
            $table->bigInteger('bid_avg')->unsigned();
            $table->bigInteger('bid_high')->unsigned();
            $table->bigInteger('last_low')->unsigned();
            $table->bigInteger('last_avg')->unsigned();
            $table->bigInteger('last_high')->unsigned();
            $table->bigInteger('ask_low')->unsigned();
            $table->bigInteger('ask_avg')->unsigned();
            $table->bigInteger('ask_high')->unsigned();
            $table->integer('start_timestamp')->index();
            $table->integer('end_timestamp');

            $table->unique(['name','pair','start_timestamp']); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('aggregate_quotes');
    }

}
