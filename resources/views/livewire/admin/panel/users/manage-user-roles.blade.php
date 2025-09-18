<div class="container-fluid p-4 pb-5">

    <!-- Heading -->
    <div class="pt-5 bg-body-tertiary mb-4">
        <h1>{{ __('Manage User Roles & Permissions') }}</h1>
        <nav class="d-flex">
            <h6 class="mb-0">
                <a href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}"
                    class="text-reset">{{ __('Home') }}</a>
                <span>/</span>
                <a href="{{ route('admin.panel.users.list', ['lang' => app()->getLocale()]) }}"
                    class="text-reset">{{ __('Users') }}</a>
                <span>/</span>
                <u>{{ $user->full_name }}</u>
            </h6>
        </nav>
    </div>

    <!-- Roles & Permissions Card -->
    <div class="card shadow-lg rounded-3">
        <div class="card-body">

            <!-- Roles -->
            <div class="mb-4">
                <h5>{{ __('Roles') }}</h5>
                <div class="row">
                    @foreach ($roles as $role)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="role-{{ $role->id }}"
                                    value="{{ $role->name }}" wire:model.live="selectedRoles">
                                <label class="form-check-label" for="role-{{ $role->id }}">
                                    {{ __($role->name) }} (<span
                                        class="text-info small">{{ strtoupper($role->guard_name) }}</span>)
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Permissions -->
            <div class="mb-4">
                <h5>{{ __('Direct Permissions') }}</h5>
                <div class="accordion" id="permissionsAccordion">
                    @foreach ($permissionsByGroup as $group => $perms)
                        <div class="accordion-item mb-2">
                            <h2 class="accordion-header" id="heading-{{ $group }}">
                                <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse"
                                    data-mdb-target="#collapse-{{ $group }}">
                                    {{ __(ucfirst($group)) }}
                                </button>
                            </h2>
                            <div id="collapse-{{ $group }}" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <div class="row">
                                        @foreach ($perms as $perm)
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                        id="perm-{{ $perm['id'] }}" value="{{ $perm['name'] }}"
                                                        wire:model="selectedPermissions">
                                                    <label class="form-check-label" for="perm-{{ $perm['id'] }}">
                                                        {{ __($perm['label'] ?? $perm['name']) }}
                                                        (<span
                                                            class="text-info small">{{ strtoupper($perm['guard_name']) }}</span>)
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-primary" wire:click="update" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="update"><i class="fas fa-save"></i>
                        {{ __('Save') }}</span>
                    <span wire:loading wire:target="update">
                        <span class="fas fa-spinner fa-spin me-2"></span>
                    </span>
                </button>

                <a href="{{ route('admin.panel.users.list', ['lang' => app()->getLocale()]) }}"
                    class="btn btn-secondary">
                    <i class="fas {{ app()->getLocale() === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }}"></i>
                    {{ __('Back') }}
                </a>
            </div>

        </div>
    </div>

</div>
