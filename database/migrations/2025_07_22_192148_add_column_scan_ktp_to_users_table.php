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
        Schema::table('users', function (Blueprint $table) {
            $table->string('scan_ktp')->nullable()->after('dokumen_kontrak');
            $table->string('nama_bank')->nullable()->after('scan_ktp');
            $table->string('nomor_rekening')->nullable()->after('nama_bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('scan_ktp');
            $table->dropColumn('nama_bank');
            $table->dropColumn('nomor_rekening');
        });
    }
};
