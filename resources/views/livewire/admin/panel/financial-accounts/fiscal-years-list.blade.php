<div class="container-fluid">

    <!-- Heading -->
    <div class="pt-5 bg-body-tertiary mb-4">
        <h1 class="">{{ __('Fiscal Years') }}</h1>
        <!-- Breadcrumb -->
        <nav class="d-flex">
            <h6 class="mb-0">
                <a href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}"
                    class="text-reset">{{ __('Home') }}</a>
                <span>/</span>
                <a href="{{ route('admin.panel.financial-accounts.fiscal-years.list', ['lang' => app()->getLocale()]) }}"
                    class="text-reset"><u>{{ __('Fiscal Years') }}</u></a>
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
                        class="form-control form-icon-trailing" placeholder="{{ __('Search by name') }}" />
                    <label class="form-label" for="search">{{ __('Search by name') }}</label>
                    <i class="fas fa-search trailing"></i>
                </div>
            </div>

            <div>
                <label class="form-label mb-1" for="company_id"><strong>{{ __('Company') }}</strong></label>
                <select id="company_id" class="select" wire:model.live="company_id">
                    <option value="">{{ __('All') }}</option>
                    {{-- @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach --}}
                </select>
            </div>

            <div>
                <label class="form-label mb-1" for="status"><strong>{{ __('Status') }}</strong></label>
                <select id="status" class="select" wire:model.live="status">
                    <option value="">{{ __('All') }}</option>
                    <option value="open">{{ __('Open') }}</option>
                    <option value="closed">{{ __('Closed') }}</option>
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
            @if (count($selectedFiscalYears) > 0)
                <button class="btn btn-danger btn-sm" wire:click="confirmDeleteSelected">
                    <i class="fas fa-trash-alt"></i> {{ __('Delete') }} ({{ count($selectedFiscalYears) }})
                </button>
            @endif
        </div>
    </div>
    <!-- Filters -->

    <!-- Start Fiscal Years Table Responsive Bordered -->
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
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Start Date') }}</th>
                    <th>{{ __('End Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Company') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('CreatedBy') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($fiscalYears as $year)
                    <tr>
                        <td class="text-center">
                            <div class="form-check font-size-16 d-flex justify-content-center align-items-center">
                                <input type="checkbox" class="form-check-input" value="{{ $year->id }}"
                                    wire:model.live="selectedItems">
                            </div>
                        </td>
                        <td>{{ $year->name }}</td>
                        <td>{{ $year->start_date }}</td>
                        <td>{{ $year->end_date }}</td>
                        <td>
                            @if ($year->status === 'open')
                                <span class="badge bg-success">{{ __('Open') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Closed') }}</span>
                            @endif
                        </td>
                        <td>{{ $year->company->title ?? $year->company_id }}</td>
                        <td>{{ $year->user->full_name ?? '-' }}</td>
                        <td>{{ $year->createdBy->full_name ?? '-' }}</td>
                        <td>
                            <!-- Edit Icon -->
                            <span wire:loading.remove wire:target="edit({{ $year->id }})">
                                <a href="#edit" wire:click="edit({{ $year->id }})"
                                    class="text-dark fa-lg me-2 ms-2" title="{{ __('Edit') }}">
                                    <x-icons.edit />
                                </a>
                            </span>
                            <span wire:loading wire:target="edit({{ $year->id }})">
                                <span class="spinner-border spinner-border-sm text-dark me-2 ms-2"
                                    role="status"></span>
                            </span>

                            <!-- Delete Icon -->
                            <span wire:loading.remove wire:target="confirmDelete({{ $year->id }})">
                                <a href="#" wire:click="confirmDelete({{ $year->id }})"
                                    class="text-danger fa-lg me-2 ms-2" title="{{ __('Delete') }}">
                                    <x-icons.delete />
                                </a>
                            </span>
                            <span wire:loading wire:target="confirmDelete({{ $year->id }})">
                                <span class="spinner-border spinner-border-sm text-danger me-2 ms-2"
                                    role="status"></span>
                            </span>

                            <!-- Show Icon -->
                            <span wire:loading.remove wire:target="show({{ $year->id }})">
                                <a href="#" wire:click="show({{ $year->id }})"
                                    class="text-primary fa-lg me-2 ms-2" title="{{ __('Show') }}">
                                    <x-icons.show />
                                </a>
                            </span>
                            <span wire:loading wire:target="show({{ $year->id }})">
                                <span class="spinner-border spinner-border-sm text-primary me-2 ms-2"
                                    role="status"></span>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('No data found') }}
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>
    <!-- End Fiscal Years Table Responsive Bordered -->

    <!-- Table Pagination -->
    <div class="d-flex justify-content-between mt-4">

        <nav aria-label="...">
            <ul class="pagination pagination-circle">
                {{ $fiscalYears->withQueryString()->onEachSide(0)->links() }}
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
