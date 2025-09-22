<div class="container py-4">
    <!-- عنوان -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">
            <i class="fas fa-building me-2 text-primary"></i>
            {{ $company->title }}
        </h3>
        <a href="{{ route('admin.panel.companies.list', ['lang' => app()->getLocale()]) }}"
            class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> {{ __('Back') }}
        </a>
    </div>

    <!-- بطاقة الشركة -->
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-body">
            <div class="row">
                <!-- Logo -->
                <div class="col-md-3 text-center">
                    @if ($company->logo)
                        <img src="{{ asset('storage/' . $company->logo) }}" class="img-fluid rounded shadow-sm mb-2"
                            alt="Company Logo">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                            style="height:150px;">
                            <i class="fas fa-image fa-2x text-muted"></i>
                        </div>
                    @endif
                    <p class="text-muted mt-2 small">{{ $company->commercial_registeration_number }}</p>
                </div>

                <!-- معلومات أساسية -->
                <div class="col-md-9">
                    <h5 class="mb-3 text-primary">{{ __('Company Information') }}</h5>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <strong>{{ __('Owner') }}:</strong> {{ $company->user?->full_name ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Email') }}:</strong> {{ $company->email }}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <strong>{{ __('Mobile') }}:</strong> {{ $company->mobile ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Landline') }}:</strong> {{ $company->landline ?? '-' }}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <strong>{{ __('Industry') }}:</strong> {{ $company->industry?->name ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Business Type') }}:</strong> {{ $company->businessType?->name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- التفاصيل المالية -->
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3 text-primary">{{ __('Financial Details') }}</h5>
            <div class="row">
                <div class="col-md-4">
                    <strong>{{ __('Currency') }}:</strong> {{ $company->currency?->code ?? '-' }}
                </div>
                <div class="col-md-4">
                    <strong>{{ __('Fiscal Year') }}:</strong> {{ $company->fiscalYear?->name ?? '-' }}
                </div>
                <div class="col-md-4">
                    <strong>{{ __('VAT Rate') }}:</strong> {{ $company->vat_rate }} %
                </div>
                <div class="col-md-4">
                    <strong>{{ __('Income Tax Rate') }}:</strong> {{ $company->income_tax_rate }} %
                </div>
                <div class="col-md-4">
                    <strong>{{ __('Status') }}:</strong>
                    <span class="badge {{ $company->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                        {{ ucfirst($company->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- الموقع -->
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3 text-primary">{{ __('Location') }}</h5>
            <div class="row">
                <div class="col-md-4">
                    <strong>{{ __('Country') }}:</strong> {{ $company->country?->name ?? '-' }}
                </div>
                <div class="col-md-4">
                    <strong>{{ __('Region') }}:</strong> {{ $company->region?->name ?? '-' }}
                </div>
                <div class="col-md-4">
                    <strong>{{ __('City') }}:</strong> {{ $company->city?->name ?? '-' }}
                </div>
                <div class="col-md-12 mt-2">
                    <strong>{{ __('Address') }}:</strong> {{ $company->address ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <!-- الفروع -->
    <div class="card shadow-lg border-0">
        <div class="card-body">
            <h5 class="mb-3 text-primary">{{ __('Branches') }}</h5>
            @if ($company->branches->count())
                <ul class="list-group list-group-light">
                    @foreach ($company->branches as $branch)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $branch->title }}</span>
                            <small class="text-muted">{{ $branch->address }}</small>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">{{ __('No branches found.') }}</p>
            @endif
        </div>
    </div>
</div>
