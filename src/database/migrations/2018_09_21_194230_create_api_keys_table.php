<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('app_name', 32);
            $table->char('key', 64);
            $table->char('origin_url', 128);
            $table->longText('access_map')->nullable();
            $table->datetime('expires_at')->default('9999-12-31');
            $table->timestamp('last_accessed_at')->nullable();
            $table->nullableTimestamps();

            $table->unique('key');
            $table->unique('app_name');
            $table->index('origin_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_keys');
    }
}
