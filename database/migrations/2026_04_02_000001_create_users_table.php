<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('gambar')->nullable();
            $table->enum('role', ['tim_teknis', 'opd', 'super_admin', 'admin_helpdesk', 'pimpinan']);
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
