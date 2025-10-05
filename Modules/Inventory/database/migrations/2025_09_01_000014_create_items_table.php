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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('items')->nullOnDelete();

            // Item Information
            $table->string('item_number')->unique(); // رقم الصنف
            $table->string('code'); // كود الصنف
            $table->string('catalog_number')->nullable(); // رقم الكتالوج
            $table->string('name'); // اسم الصنف
            $table->string('name_ar')->nullable(); // الاسم بالعربية
            $table->text('description')->nullable(); // الوصف
            $table->text('description_ar')->nullable(); // الوصف بالعربية
            $table->string('model')->nullable(); // موديل
            // $table->string('unit_name')->nullable(); take it from (unit_id) // الوحدة (text field)

            // Item Type
            $table->enum('type', ['product', 'service', 'material', 'raw_material']); // نوع الصنف

            // Stock Information
            $table->decimal('quantity', 12, 2)->default(0); // الكمية
            $table->decimal('balance', 12, 2)->default(0); // الرصيد
            $table->decimal('minimum_limit', 12, 2)->default(0); // الحد الأدنى
            $table->decimal('maximum_limit', 12, 2)->default(0); // الحد الأقصى
            $table->decimal('reorder_limit', 12, 2)->default(0); // حد إعادة الطلب
            $table->decimal('max_reorder_limit', 12, 2)->default(0); // أغلى حد لإعادة الطلب

            // Purchase Prices (أسعار الشراء)
            $table->decimal('cost_price', 12, 2)->nullable(); // سعر التكلفة
            $table->decimal('purchase_price', 12, 2)->nullable(); // سعر الشراء
            $table->decimal('first_purchase_price', 12, 2)->nullable(); // سعر الشراء الأول
            $table->decimal('second_purchase_price', 12, 2)->nullable(); // سعر الشراء الثاني
            $table->decimal('third_purchase_price', 12, 2)->nullable(); // سعر الشراء الثالث
            $table->decimal('purchase_discount_rate', 5, 2)->nullable(); // نسبة الخصم عند الشراء
            $table->boolean('purchase_prices_include_vat')->default(false); // أسعار الشراء المذكورة تشمل الضريبة المضافة

            // Sale Prices (أسعار البيع)
            $table->decimal('sale_price', 12, 2)->nullable(); // سعر البيع
            $table->decimal('minimum_sale_price', 12, 2)->nullable(); // الحد الأدنى لسعر البيع
            $table->decimal('first_sale_price', 12, 2)->nullable(); // سعر البيع الأول
            $table->decimal('second_sale_price', 12, 2)->nullable(); // سعر البيع الثاني
            $table->decimal('third_sale_price', 12, 2)->nullable(); // سعر البيع الثالث
            $table->decimal('sale_discount_rate', 5, 2)->nullable(); // نسبة الخصم عند البيع
            $table->decimal('maximum_sale_discount_rate', 5, 2)->nullable(); // أعلى نسبة خصم عند البيع
            $table->decimal('minimum_allowed_sale_price', 12, 2)->nullable(); // أقل سعر بيع مسموح به
            $table->boolean('sale_prices_include_vat')->default(false); // أسعار البيع المذكورة تشمل الضريبة المضافة

            // VAT Information (معلومات الضريبة)
            $table->boolean('item_subject_to_vat')->default(false); // يخضع الصنف لضريبة المضافة

            // Additional Information
            $table->text('notes')->nullable(); // ملاحظات

            // Barcode Information (معلومات الباركود)
            $table->string('barcode')->nullable(); // الباركود
            $table->string('barcode_type')->nullable(); // نوع الباركود (EAN13, EAN8, UPC, CODE128, etc.)

            // Product Information (معلومات المنتج)
            $table->date('expiry_date')->nullable(); // تاريخ الانتهاء
            $table->string('image')->nullable(); // الصورة
            $table->string('color')->nullable(); // اللون

            // ✅ Physical Dimensions (الأبعاد الفيزيائية)
            $table->decimal('length', 10, 2)->nullable(); // الطول
            $table->decimal('width', 10, 2)->nullable(); // العرض
            $table->decimal('height', 10, 2)->nullable(); // الارتفاع

            // Item Type (نوع الصنف)
            $table->string('item_type')->default('goods'); // نوع الصنف (خدمة/بضائع/عمل/أصل/تحويل/حد أدنى)

            $table->boolean('active')->default(true); // نشط

            // Stock Tracking
            $table->boolean('stock_tracking')->default(true); // تتبع المخزون

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'branch_id']);
            $table->index(['company_id', 'unit_id']);
            $table->index(['company_id', 'parent_id']);
            $table->index(['company_id', 'active']);
            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'item_number']);
            $table->unique(['barcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
