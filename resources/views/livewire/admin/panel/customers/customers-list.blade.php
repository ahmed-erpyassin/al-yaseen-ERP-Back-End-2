<div class="container-fluid">
    <!-- Div Alert -->
    @if($showAlert)
        <div class="alert alert-{{ $alertType === 'success' ? 'success' : ($alertType === 'error' ? 'danger' : ($alertType === 'warning' ? 'warning' : 'info')) }} alert-dismissible fade show" role="alert">
            @if($alertTitle)
                <h5 class="alert-heading">{{ $alertTitle }}</h5>
            @endif
            <p class="mb-0">{{ $alertMessage }}</p>
            <button type="button" class="btn-close" wire:click="hideAlert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteConfirm)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تأكيد الحذف</h5>
                        <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                    </div>
                    <div class="modal-body">
                        <p>هل أنت متأكد من حذف هذا العميل؟ لا يمكن التراجع عن هذا الإجراء.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">إلغاء</button>
                        <button type="button" class="btn btn-danger" wire:click="confirmDelete">حذف</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">قائمة العملاء</h4>
                    <div>
                        @if(count($selectedCustomers) > 0)
                            <button type="button" class="btn btn-danger me-2" wire:click="bulkDelete">
                                <i class="fas fa-trash"></i> حذف المحدد ({{ count($selectedCustomers) }})
                            </button>
                        @endif
                        <a href="{{ route('admin.panel.customers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة عميل جديد
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filters -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" wire:model.live="search" placeholder="البحث في العملاء...">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" wire:model.live="pagination">
                                <option value="10">10 لكل صفحة</option>
                                <option value="25">25 لكل صفحة</option>
                                <option value="50">50 لكل صفحة</option>
                                <option value="100">100 لكل صفحة</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" wire:model.live="sort_field">
                                <option value="created_at">تاريخ الإنشاء</option>
                                <option value="first_name">الاسم الأول</option>
                                <option value="company_name">اسم الشركة</option>
                                <option value="email">البريد الإلكتروني</option>
                                <option value="customer_number">رقم العميل</option>
                            </select>
                        </div>
                    </div>

                    <!-- Customers Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>
                                        <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                                    </th>
                                    <th wire:click="sortBy('customer_number')" style="cursor: pointer;">
                                        رقم العميل
                                        @if($sort_field === 'customer_number')
                                            <i class="fas fa-sort-{{ $sort_direction === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('company_name')" style="cursor: pointer;">
                                        اسم الشركة
                                        @if($sort_field === 'company_name')
                                            <i class="fas fa-sort-{{ $sort_direction === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('first_name')" style="cursor: pointer;">
                                        الاسم
                                        @if($sort_field === 'first_name')
                                            <i class="fas fa-sort-{{ $sort_direction === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('email')" style="cursor: pointer;">
                                        البريد الإلكتروني
                                        @if($sort_field === 'email')
                                            <i class="fas fa-sort-{{ $sort_direction === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </th>
                                    <th>الهاتف</th>
                                    <th>الحالة</th>
                                    <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                                        تاريخ الإنشاء
                                        @if($sort_field === 'created_at')
                                            <i class="fas fa-sort-{{ $sort_direction === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                    <tr>
                                        <td>
                                            <input type="checkbox" wire:model="selectedCustomers" value="{{ $customer->id }}" class="form-check-input">
                                        </td>
                                        <td>{{ $customer->customer_number }}</td>
                                        <td>{{ $customer->company_name }}</td>
                                        <td>{{ $customer->first_name }} {{ $customer->second_name }}</td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->phone }}</td>
                                        <td>
                                            <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'danger' }}">
                                                {{ $customer->status === 'active' ? 'نشط' : 'غير نشط' }}
                                            </span>
                                        </td>
                                        <td>{{ $customer->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="edit({{ $customer->id }})" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="delete({{ $customer->id }})" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p>لا توجد عملاء</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($customers->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $customers->links() }}
                        </div>
                    @endif
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

.alert-warning {
    background-color: #fff3cd;
    color: #856404;
    border-left: 4px solid #ffc107;
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

.table th {
    cursor: pointer;
    user-select: none;
}

.table th:hover {
    background-color: rgba(255,255,255,0.1);
}

.modal.show {
    display: block !important;
}
</style>

