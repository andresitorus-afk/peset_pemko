<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori_aset', function (Blueprint $table) {
            $table->string('kode_kategori')->nullable()->after('kode_kib')->comment('kode lengkap dari XLS, misal 1.3.1.01.01.01.001');
            $table->foreignUuid('parent_id')->nullable()->after('kode_kategori')->constrained('kategori_aset')->nullOnDelete();
            $table->boolean('is_leaf')->default(false)->after('parent_id')->comment('true jika ini sub-kategori paling detail (bisa dipilih untuk aset)');
        });
    }

    public function down(): void
    {
        Schema::table('kategori_aset', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['kode_kategori', 'parent_id', 'is_leaf']);
        });
    }
};
