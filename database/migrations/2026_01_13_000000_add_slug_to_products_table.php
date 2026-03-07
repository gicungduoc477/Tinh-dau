<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('products')) {
            return; // nothing to do
        }

        if (!Schema::hasColumn('products', 'slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        // Backfill missing slugs
        $existing = DB::table('products')->pluck('slug', 'id')->toArray();
        $used = array_filter(array_values($existing));
        $rows = DB::table('products')->select('id', 'name', 'slug')->get();
        foreach ($rows as $row) {
            if (empty($row->slug)) {
                $base = Str::slug($row->name ?: 'product-'.$row->id);
                $slug = $base;
                $i = 1;
                while (in_array($slug, $used)) {
                    $slug = $base . '-' . $i++;
                }
                DB::table('products')->where('id', $row->id)->update(['slug' => $slug]);
                $used[] = $slug;
            }
        }

        // Add unique index if not exists
        // MySQL allows multiple NULLs, but we've filled slugs where missing, so safe to add unique index
        // Try to add unique index if it doesn't already exist. Use try/catch to be robust when Doctrine DBAL is not installed.
        try {
            DB::statement('ALTER TABLE products ADD UNIQUE INDEX products_slug_unique (slug)');
        } catch (\Exception $e) {
            // ignore - index may already exist or DBAL not available to introspect
        }
    }

    public function down()
    {
        if (!Schema::hasTable('products')) return;
        try {
            DB::statement('ALTER TABLE products DROP INDEX products_slug_unique');
        } catch (\Exception $e) {
            // ignore
        }

        if (Schema::hasColumn('products', 'slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};