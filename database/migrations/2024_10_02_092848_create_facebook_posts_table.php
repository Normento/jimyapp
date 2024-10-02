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
        Schema::create('facebook_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rewritten_article_id')->constrained()->onDelete('cascade'); // Référence à l'article réécrit
            $table->string('facebook_post_id')->unique(); // ID de la publication Facebook
            $table->enum('status', ['posted', 'failed'])->default('posted');
            $table->timestamp('scheduled_at')->nullable(); // Date de publication programmée
            $table->timestamp('posted_at')->nullable(); // Date de publication réelle
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_posts');
    }
};
