<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Sliders extends Migration
{
    public function up()
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->integer('width')->unsigned();
            $table->integer('height')->unsigned();
            $table->string('backgroundColor', 7);
            $table->boolean('autoSliding')->nullable()->default(null);
            $table->boolean('nextPrevStatus')->nullable()->default(null);
            $table->boolean('dotStatus')->nullable()->default(null);
            $table->boolean('titleStatus')->nullable()->default(null);
            $table->integer('slidingSpeed')->unsigned();
            $table->boolean('status');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sliders');
    }
}
