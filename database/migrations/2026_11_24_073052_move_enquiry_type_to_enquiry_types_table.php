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
        Schema::table('enquiries', function (Blueprint $table) {
            $table->foreignId('enquiry_type_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        $typeIds = DB::table('enquiry_types')->pluck('id', 'slug');

        foreach ($typeIds as $slug => $id) {
            DB::table('enquiries')->where('type', $slug)->update(['enquiry_type_id' => $id]);
        }

        DB::table('enquiries')->whereNull('enquiry_type_id')->update(['enquiry_type_id' => $typeIds['general'] ?? null]);

        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->string('type', 30)->default('general')->index()->after('user_id');
        });

        foreach (DB::table('enquiry_types')->pluck('slug', 'id') as $id => $slug) {
            DB::table('enquiries')->where('enquiry_type_id', $id)->update(['type' => $slug]);
        }

        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enquiry_type_id');
        });
    }
};
