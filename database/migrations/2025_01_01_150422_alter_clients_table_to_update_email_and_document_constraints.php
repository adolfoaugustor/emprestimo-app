<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('email')->nullable()->change();

            try {
                DB::statement('ALTER TABLE clients DROP INDEX clients_email_unique');
            } catch (\Exception $e) {
                // Log ou ignore o erro se o índice não existir
            }

            $table->unique('document');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('email')->nullable(false)->unique()->change();
            $table->dropUnique(['document']);
        });
    }
};
