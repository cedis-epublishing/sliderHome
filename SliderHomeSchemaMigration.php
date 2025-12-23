<?php

/**
 * @file classes/migration/SliderHomeSchemaMigration.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SliderHomeSchemaMigration
 * @brief Describe database table structures.
 */

namespace APP\plugins\generic\sliderHome;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as Schema;
use PKP\config\Config;

class SliderHomeSchemaMigration extends Migration {
    /**
     * Run the migrations.
     * @return void
     */
    public function up() {
        echo "Running SliderHomeSchemaMigration...\n";
        // we need to update tables if at least one doesn't exist
        if (!Schema::hasTable('slider_settings') || !Schema::hasTable('slider')) {

            // if there is no slider_settings table there should be no slider files in the public folder either
            // This script is not context-aware PublicFileManager cannot be used and public files must be handled globally
            // search for slider images and print to stdout to delete them manually
            $publicFilePath = Config::getVar('files', 'public_files_dir') . '/journals';
            $sliderFiles = glob($publicFilePath . '/*/slider_image_*');
            if ($sliderFiles) {
                echo "The following slider image files were found and should be deleted manually:\n";
                foreach ($sliderFiles as $file) {
                    echo $file . "\n";
                }
            }

            // remove old slider data and tables
            if (Schema::hasTable('slider')) {
                echo "Removing old slider data and tables...\n";
                Schema::drop('slider');
            }
            if (Schema::hasTable('slider_settings')) {
                Schema::drop('slider_settings');
            }

            echo "Creating new slider tables...\n";
            // main slider table
            Schema::create('slider', function (Blueprint $table) {
                $table->increments('slider_content_id');
                $table->smallInteger('context_id')->default(0);
                $table->smallInteger('sequence')->default(0);
                $table->boolean('show_content')->default(false);
            });

            // slider content settings
            Schema::create('slider_settings', function (Blueprint $table) {
                $table->bigInteger('slider_content_id')->default(0);
                $table->string('locale', 14)->default('');
                $table->string('setting_name', 255)->default('');
                $table->longText('setting_value')->nullable();
                $table->string('setting_type', 6)->default('string')->comment('(bool|int|float|string|object)');
                $table->index(['slider_content_id'], 'slider_settings_slider_id');
                $table->unique(['slider_content_id', 'locale', 'setting_name'], 'slider_settings_pkey');
            });

            // Set default values for maxHeight, speed, delay, ... in journal settings if not already set
            DB::table('journal_settings')
                ->where('setting_name', 'maxHeight')
                ->whereNull('setting_value')
                ->update(['setting_value' => '100']);
            DB::table('journal_settings')
                ->where('setting_name', 'speed')
                ->whereNull('setting_value')
                ->update(['setting_value' => '2000']);
            DB::table('journal_settings')
                ->where('setting_name', 'delay')
                ->whereNull('setting_value')
                ->update(['setting_value' => '2000']);
            DB::table('journal_settings')
                ->where('setting_name', 'slideEffect')
                ->whereNull('setting_value')
                ->update(['setting_value' => '']);

        } else {
            echo "SliderHomeSchemaMigration: 'slider_settings' table already exists, skipping migration.\n";
        }
    }
}