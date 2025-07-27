<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->onDelete('set null');
            $table->string('email', 100);
            $table->string('firstname', 50)->nullable();
            $table->string('lastname', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('photo', 30)->nullable();
            $table->string('password', 60); // Changed to 60 for Laravel bcrypt
            $table->string('dokumen', 100)->nullable();
            $table->text('token')->nullable();
            $table->timestamp('created')->nullable()->useCurrent();
            $table->string('oss_id', 50)->nullable();
            $table->integer('status')->default(1);
            $table->string('username', 100)->nullable();
            $table->text('g-recaptcha-response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};