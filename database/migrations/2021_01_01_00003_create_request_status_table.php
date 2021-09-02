<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateRequestStatusTable
 */
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
