<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('auto_reply_message');
            $table->string('bank_account_name')->nullable()->after('bank_name');
            $table->string('bank_account_number')->nullable()->after('bank_account_name');
            $table->string('bank_iban')->nullable()->after('bank_account_number');
            $table->string('bank_swift_code')->nullable()->after('bank_iban');
            $table->text('bank_address')->nullable()->after('bank_swift_code');
            $table->text('bank_payment_instructions')->nullable()->after('bank_address');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'bank_iban',
                'bank_swift_code',
                'bank_address',
                'bank_payment_instructions',
            ]);
        });
    }
};