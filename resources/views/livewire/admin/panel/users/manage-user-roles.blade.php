<div class="container-fluid p-4 pb-5">

    <div class="pt-5 bg-light mb-4 rounded shadow-sm p-3 d-flex justify-content-between align-items-center" wire:ignore>
        <div>
            <h1 class="mb-2">{{ __('Manage User Roles & Permissions') }}</h1>
            <nav class="d-flex small">
                <a href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}"
                    class="text-reset">{{ __('Home') }}</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.panel.users.list', ['lang' => app()->getLocale()]) }}"
                    class="text-reset">{{ __('Users') }}</a>
                <span class="mx-1">/</span>
                <u>{{ $user->full_name }}</u>
            </nav>
        </div>

        <!-- Actions -->
        <div class="d-flex gap-2">
            <!-- Save Button -->
            <button class="btn btn-primary btn-sm" wire:click="update" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="update">
                    <i class="fas fa-save me-1"></i> {{ __('Save') }}
                </span>
                <span wire:loading wire:target="update">
                    <span class="fas fa-spinner fa-spin me-2"></span>
                </span>
            </button>

            <!-- Back Button -->
            <a href="{{ route('admin.panel.users.list', ['lang' => app()->getLocale()]) }}"
                class="btn btn-secondary btn-sm">
                <i class="fas {{ app()->getLocale() === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }} me-1"></i>
                {{ __('Back') }}
            </a>
        </div>
    </div>


    <div class="row g-4">

        <!-- Roles Card -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm rounded-3 h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>{{ __('Roles') }}</h5>
                </div>
                <div class="card-body">
                    @foreach ($roles as $role)
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="role-{{ $role->id }}"
                                value="{{ $role->name }}" wire:model.live="selectedRoles">
                            <label class="form-check-label" for="role-{{ $role->id }}">
                                {{ __($role->name) }}
                                <span class="badge bg-info">{{ strtoupper($role->guard_name) }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- API Permissions Card -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm rounded-3 h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-code me-2"></i>{{ __('API Permissions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="apiPermissionsAccordion" wire:ignore.self>
                        @foreach ($permissionsByGroup['api'] ?? [] as $group => $perms)
                            <div class="accordion-item mb-2" wire:ignore.self>
                                <h2 class="accordion-header" id="heading-api-{{ $group }}">
                                    <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse"
                                        data-mdb-target="#collapse-api-{{ $group }}">
                                        {{ __(ucfirst($group ?? 'General')) }}
                                    </button>
                                </h2>
                                <div id="collapse-api-{{ $group }}" class="accordion-collapse collapse"
                                    data-mdb-parent="#apiPermissionsAccordion" wire:ignore.self>
                                    <div class="accordion-body">
                                        @foreach ($perms as $perm)
                                            <div class="form-check mb-2">
                                                <input type="checkbox" class="form-check-input"
                                                    id="perm-api-{{ $perm['id'] }}" value="{{ $perm['name'] }}"
                                                    wire:model="selectedPermissions">
                                                <label class="form-check-label" for="perm-api-{{ $perm['id'] }}">
                                                    {{ __($perm['label'] ?? $perm['name']) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Web Permissions Card -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm rounded-3 h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-desktop me-2"></i>{{ __('Web Permissions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="webPermissionsAccordion" wire:ignore.self>
                        @foreach ($permissionsByGroup['web'] ?? [] as $group => $perms)
                            <div class="accordion-item mb-2" wire:ignore.self>
                                <h2 class="accordion-header" id="heading-web-{{ $group }}">
                                    <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse"
                                        data-mdb-target="#collapse-web-{{ $group }}">
                                        {{ __(ucfirst($group ?? 'General')) }}
                                    </button>
                                </h2>
                                <div id="collapse-web-{{ $group }}" class="accordion-collapse collapse"
                                    data-mdb-parent="#webPermissionsAccordion" wire:ignore.self>
                                    <div class="accordion-body">
                                        @foreach ($perms as $perm)
                                            <div class="form-check mb-2">
                                                <input type="checkbox" class="form-check-input"
                                                    id="perm-web-{{ $perm['id'] }}" value="{{ $perm['name'] }}"
                                                    wire:model="selectedPermissions">
                                                <label class="form-check-label" for="perm-web-{{ $perm['id'] }}">
                                                    {{ __($perm['label'] ?? $perm['name']) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
