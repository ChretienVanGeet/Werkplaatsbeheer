<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('instructor_assignments', function (Blueprint $table) {
            $table->unsignedTinyInteger('load_percentage')
                ->default(100)
                ->after('activity_id');
        });

        DB::table('resources')->update(['instructor_capacity' => 100]);
        DB::table('instructor_assignments')->update(['load_percentage' => 100]);

        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn('resource_capacity');
        });

        Schema::create('instructor_resource', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['instructor_id', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructor_assignments', function (Blueprint $table) {
            $table->dropColumn('load_percentage');
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->unsignedSmallInteger('resource_capacity')->default(1);
        });

        Schema::dropIfExists('instructor_resource');
    }
};
