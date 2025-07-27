<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id('menu_id');
            $table->string('menu_name', 100)->nullable();
            $table->string('menu_url', 255)->nullable();
            $table->integer('menu_parent')->default(0);
            $table->string('menu_type', 100)->nullable();
            $table->integer('menu_order')->nullable();
            $table->string('menu_icon', 50)->nullable();
            $table->string('menu_description', 255)->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};