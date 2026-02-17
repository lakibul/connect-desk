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
            $table->string('twilio_account_sid')->nullable()->after('whatsapp_phone_number_id');
            $table->text('twilio_auth_token')->nullable()->after('twilio_account_sid');
            $table->string('twilio_whatsapp_from')->nullable()->after('twilio_auth_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['twilio_account_sid', 'twilio_auth_token', 'twilio_whatsapp_from']);
        });
    }
};
