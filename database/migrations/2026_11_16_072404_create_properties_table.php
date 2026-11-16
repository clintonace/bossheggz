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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('listing_type', 20)->index();
            $table->string('property_type', 30)->index();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedBigInteger('price');
            $table->string('rent_period', 20)->nullable();
            $table->string('address');
            $table->string('city')->index();
            $table->string('state')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedInteger('area_sqm')->nullable();
            $table->text('description');
            $table->json('features')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
