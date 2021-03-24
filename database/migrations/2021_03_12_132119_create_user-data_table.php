<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user-data', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('allegro_login');
            $table->string('allegro_pass');
            $table->string('token');
            $table->string('opt-5');
            $table->string('opt-6');
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
        Schema::dropIfExists('user-data');
    }
}
