<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Slides extends Migration
{
    public function up()
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('href', 2083);
            $table->boolean('blank')->nullable()->default(null);
            $table->string('slideExtension', 4);
            $table->boolean('status')->default(true);
            $table->foreignId('slider_id')->references('id')->on('sliders')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('slides');
    }
}
