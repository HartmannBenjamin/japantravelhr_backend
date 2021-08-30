<?php

namespace database\migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'requests',
            function (Blueprint $table) {
                $table->id();
                $table->string('subject');
                $table->text('description');

                $table->unsignedBigInteger('status_id')->default(1);
                $table->foreign('status_id')
                    ->references('id')
                    ->on('requests_status');

                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users');

                $table->timestamps();
            }
        );

        DB::table('requests')->insert(
            [
            'subject' => 'Problem',
            'description' => 'This is problem description.',
            'user_id' => 1,
            'status_id' => 1,
            'created_at' => new DateTime('now'),
            'updated_at' => new DateTime('now'),
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
        Schema::dropIfExists('requests');
    }
}
