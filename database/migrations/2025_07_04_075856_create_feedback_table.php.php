<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_create_feedback_table.php
public function up()
{
    Schema::create('feedback', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // consumer
        $table->foreignId('product_id')->constrained()->onDelete('cascade'); // reviewed product
        $table->tinyInteger('rating')->unsigned(); // 1 to 5
        $table->text('comment');
        $table->text('reply')->nullable(); // farmer response
        $table->boolean('approved')->default(false); // moderation
        $table->timestamps();

        // One feedback per product per user
        $table->unique(['user_id', 'product_id']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
