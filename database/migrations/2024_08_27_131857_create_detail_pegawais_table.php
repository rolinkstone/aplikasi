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
        Schema::create('detail_pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi');
            $table->string('alamat');
            $table->string('kode_pos');
            $table->string('pegawai_id');
            $table->string('penerbit');
            $table->json('technologies')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pegawais');
    }
};
