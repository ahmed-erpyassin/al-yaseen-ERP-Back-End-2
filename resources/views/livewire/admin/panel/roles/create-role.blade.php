<div class="container-fluid p-4 pb-5">

    <!-- Heading -->
    <div class="pt-5 bg-body-tertiary mb-4">
        <h1 class="">{{ __('Create Role') }}</h1>
        <nav class="d-flex">
            <h6 class="mb-0">
                <a href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}"
                    class="text-reset">{{ __('Home') }}</a>
                <span>/</span>
                <a href="{{ route('admin.panel.users.roles.list', ['lang' => app()->getLocale()]) }}"
                    class="text-reset">{{ __('Roles') }}</a>
                <span>/</span>
                <u>{{ __('Create') }}</u>
            </h6>
        </nav>
    </div>
    <!-- Heading -->

    <!-- Form Card -->
    <div class="card shadow-lg rounded-3">
        <div class="card-body">
            <div>

                <div class="row">

                    <!-- Name -->
                    <div class="col-md-6 mb-4">
                        <label class="form-label" for="name">{{ __('Role Name') }}</label>
                        <input type="text" id="name" wire:model.defer="name" class="form-control"
                            placeholder="{{ __('Enter role name') }}" />
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Guard -->
                    <div class="col-md-6 mb-4">
                        <div wire:ignore>
                            <label class="form-label" for="guard_name">{{ __('Guard') }}</label>
                            <select id="guard_name" class="select" wire:model.live="guard_name">
                                <option value="web">Web</option>
                                <option value="api">API</option>
                            </select>
                        </div>
                        @error('guard_name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </div>


                <!-- Permissions -->
                <div class="mb-4">
                    <h5 class="mb-3">{{ __('Permissions') }}</h5>

                    <div class="accordion" id="guardsAccordion">
                        @php
                            // تصفية الصلاحيات حسب Guard المختار
                            $filteredPermissions = [];
                            foreach ($permissionsByGroup as $group => $perms) {
                                $groupPerms = array_filter($perms, function ($perm) use ($guard_name) {
                                    return $perm['guard_name'] === $guard_name;
                                });

                                if (count($groupPerms) > 0) {
                                    $filteredPermissions[$group] = $groupPerms;
                                }
                            }
                        @endphp

                        @if (!empty($filteredPermissions))
                            @foreach ($filteredPermissions as $group => $perms)
                                <div class="accordion-item mb-2">
                                    <h2 class="accordion-header" id="heading-{{ $group }}">
                                        <button class="accordion-button collapsed" type="button"
                                            data-mdb-toggle="collapse" data-mdb-target="#collapse-{{ $group }}">
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
                                                                id="perm-{{ $perm['id'] }}"
                                                                value="{{ $perm['name'] }}"
                                                                wire:model="selectedPermissions">
                                                            <label class="form-check-label"
                                                                for="perm-{{ $perm['id'] }}">
                                                                {{ __($perm['label'] ?? $perm['name']) }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">{{ __('No permissions available for this guard.') }}</p>
                        @endif
                    </div>

                    @error('selectedPermissions')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>



                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2">

                    <button type="submit" class="btn btn-primary" wire:click="create" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="create">
                            <i class="fas fa-plus"></i> {{ __('Create') }}
                        </span>

                        <span wire:loading wire:target="create">
                            <span class="fas fa-spinner fa-spin me-2"></span>
                        </span>
                    </button>

                    <a href="{{ route('admin.panel.users.roles.list', ['lang' => app()->getLocale()]) }}"
                        class="btn btn-secondary">
                        <i class="fas {{ app()->getLocale() === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }}"></i>
                        {{ __('Back') }}
                    </a>
                </div>

            </div>
        </div>
    </div>
    <!-- End Form Card -->

</div>
