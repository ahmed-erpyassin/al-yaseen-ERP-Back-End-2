<div class="container-fluid">
    <!-- Div Alert -->
    @if($showAlert)
        <div class="alert alert-{{ $alertType === 'success' ? 'success' : ($alertType === 'error' ? 'danger' : 'info') }} alert-dismissible fade show" role="alert">
            @if($alertTitle)
                <h5 class="alert-heading">{{ $alertTitle }}</h5>
            @endif
            <p class="mb-0">{{ $alertMessage }}</p>
            <button type="button" class="btn-close" wire:click="hideAlert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        {{ $isEdit ? 'تعديل بيانات العميل' : 'إضافة عميل جديد' }}
                    </h4>
                    <div>
                        <button type="button" class="btn btn-secondary me-2" wire:click="cancel">
                            <i class="fas fa-times"></i> إلغاء
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="fas fa-save"></i> حفظ
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="row">
                            <!-- Customer Information Section -->
                            <div class="col-12">
                                <h5 class="text-primary mb-3">معلومات العميل</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="customer_number" class="form-label">رقم العميل <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('customer_number') is-invalid @enderror" 
                                       id="customer_number" wire:model="customer_number" placeholder="أدخل رقم العميل">
                                @error('customer_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">اسم الشركة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                       id="company_name" wire:model="company_name" placeholder="أدخل اسم الشركة">
                                @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">الاسم الأول <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                       id="first_name" wire:model="first_name" placeholder="أدخل الاسم الأول">
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="second_name" class="form-label">الاسم الثاني <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('second_name') is-invalid @enderror" 
                                       id="second_name" wire:model="second_name" placeholder="أدخل الاسم الثاني">
                                @error('second_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="contact_name" class="form-label">اسم جهة الاتصال <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('contact_name') is-invalid @enderror" 
                                       id="contact_name" wire:model="contact_name" placeholder="أدخل اسم جهة الاتصال">
                                @error('contact_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" wire:model="email" placeholder="أدخل البريد الإلكتروني">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">الهاتف <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" wire:model="phone" placeholder="أدخل رقم الهاتف">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="mobile" class="form-label">الجوال <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('mobile') is-invalid @enderror" 
                                       id="mobile" wire:model="mobile" placeholder="أدخل رقم الجوال">
                                @error('mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="address_one" class="form-label">العنوان الأول <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('address_one') is-invalid @enderror" 
                                       id="address_one" wire:model="address_one" placeholder="أدخل العنوان الأول">
                                @error('address_one') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="address_two" class="form-label">العنوان الثاني <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('address_two') is-invalid @enderror" 
                                       id="address_two" wire:model="address_two" placeholder="أدخل العنوان الثاني">
                                @error('address_two') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">الرمز البريدي <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" wire:model="postal_code" placeholder="أدخل الرمز البريدي">
                                @error('postal_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="licensed_operator" class="form-label">المشغل المرخص <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('licensed_operator') is-invalid @enderror" 
                                       id="licensed_operator" wire:model="licensed_operator" placeholder="أدخل المشغل المرخص">
                                @error('licensed_operator') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tax_number" class="form-label">الرقم الضريبي <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('tax_number') is-invalid @enderror" 
                                       id="tax_number" wire:model="tax_number" placeholder="أدخل الرقم الضريبي">
                                @error('tax_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model="status">
                                    <option value="active">نشط</option>
                                    <option value="inactive">غير نشط</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Account Information Section -->
                            <div class="col-12 mt-4">
                                <h5 class="text-primary mb-3">معلومات الحساب</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">الكود <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" wire:model="code" placeholder="أدخل الكود">
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="invoice_type" class="form-label">نوع الفاتورة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('invoice_type') is-invalid @enderror" 
                                       id="invoice_type" wire:model="invoice_type" placeholder="أدخل نوع الفاتورة">
                                @error('invoice_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">الفئة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                       id="category" wire:model="category" placeholder="أدخل الفئة">
                                @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="currency_id" class="form-label">العملة <span class="text-danger">*</span></label>
                                <select class="form-select @error('currency_id') is-invalid @enderror" id="currency_id" wire:model="currency_id">
                                    <option value="">اختر العملة</option>
                                    <!-- Add currency options here -->
                                </select>
                                @error('currency_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">ملاحظات</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" wire:model="notes" rows="3" placeholder="أدخل أي ملاحظات إضافية"></textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Hidden fields for IDs -->
                            <input type="hidden" wire:model="company_id">
                            <input type="hidden" wire:model="branch_id">
                            <input type="hidden" wire:model="employee_id">
                            <input type="hidden" wire:model="country_id">
                            <input type="hidden" wire:model="region_id">
                            <input type="hidden" wire:model="city_id">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.alert {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border-left: 4px solid #17a2b8;
}

.alert-heading {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    opacity: 0.7;
    cursor: pointer;
}

.btn-close:hover {
    opacity: 1;
}
</style>

