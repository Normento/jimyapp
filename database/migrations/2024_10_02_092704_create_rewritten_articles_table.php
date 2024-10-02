<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rewritten_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->onDelete('cascade'); // Référence à la ligue
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Référence à l'utilisateur
            $table->string('title');
            $table->text('description');
            $table->text('content');
            $table->string('url')->nullable();
            $table->string('image_url')->nullable();
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewritten_articles');
    }
};
