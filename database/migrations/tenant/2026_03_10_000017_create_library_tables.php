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
        // Book Categories
        Schema::create('book_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('book_categories')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Books
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('isbn', 20)->nullable();
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('edition')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('language', 50)->default('English');
            $table->integer('pages')->nullable();
            $table->string('shelf_location', 100)->nullable();
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->decimal('price', 10, 2)->nullable();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('grade_level_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('book_type', ['reference', 'textbook', 'fiction', 'non_fiction', 'periodical', 'other'])->default('other');
            $table->boolean('is_loanable')->default(true);
            $table->integer('loan_duration_days')->default(14);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'title']);
            $table->index(['school_id', 'isbn']);
        });

        // Book Copies
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('copy_number', 50);
            $table->string('barcode', 100)->nullable();
            $table->enum('condition', ['new', 'good', 'fair', 'poor', 'damaged', 'lost'])->default('good');
            $table->enum('status', ['available', 'loaned', 'reserved', 'maintenance', 'lost', 'retired'])->default('available');
            $table->date('acquisition_date')->nullable();
            $table->string('acquisition_source', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['book_id', 'copy_number']);
            $table->index('barcode');
        });

        // Library Cards
        Schema::create('library_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->morphs('cardable'); // student or teacher - morphs already creates an index
            $table->string('card_number')->unique();
            $table->string('barcode', 100)->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->integer('max_books')->default(3);
            $table->integer('current_books')->default(0);
            $table->decimal('outstanding_fines', 10, 2)->default(0);
            $table->enum('status', ['active', 'suspended', 'expired', 'lost'])->default('active');
            $table->timestamps();
        });

        // Book Loans
        Schema::create('book_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_copy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_card_id')->constrained()->cascadeOnDelete();
            $table->date('loan_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['active', 'returned', 'overdue', 'lost'])->default('active');
            $table->string('condition_at_loan', 50);
            $table->string('condition_at_return', 50)->nullable();
            $table->integer('renewals')->default(0);
            $table->integer('max_renewals')->default(2);
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->boolean('fine_paid')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('loaned_by')->constrained('users');
            $table->foreignId('returned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['library_card_id', 'status']);
            $table->index(['due_date', 'status']);
        });

        // Book Reservations
        Schema::create('book_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_card_id')->constrained()->cascadeOnDelete();
            $table->timestamp('reserved_at');
            $table->date('expiry_date');
            $table->enum('status', ['pending', 'available', 'collected', 'cancelled', 'expired'])->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();

            $table->index(['book_id', 'status']);
        });

        // Library Fines
        Schema::create('library_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_card_id')->constrained()->cascadeOnDelete();
            $table->string('fine_type', 50); // overdue, lost, damage
            $table->decimal('amount', 10, 2);
            $table->integer('overdue_days')->nullable();
            $table->enum('status', ['pending', 'paid', 'waived'])->default('pending');
            $table->foreignId('paid_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // E-Resources
        Schema::create('e_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->string('resource_type', 50); // ebook, audio, video, document
            $table->string('file_path')->nullable();
            $table->string('url')->nullable();
            $table->string('file_format', 20)->nullable();
            $table->integer('file_size')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('grade_level_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('views_count')->default(0);
            $table->integer('downloads_count')->default(0);
            $table->boolean('is_downloadable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Library Settings
        Schema::create('library_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_settings');
        Schema::dropIfExists('e_resources');
        Schema::dropIfExists('library_fines');
        Schema::dropIfExists('book_reservations');
        Schema::dropIfExists('book_loans');
        Schema::dropIfExists('library_cards');
        Schema::dropIfExists('book_copies');
        Schema::dropIfExists('books');
        Schema::dropIfExists('book_categories');
    }
};
