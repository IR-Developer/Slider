<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Posts extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('faTitle', 150);
            $table->string('faNickname', 300);
            $table->string('faSummary', 500);
            $table->text('faContent')->nullable()->default(null);
            $table->boolean('view_status')->default(false);
            $table->tinyInteger('date_status')->unsigned()->default(0);
            $table->integer('since_time')->unsigned()->nullable()->default(null);
            $table->integer('until_time')->unsigned()->nullable()->default(null);
            $table->boolean('comments_status')->default(false);
            $table->boolean('comments_reply_status')->default(false);
            $table->bigInteger('view_count')->unsigned()->default(0);
            $table->string('iconExtension', 4)->nullable()->default(null);
            $table->boolean('top_status')->default(false);
            $table->boolean('special_status')->default(false);
            $table->boolean('slider_status')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
