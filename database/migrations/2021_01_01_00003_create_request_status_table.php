<?php

namespace database\migrations;

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
        Schema::create(
            'requests_status',
            function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('description');
                $table->string('color_code')->default("#E7E7E7");
            }
        );

        DB::table('requests_status')->insert(
            [
            'name' => 'Open',
            'description' => 'The request is waiting for an HR staff to review it.'
            ]
        );
        DB::table('requests_status')->insert(
            [
            'name' => 'Hr Reviewed',
            'description' => 'A manager will process your request.',
            'color_code' => '#A1F1FF'
            ]
        );
        DB::table('requests_status')->insert(
            [
            'name' => 'Processed',
            'description' => 'The request is complete.',
            'color_code' => '#9FA8FF'
            ]
        );
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
