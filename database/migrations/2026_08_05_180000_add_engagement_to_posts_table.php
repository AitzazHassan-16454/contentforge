<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('excerpt');
            $table->string('cover_image_alt')->nullable()->after('cover_image');
            $table->unsignedBigInteger('view_count')->default(0)->after('status');
            $table->timestamp('scheduled_at')->nullable()->after('published_at');

            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['scheduled_at']);
            $table->dropColumn(['cover_image', 'cover_image_alt', 'view_count', 'scheduled_at']);
        });
    }
};
