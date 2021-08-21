<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('image_name');
            $table->foreignId('role_id')->default(1)->constrained();
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')->insert([
            'name' => 'David',
            'email' => 'user@japantravel.com',
            'password' => '$2y$10$CD9AkWwgrSjz27Qe/pOxA.8/3N0e3c0RY2xowh9l8S1.PMie0N19e',
            'image_name' => 'test.png',
            'role_id' => 1,
            'created_at' => new DateTime('now'),
        ]);
        DB::table('users')->insert([
            'name' => 'David',
            'email' => 'hr@japantravel.com',
            'password' => '$2y$10$CD9AkWwgrSjz27Qe/pOxA.8/3N0e3c0RY2xowh9l8S1.PMie0N19e',
            'image_name' => 'test.png',
            'role_id' => 2,
            'created_at' => new DateTime('now'),
        ]);
        DB::table('users')->insert([
            'name' => 'David',
            'email' => 'manager@japantravel.com',
            'password' => '$2y$10$CD9AkWwgrSjz27Qe/pOxA.8/3N0e3c0RY2xowh9l8S1.PMie0N19e',
            'image_name' => 'test.png',
            'role_id' => 3,
            'created_at' => new DateTime('now'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
