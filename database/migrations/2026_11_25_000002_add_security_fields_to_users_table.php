<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('activo');
            $table->unsignedInteger('failed_login_attempts')->default(0)->after('must_change_password');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->boolean('mfa_enabled')->default(true)->after('locked_until');
            $table->string('mfa_code', 12)->nullable()->after('mfa_enabled');
            $table->timestamp('mfa_expires_at')->nullable()->after('mfa_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'must_change_password',
                'failed_login_attempts',
                'locked_until',
                'mfa_enabled',
                'mfa_code',
                'mfa_expires_at',
            ]);
        });
    }
};
