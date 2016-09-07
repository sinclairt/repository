<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDummyRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dummy_relations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dummy_id', false, true);
            $table->foreign('dummy_id')->references('id')->on('dummies')->onDelete('cascade');
            $table->string('name');
            $table->string('detail');
            $table->softDeletes();
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
        Schema::drop('dummy_relations');
    }
}
