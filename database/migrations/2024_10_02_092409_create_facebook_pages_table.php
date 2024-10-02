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
        Schema::create('facebook_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Référence à l'utilisateur
            $table->string('facebook_page_id')->unique(); // ID unique de la page Facebook
            $table->string('name'); // Nom de la page
            $table->text('access_token'); // Token d'accès spécifique à la page
            $table->string('perms')->nullable(); // Permissions accordées
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_pages');
    }
};
