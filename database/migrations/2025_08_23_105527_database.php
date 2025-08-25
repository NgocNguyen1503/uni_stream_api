<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('total_live')->default(0);
            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->text('google_token')->nullable();
            $table->text('device_token')->nullable();
            $table->integer('toxic_comments')->default(0);
        });

        Schema::create('lives', function (Blueprint $table) {
            $table->id();
            $table->integer('streamer_id')->nullable();
            $table->string('title', 255);
            $table->string('thumbnail', 255)->nullable();
            $table->timestamp('time_start')->nullable();
            $table->timestamp('time_end')->nullable();
            $table->string('stream_url', 255)->nullable();
            $table->string('stream_key', 255)->nullable();
            $table->string('watch_url', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('pushes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable();
            $table->string('content', 255)->nullable();
            $table->string('status')->default('waiting');
            $table->integer('user_id');
            $table->timestamps();
        });

        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('follow_id');
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('comment', 255)->nullable();
            $table->integer('live_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
