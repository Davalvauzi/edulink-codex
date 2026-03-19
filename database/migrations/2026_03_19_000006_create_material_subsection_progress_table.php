<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_subsection_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_subsection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['material_subsection_id', 'user_id'], 'material_subsection_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_subsection_progress');
    }
};
