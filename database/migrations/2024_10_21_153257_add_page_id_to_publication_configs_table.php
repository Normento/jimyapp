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
        Schema::table('publication_configs', function (Blueprint $table) {
            $table->foreignId('page_id')->references('id')->on('facebook_pages')->constrained()->onDelete('cascade'); // Référence une page

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publication_configs', function (Blueprint $table) {
            //
        });
    }
};
