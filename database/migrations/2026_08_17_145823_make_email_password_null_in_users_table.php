<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        Schema::table('users', function(Blueprint $table) {

            // Remove unique index from email
            $table->dropUnique('users_email_unique');

            // Make email nullable
            $table->string('email')->nullable()->change();

            // Make password nullable
            $table->string('password')->nullable()->change();
        });
    }

    public function down() : void
    {
        // Replace blank / NULL emails with unique placeholders so the unique index can be restored.
        $seenEmails = [];

        DB::table('users')
            ->select('id', 'email')
            ->orderBy('id')
            ->get()
            ->each(function($user) use (&$seenEmails) {
                $email = $user->email;

                if (blank($email) || in_array($email, $seenEmails, true))
                {
                    $email = "user-{$user->id}@example.local";
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['email' => $email]);
                }

                $seenEmails[] = $email;
            });

        DB::table('users')
            ->whereNull('password')
            ->update([
                'password' => '',
            ]);

        Schema::table('users', function(Blueprint $table) {

            // Make fields NOT NULL
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();

            // Add unique index again
            $table->unique('email');
        });
    }
};
