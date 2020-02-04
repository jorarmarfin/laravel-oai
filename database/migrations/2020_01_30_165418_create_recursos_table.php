<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecursosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recursos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('dspace_id')->nullable();
            $table->string('title')->nullable();
            $table->json('contributor')->nullable();
            $table->json('communities')->nullable();
            $table->json('collections')->nullable();
            $table->string('subject')->nullable();
            $table->mediumText('description')->nullable();
            $table->date('date')->nullable();
            $table->year('year')->nullable();
            $table->string('identifier')->nullable();
            $table->string('language')->nullable();
            $table->string('rights')->nullable();
            $table->string('format')->nullable();
            $table->string('publisher')->nullable();
            $table->boolean('procesar')->nullable();
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
        Schema::dropIfExists('recursos');
    }
}
