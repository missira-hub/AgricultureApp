<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // migration file
public function up()
{
    if (!Schema::hasColumn('conversations', 'product_id')) {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
        });
    }
}

public function down()
{
    Schema::table('conversations', function (Blueprint $table) {
        $table->dropForeign(['product_id']);
        $table->dropColumn('product_id');
    });
}
};
