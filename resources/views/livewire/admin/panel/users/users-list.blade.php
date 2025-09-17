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
                <a href="{{ route('admin.panel.users.list', ['lang' => app()->getLocale()]) }}"
                    class="text-reset"><u>{{ __('Users') }}</u></a>
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
                        class="form-control form-icon-trailing" placeholder="{{ __('Search by name or email') }}" />
                    <label class="form-label" for="search">{{ __('Search by name or email') }}</label>
                    <i class="fas fa-search trailing"></i>
                </div>
            </div>

            <div>
                <label class="form-label mb-1" for="status"><strong>{{ __('Status') }}</strong></label>
                <select id="status" class="select" wire:model.live="status">
                    <option value="">{{ __('All') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                    <option value="suspended">{{ __('Suspended') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                </select>
            </div>

            <div>
                <label class="form-label mb-1" for="type"><strong>{{ __('Type') }}</strong></label>
                <select id="type" class="select" wire:model.live="type">
                    <option value="">{{ __('All') }}</option>
                    <option value="super_admin">{{ __('Super Admin') }}</option>
                    <option value="admin">{{ __('Admin') }}</option>
                    <option value="customer">{{ __('Customer') }}</option>
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
            @if (count($selectedUsers) > 0)
                <button class="btn btn-danger btn-sm" wire:click="confirmDeleteSelected">
                    <i class="fas fa-trash-alt"></i> {{ __('Delete') }} ({{ count($selectedUsers) }})
                </button>
            @endif
        </div>
    </div>
    <!-- Filters -->


    <!-- Start Users Table Responsive Bordered -->
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
                    <th>{{ __('Full Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Role') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('CreatedBy') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="text-center">
                            <div class="form-check font-size-16 d-flex justify-content-center align-items-center">
                                <input type="checkbox" class="form-check-input" value="{{ $user->id }}"
                                    wire:model.live="selectedItems">
                            </div>
                        </td>
                        <td>{{ $user->full_name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ $user->roles->first()?->name }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                    'suspended' => 'danger',
                                    'pending' => 'warning',
                                ];
                            @endphp
                            <span class="badge badge-{{ $statusColors[$user->status] ?? 'light' }}">
                                    {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $typeColors = [
                                    'super_admin' => 'primary',
                                    'admin' => 'info',
                                    'customer' => 'dark',
                                ];
                            @endphp
                            <span class="badge badge-{{ $typeColors[$user->type] ?? 'light' }}">
                                {{ __(ucwords(str_replace('_', ' ', $user->type))) }}
                            </span>
                        </td>
                        <td>{{ $user->creator?->full_name }}</td>
                        <td>
                            <!-- Edit Icon -->
                            <span wire:loading.remove wire:target="edit({{ $user->id }})">
                                <a href="#edit" wire:click="edit({{ $user->id }})"
                                    class="text-dark fa-lg me-2 ms-2" title="{{ __('Edit') }}">
                                    <x-icons.edit />
                                </a>
                            </span>
                            <span wire:loading wire:target="edit({{ $user->id }})">
                                <span class="spinner-border spinner-border-sm text-dark me-2 ms-2"
                                    role="status"></span>
                            </span>

                            <!-- Delete Icon -->
                            <span wire:loading.remove wire:target="confirmDelete({{ $user->id }})">
                                <a href="#" wire:click="confirmDelete({{ $user->id }})"
                                    class="text-danger fa-lg me-2 ms-2" title="{{ __('Delete') }}">
                                    <x-icons.delete />
                                </a>
                            </span>
                            <span wire:loading wire:target="confirmDelete({{ $user->id }})">
                                <span class="spinner-border spinner-border-sm text-danger me-2 ms-2"
                                    role="status"></span>
                            </span>

                            <!-- Show Icon -->
                            <span wire:loading.remove wire:target="show({{ $user->id }})">
                                <a href="#" wire:click="show({{ $user->id }})"
                                    class="text-primary fa-lg me-2 ms-2" title="{{ __('Show') }}">
                                    <x-icons.show />
                                </a>
                            </span>
                            <span wire:loading wire:target="show({{ $user->id }})">
                                <span class="spinner-border spinner-border-sm text-primary me-2 ms-2"
                                    role="status"></span>
                            </span>

                            <!-- Manage User Roles Icon -->
                            <span wire:loading.remove wire:target="manageUserRoles({{ $user->id }})">
                                <a href="#" wire:click="manageUserRoles({{ $user->id }})"
                                    class="text-warning fa-lg me-2 ms-2"
                                    title="{{ __('Manage User Roles & Permissions') }}">
                                    <x-icons.manage-user-roles />
                                </a>
                            </span>
                            <span wire:loading wire:target="manageUserRoles({{ $user->id }})">
                                <span class="spinner-border spinner-border-sm text-warning me-2 ms-2"
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

    <!-- End Users Table Responsive Bordered -->

    <!-- Table Pagination -->
    <div class="d-flex justify-content-between mt-4">

        <nav aria-label="...">
            <ul class="pagination pagination-circle">
                {{ $users->withQueryString()->onEachSide(0)->links() }}
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
