<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace the fixed "placement" with format + page + section + position + width.
     */
    public function up(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            $table->string('format', 20)->default('inline')->after('image_path')->index();
            $table->string('page', 20)->nullable()->after('format')->index();
            $table->string('section', 40)->nullable()->after('page');
            $table->string('position', 20)->nullable()->after('section');
            $table->string('width', 10)->default('full')->after('position');
            $table->unsignedTinyInteger('display_seconds')->default(8)->after('width');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('display_seconds');
        });

        $mapping = [
            'listings_grid' => ['page' => 'listings', 'section' => 'grid', 'position' => 'inside', 'width' => 'half'],
            'home_banner' => ['page' => 'home', 'section' => 'latest', 'position' => 'after', 'width' => 'full'],
            'property_page' => ['page' => 'property', 'section' => 'about', 'position' => 'after', 'width' => 'full'],
        ];

        foreach ($mapping as $placement => $columns) {
            DB::table('adverts')->where('placement', $placement)->update($columns);
        }

        Schema::table('adverts', function (Blueprint $table) {
            $table->dropIndex(['placement']);
            $table->dropColumn('placement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            $table->string('placement', 30)->default('home_banner')->index();
        });

        DB::table('adverts')->where('page', 'listings')->update(['placement' => 'listings_grid']);
        DB::table('adverts')->where('page', 'property')->update(['placement' => 'property_page']);

        Schema::table('adverts', function (Blueprint $table) {
            $table->dropIndex(['format']);
            $table->dropIndex(['page']);
            $table->dropColumn(['format', 'page', 'section', 'position', 'width', 'display_seconds', 'sort_order']);
        });
    }
};
