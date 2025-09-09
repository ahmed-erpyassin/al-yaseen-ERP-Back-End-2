<div style="width: 30rem;">
    <form wire:submit.prevent="login">
        <!-- Email input -->
        <div class="form-outline" wire:ignore>
            <input type="email" id="email" maxlength="50" class="form-control form-control-lg"
                wire:model.defer="email" />
            <label class="form-label" for="email">Email</label>
        </div>
        @error('email')
            <div class="form-helper text-danger">{{ $message }}</div>
        @enderror

        <!-- Password input -->
        <div class="form-outline mt-4" wire:ignore>
            <input type="password" id="passwordID" maxlength="30" class="form-control form-control-lg"
                wire:model.defer="password" />
            <label class="form-label" for="passwordID">Password</label>
        </div>
        @error('password')
            <div class="form-helper text-danger">{{ $message }}</div>
        @enderror

        <div class="d-flex justify-content-between align-items-center mb-4 mt-4 px-1" wire:ignore>
            <!-- Checkbox -->
            <div class="form-check">
                <input class="form-check-input" type="checkbox" wire:model="remember" id="rememberMe" />
                <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>
            <div>
                <a href="#">
                    Forgot password?
                </a>
            </div>
        </div>

        <!-- Submit button -->
        <button type="submit" class="btn btn-lg btn-block bg-primary text-white" wire:loading.attr="disabled">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" wire:loading></span>
            Log in
        </button>
    </form>
</div>
