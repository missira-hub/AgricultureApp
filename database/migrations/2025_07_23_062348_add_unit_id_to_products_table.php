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
    Schema::table('products', function (Blueprint $table) {
        $table->unsignedBigInteger('unit_id')->nullable()->after('category_id');

        // Add foreign key constraint if you want (optional)
        $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropForeign(['unit_id']);
        $table->dropColumn('unit_id');
    });
}

};
