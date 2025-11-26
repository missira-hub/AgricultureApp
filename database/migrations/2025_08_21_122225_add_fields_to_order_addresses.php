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
    Schema::table('order_addresses', function (Blueprint $table) {
        $table->foreignId('order_id')->constrained()->onDelete('cascade');
        $table->string('full_address');
        $table->string('city')->nullable();
        $table->string('postal_code')->nullable();
        $table->string('phone')->nullable();
    });
}

public function down()
{
    Schema::table('order_addresses', function (Blueprint $table) {
        $table->dropForeign(['order_id']);
        $table->dropColumn(['order_id', 'full_address', 'city', 'postal_code', 'phone']);
    });
}
};
