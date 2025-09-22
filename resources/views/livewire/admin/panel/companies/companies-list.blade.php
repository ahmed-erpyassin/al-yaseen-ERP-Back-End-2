<div class="container-fluid">

    <!-- Heading -->
    <div class="pt-5 bg-body-tertiary mb-4">
        <h1 class="">{{ __('Dashboard') }}</h1>
        <!-- Breadcrumb -->
        <nav class="d-flex">
            <h6 class="mb-0">
                <a href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}"
                    class="text-reset">{{ __('Home') }}</a>
                <span>/</span>
                <a href="{{ route('admin.panel.companies.list', ['lang' => app()->getLocale()]) }}"
                    class="text-reset"><u>{{ __('Companies') }}</u></a>
            </h6>
        </nav>
        <!-- Breadcrumb -->
    </div>
    <!-- Heading -->

    <!-- Filters -->
    <div class="row p-2 mb-3 align-items-center justify-content-between">
        <div class="col-md-9 d-flex gap-3" wire:ignore>

            <div class="mb-2">
                <label class="form-label mb-1" for="search"><strong>{{ __('Search') }}</strong></label>
                <div class="form-outline" data-mdb-input-init>
                    <input type="search" id="search" wire:model.live.debounce.500ms="search"
                        class="form-control form-icon-trailing" placeholder="{{ __('Search by title or email') }}" />
                    <label class="form-label" for="search">{{ __('Search by title or email') }}</label>
                    <i class="fas fa-search trailing"></i>
                </div>
            </div>

            <div>
                <label class="form-label mb-1" for="status"><strong>{{ __('Status') }}</strong></label>
                <select id="status" class="select" wire:model.live="status">
                    <option value="">{{ __('All') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </select>
            </div>

            <div>
                <label class="form-label mb-1" for="industry"><strong>{{ __('Industry') }}</strong></label>
                <select id="industry" class="select" wire:model.live="industry">
                    <option value="">{{ __('All') }}</option>
                    {{-- @foreach ($industries as $industry)
                        <option value="{{ $industry->id }}">{{ $industry->name }}</option>
                    @endforeach --}}
                </select>
            </div>

            <div>
                <label class="form-label mb-1" for="country"><strong>{{ __('Country') }}</strong></label>
                <select id="country" class="select" wire:model.live="country">
                    <option value="">{{ __('All') }}</option>
                    {{-- @foreach ($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach --}}
                </select>
            </div>

        </div>
        <div class="col-md-3 d-flex justify-content-end gap-2">
            <button class="btn btn-primary btn-sm" wire:click="filter">
                <i class="fas fa-filter"></i> {{ __('Filter') }}
            </button>
            <button class="btn btn-secondary btn-sm" wire:click="resetFilters">
                <i class="fas fa-undo"></i> {{ __('Reset') }}
            </button>
            @if (count($selectedCompanies) > 0)
                <button class="btn btn-danger btn-sm" wire:click="confirmDeleteSelected">
                    <i class="fas fa-trash-alt"></i> {{ __('Delete') }} ({{ count($selectedCompanies) }})
                </button>
            @endif
        </div>
    </div>
    <!-- Filters -->

    <!-- Start Companies Table Responsive Bordered -->
    <div class="table-responsive-md text-center">
        <div style="height: 8px; margin-bottom: 12px;">
            <div class="datatable-loader bg-light" style="height: 8px;" wire:loading>
                <span class="datatable-loader-inner">
                    <span class="datatable-progress bg-primary"></span>
                </span>
            </div>
        </div>
        <table class="table table-bordered table-hover align-middle text-center rounded-3 shadow-lg">
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">
                        <div class="form-check font-size-16 d-flex justify-content-center">
                            <input type="checkbox" class="form-check-input" wire:model.live="selectAll" id="select-all">
                        </div>
                    </th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Commercial Registration') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Mobile') }}</th>
                    <th>{{ __('Industry') }}</th>
                    <th>{{ __('Country') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('CreatedBy') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($companies as $company)
                    <tr>
                        <td class="text-center">
                            <div class="form-check font-size-16 d-flex justify-content-center align-items-center">
                                <input type="checkbox" class="form-check-input" value="{{ $company->id }}"
                                    wire:model.live="selectedItems">
                            </div>
                        </td>
                        <td>{{ $company->title }}</td>
                        <td>{{ $company->commercial_registeration_number }}</td>
                        <td>{{ $company->email }}</td>
                        <td>{{ $company->mobile }}</td>
                        <td>{{ optional($company->industry)?->name ?? '-' }}</td>
                        <td>{{ optional($company->country)?->name ?? '-' }}</td>
                        <td>{{ $company->status }}</td>
                        <td>{{ optional($company->creator)->full_name }}</td>
                        <td>

                            @if (Auth::user()->id == $company?->user?->id)
                                <!-- Edit Icon -->
                                <span wire:loading.remove wire:target="edit({{ $company->id }})">
                                    <a href="#edit" wire:click="edit({{ $company->id }})"
                                        class="text-dark fa-lg me-2 ms-2" title="{{ __('Edit') }}">
                                        <x-icons.edit />
                                    </a>
                                </span>
                                <span wire:loading wire:target="edit({{ $company->id }})">
                                    <span class="spinner-border spinner-border-sm text-dark me-2 ms-2"
                                        role="status"></span>
                                </span>

                                <!-- Delete Icon -->
                                <span wire:loading.remove wire:target="confirmDelete({{ $company->id }})">
                                    <a href="#" wire:click="confirmDelete({{ $company->id }})"
                                        class="text-danger fa-lg me-2 ms-2" title="{{ __('Delete') }}">
                                        <x-icons.delete />
                                    </a>
                                </span>
                                <span wire:loading wire:target="confirmDelete({{ $company->id }})">
                                    <span class="spinner-border spinner-border-sm text-danger me-2 ms-2"
                                        role="status"></span>
                                </span>
                            @endif


                            <!-- Show Icon -->
                            <span wire:loading.remove wire:target="show({{ $company->id }})">
                                <a href="#" wire:click="show({{ $company->id }})"
                                    class="text-primary fa-lg me-2 ms-2" title="{{ __('Show') }}">
                                    <x-icons.show />
                                </a>
                            </span>
                            <span wire:loading wire:target="show({{ $company->id }})">
                                <span class="spinner-border spinner-border-sm text-primary me-2 ms-2"
                                    role="status"></span>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('No data found') }}
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>
    <!-- End Companies Table Responsive Bordered -->

    <!-- Table Pagination -->
    <div class="d-flex justify-content-between mt-4">

        <nav aria-label="...">
            <ul class="pagination pagination-circle">
                {{ $companies->withQueryString()->onEachSide(0)->links() }}
            </ul>
        </nav>

        <div class="col-md-1" wire:ignore>
            <select class="select" wire:model.live="pagination">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>

    </div>
    <!-- Table Pagination -->

</div>
