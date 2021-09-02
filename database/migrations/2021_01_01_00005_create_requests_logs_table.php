<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateRequestsLogsTable
 */
class CreateRequestsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'requests_logs',
            function (Blueprint $table) {
                $table->id();
                $table->string('message');

                $table->unsignedBigInteger('request_id');
                $table->foreign('request_id')
                    ->references('id')
                    ->on('requests');

                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users');

                $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('requests_logs');
    }
}
