<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Class InsertDataFixtures
 */
class InsertDataFixtures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * Users Roles
         */
        DB::table('roles')->insert(['name' => 'User']);
        DB::table('roles')->insert(['name' => 'HR']);
        DB::table('roles')->insert(['name' => 'Manager']);

        /*
         * Users
         */
        DB::table('users')->insert(
            [
                'name' => 'David User',
                'email' => 'user@japantravel.com',
                'password' => '$2y$10$CD9AkWwgrSjz27Qe/pOxA.8/3N0e3c0RY2xowh9l8S1.PMie0N19e',
                'image_name' => 'test.png',
                'role_id' => 1,
                'created_at' => new DateTime('now'),
            ]
        );
        DB::table('users')->insert(
            [
                'name' => 'Franck HR',
                'email' => 'hr@japantravel.com',
                'password' => '$2y$10$CD9AkWwgrSjz27Qe/pOxA.8/3N0e3c0RY2xowh9l8S1.PMie0N19e',
                'image_name' => 'test.png',
                'role_id' => 2,
                'created_at' => new DateTime('now'),
            ]
        );
        DB::table('users')->insert(
            [
                'name' => 'John Manager',
                'email' => 'manager@japantravel.com',
                'password' => '$2y$10$CD9AkWwgrSjz27Qe/pOxA.8/3N0e3c0RY2xowh9l8S1.PMie0N19e',
                'image_name' => 'test.png',
                'role_id' => 3,
                'created_at' => new DateTime('now'),
            ]
        );

        /*
         * Requests Statuses
         */
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

        /*
         * Requests
         */
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
        DB::table('requests')->insert(
            [
                'subject' => 'Problem Reviewed',
                'description' => 'This is problem reviewed description.',
                'user_id' => 1,
                'status_id' => 2,
                'created_at' => new DateTime('now'),
                'updated_at' => new DateTime('now'),
            ]
        );

        /*
         * Requests Logs
         */
        DB::table('requests_logs')->insert(
            [
            'message' => 'Request created by user',
            'request_id' => 1,
            'user_id' => 1,
            ]
        );
        DB::table('requests_logs')->insert(
            [
                'message' => 'Request created by user',
                'request_id' => 2,
                'user_id' => 1,
            ]
        );
        DB::table('requests_logs')->insert(
            [
                'message' => 'Request status updated to "HR Reviewed"',
                'request_id' => 2,
                'user_id' => 2,
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
        Schema::disableForeignKeyConstraints();
        DB::table('requests_logs')->truncate();
        DB::table('requests')->truncate();
        DB::table('requests_status')->truncate();
        DB::table('users')->truncate();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}
