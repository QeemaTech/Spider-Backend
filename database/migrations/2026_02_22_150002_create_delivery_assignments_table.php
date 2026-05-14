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
        Schema::create('delivery_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->string('status', 30)->default('assigned'); // assigned, picking_up, in_transit, delivered
            $table->decimal('total_km', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_assignment_pickups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_order_id')->constrained('vendor_orders')->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence');
            $table->timestamp('picked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_assignment_pickups');
        Schema::dropIfExists('delivery_assignments');
    }
};
