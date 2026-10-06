public function up(): void
{
    Schema::create('tokens', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('token')->unique();
        $table->timestamps();
    });
}