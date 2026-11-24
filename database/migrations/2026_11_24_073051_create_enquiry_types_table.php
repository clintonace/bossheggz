<?php

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
        Schema::create('enquiry_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->string('description', 200)->nullable();
            $table->foreignId('whatsapp_number_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Types the website's own forms rely on. They can be renamed but not removed.
        $systemTypes = [
            'general' => 'General enquiry',
            'property' => 'Property enquiry',
            'investment' => 'Investment enquiry',
            'land_banking' => 'Land banking enquiry',
            'consultation' => 'Consultation request',
            'list_property' => 'List my property',
            'shortlet_booking' => 'Shortlet booking',
        ];

        DB::table('enquiry_types')->insert(collect($systemTypes)->values()->map(fn (string $name, int $index) => [
            'name' => $name,
            'slug' => array_keys($systemTypes)[$index],
            'is_active' => true,
            'is_system' => true,
            'sort_order' => $index,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiry_types');
    }
};
