<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRequestStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests_status', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color_code')->default("#FCFCFC");
        });

        DB::table('requests_status')->insert(['name' => 'open']);
        DB::table('requests_status')->insert(['name' => 'processed', 'color_code' => '#9FA8FF']);
        DB::table('requests_status')->insert(['name' => 'hr_reviewed', 'color_code' => '#A1F1FF']);
        DB::table('requests_status')->insert(['name' => 'complete', 'color_code' => '#85F37C']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requests_status');
    }
}
