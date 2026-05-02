<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Complaint details
            $table->string('category')
                ->comment('تأخير، سلوك، تسعير، أمان، نظافة، أخرى');
            $table->string('priority')->default('medium');
            $table->string('subject');
            $table->text('description');
            $table->json('attachments')->nullable();
            
            // Status tracking
            $table->string('status')->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->text('resolution')->nullable();
            
            // Response time tracking
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->integer('response_time_minutes')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
