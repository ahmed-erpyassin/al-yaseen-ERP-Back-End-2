<div class="container-fluid">

    <!-- Heading -->
    <div class="pt-5 bg-body-tertiary mb-4">
        <h1 class="">{{ __('Roles Management') }}</h1>
        <!-- Breadcrumb -->
        <nav class="d-flex">
            <h6 class="mb-0">
                <a href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}"
                    class="text-reset">{{ __('Home') }}</a>
                <span>/</span>
                <a href="{{ route('admin.panel.users.roles.list', ['lang' => app()->getLocale()]) }}"
                    class="text-reset"><u>{{ __('Roles') }}</u></a>
            </h6>
        </nav>
        <!-- Breadcrumb -->
    </div>
    <!-- Heading -->


    <!-- Filters -->
    <div class="row p-2 mb-3 align-items-center justify-content-between">
        <div class="col-md-9 d-flex gap-3" wire:ignore>

            <!-- Search -->
            <div class="mb-2">
                <label class="form-label mb-1" for="search"><strong>{{ __('Search') }}</strong></label>
                <div class="form-outline" data-mdb-input-init>
                    <input type="search" id="search" wire:model.live.debounce.500ms="search"
                        class="form-control form-icon-trailing" placeholder="{{ __('Search by role name') }}" />
                    <label class="form-label" for="search">{{ __('Search by role name') }}</label>
                    <i class="fas fa-search trailing"></i>
                </div>
            </div>

            <!-- Guard filter -->
            <div>
                <label class="form-label mb-1" for="guard"><strong>{{ __('Guard') }}</strong></label>
                <select id="guard" class="select" wire:model.live="guard">
                    <option value="">{{ __('All') }}</option>
                    <option value="web">{{ __('Web') }}</option>
                    <option value="api">{{ __('API') }}</option>
                </select>
            </div>

        </div>
        <div class="col-md-3 d-flex justify-content-end gap-2">

            <!-- زر إضافة -->
            <button class="btn btn-primary btn-sm" wire:click="create" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="create">
                    <i class="fas fa-plus"></i> {{ __('Add Role') }}
                </span>
                <span wire:loading wire:target="create">
                    <span class="fas fa-spinner fa-spin me-2"></span>
                </span>
            </button>

            <!-- زر إعادة التصفية -->
            <button class="btn btn-secondary btn-sm" wire:click="resetFilters" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="resetFilters">
                    <i class="fas fa-undo"></i> {{ __('Reset') }}
                </span>
                <span wire:loading wire:target="resetFilters">
                    <span class="fas fa-spinner fa-spin me-2"></span>
                </span>
            </button>

            @if (count($selectedRoles) > 0)
                <button class="btn btn-danger btn-sm" wire:click="confirmDeleteSelected">
                    <i class="fas fa-trash-alt"></i> {{ __('Delete') }} ({{ count($selectedRoles) }})
                </button>
            @endif
        </div>
    </div>
    <!-- Filters -->


    <!-- Roles Table -->
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
                    <th>{{ __('Role Name') }}</th>
                    <th>{{ __('Guard') }}</th>
                    <th>{{ __('Permissions Count') }}</th>
                    <th>{{ __('Users Count') }}</th>
                    <th>{{ __('Created At') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td class="text-center">
                            <div class="form-check font-size-16 d-flex justify-content-center align-items-center">
                                <input type="checkbox" class="form-check-input" value="{{ $role->id }}"
                                    wire:model.live="selectedRoles">
                            </div>
                        </td>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->guard_name }}</td>
                        <td>{{ $role->permissions_count }}</td>
                        <td>{{ $role->users_count }}</td>
                        <td>{{ $role->created_at->format('Y-m-d') }}</td>
                        <td>
                            <!-- Edit Icon -->
                            <span wire:loading.remove wire:target="edit({{ $role->id }})">
                                <a href="#edit" wire:click="edit({{ $role->id }})"
                                    class="text-dark fa-lg me-2 ms-2" title="{{ __('Edit') }}">
                                    <x-icons.edit />
                                </a>
                            </span>
                            <span wire:loading wire:target="edit({{ $role->id }})">
                                <span class="fas fa-spinner fa-spin text-dark me-2 ms-2" role="status"></span>
                            </span>

                            <!-- Delete -->
                            <a href="#" wire:click="confirmDelete({{ $role->id }})"
                                class="text-danger fa-lg me-2 ms-2" title="{{ __('Delete') }}">
                                <x-icons.delete />
                            </a>

                            <!-- Show Permissions -->
                            <a href="#show" wire:click="show({{ $role->id }})"
                                class="text-primary fa-lg me-2 ms-2" title="{{ __('Show Permissions') }}">
                                <x-icons.show />
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('No roles found') }}
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>
    <!-- End Roles Table -->

    <!-- Pagination -->
    <div class="d-flex justify-content-between mt-4">

        <nav aria-label="...">
            <ul class="pagination pagination-circle">
                {{ $roles->withQueryString()->onEachSide(0)->links() }}
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
    <!-- End Pagination -->

</div>
