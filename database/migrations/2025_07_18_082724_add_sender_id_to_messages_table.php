<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('messages', function (Blueprint $table) {
        $table->unsignedBigInteger('sender_id')->after('id');

        // Optionally, if you have a users table and want to enforce foreign key constraint:
        $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('messages', function (Blueprint $table) {
        $table->dropForeign(['sender_id']);
        $table->dropColumn('sender_id');
    });
}

};
