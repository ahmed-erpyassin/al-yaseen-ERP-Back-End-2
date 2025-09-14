<?php

namespace App\Livewire\Admin\Auth;

use App\Helpers\LivewireHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Users\Models\User;

class Login extends Component
{
    use LivewireHelper;

    public $email = '';
    public $password = '';
    public $remember = false;

    #[Layout('layouts.admin.auth.login', ['headerTitle' => 'نظام إدار المؤسسات - تسجيل الدخول']), Title('Admin Login')]
    public function render()
    {
        return view('livewire.admin.auth.login');
    }

    public function login()
    {
        $data = $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            $this->alertMessage('البريد الإلكتروني غير مسجل', 'error', 'center');
            return;
        }

        if (!Hash::check($data['password'], $user->password)) {
            $this->alertMessage('كلمة المرور غير صحيحة', 'error', 'center');
            return;
        }

        // if (!$user->is_active) {
        //     $this->alertMessage('حسابك غير مفعل', 'error');
        //     return;
        // }

        Auth::guard('web')->login($user, $this->remember);
        $this->alertMessage('تم تسجيل الدخول بنجاح', 'success', 'center');
        return redirect()->route('admin.panel.index');
    }
}
