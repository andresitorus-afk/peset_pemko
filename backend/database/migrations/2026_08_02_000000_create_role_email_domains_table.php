<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_email_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_email_domains');
    }
};
