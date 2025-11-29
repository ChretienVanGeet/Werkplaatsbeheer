<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->renameColumn('contact', 'comments');

            $table->after('name', function (Blueprint $table) {
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('city')->nullable();
            });

        });

        Schema::table('companies', function (Blueprint $table) {
            $table->renameColumn('contact', 'comments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->renameColumn('comments', 'contact');

            $table->dropColumn('phone');
            $table->dropColumn('email');
            $table->dropColumn('city');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->renameColumn('comments', 'contact');
        });
    }
};
