<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sidebar "light"/"dark" used to be fixed styles; sidebars now follow the
     * light/dark mode by default ("auto"). Move settings saved under the old
     * options to the new default. Other saved settings are left untouched.
     */
    public function up(): void
    {
        DB::table('site_settings')->orderBy('id')->each(function (object $row): void {
            $settings = json_decode($row->settings, true) ?: [];

            if (array_key_exists('sidebar', $settings) && $settings['sidebar'] !== 'auto') {
                $settings['sidebar'] = 'auto';
                DB::table('site_settings')->where('id', $row->id)->update(['settings' => json_encode($settings)]);
            }
        });
    }

    /**
     * The previous fixed style cannot be recovered; nothing to undo.
     */
    public function down(): void
    {
        //
    }
};
