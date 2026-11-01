<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('account_owner_id')->nullable()->after('user_type')->constrained('users')->restrictOnDelete();
            $table->boolean('is_active')->default(true)->after('account_owner_id');
            $table->boolean('must_change_password')->default(false)->after('is_active');
            $table->index('account_owner_id');
        });

        Schema::create('subuser_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('create_clients')->default(true);
            $table->boolean('create_charges')->default(true);
            $table->boolean('record_payments')->default(true);
            $table->boolean('change_due_dates')->default(true);
            $table->timestamps();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('account_owner_id')->nullable()->after('id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->after('account_owner_id')->constrained('users')->nullOnDelete();
            $table->index('account_owner_id');
        });

        Schema::table('charges', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('client_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->foreignId('account_owner_id')->nullable()->after('id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->after('account_owner_id')->constrained('users')->nullOnDelete();
            $table->index(['account_owner_id', 'transaction_date']);
        });

        Schema::create('installment_due_date_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_id')->constrained()->cascadeOnDelete();
            $table->date('old_due_date');
            $table->date('new_due_date');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('client_account_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_account_owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('to_account_owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('transferred_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_account_transfers');
        Schema::dropIfExists('installment_due_date_changes');

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropForeign(['account_owner_id']);
            $table->dropForeign(['created_by_user_id']);
            $table->dropIndex(['account_owner_id', 'transaction_date']);
            $table->dropColumn(['account_owner_id', 'created_by_user_id']);
        });

        Schema::table('charges', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn('created_by_user_id');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['account_owner_id']);
            $table->dropForeign(['created_by_user_id']);
            $table->dropIndex(['account_owner_id']);
            $table->dropColumn(['account_owner_id', 'created_by_user_id']);
        });

        Schema::dropIfExists('subuser_permissions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['account_owner_id']);
            $table->dropIndex(['account_owner_id']);
            $table->dropColumn(['account_owner_id', 'is_active', 'must_change_password']);
        });
    }
};
