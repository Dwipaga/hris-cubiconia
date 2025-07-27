<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_roles', function (Blueprint $table) {
            $table->id('role_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->onDelete('cascade');
            $table->integer('app_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_roles');
    }
};