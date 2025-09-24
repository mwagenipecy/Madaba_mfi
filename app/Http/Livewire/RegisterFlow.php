<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guest')]
class RegisterFlow extends Component
{
    public string $step = 'details';
    public string $name = '';
    public string $email = '';
    public bool $terms = false;

    public string $plan = '';
    public string $payment_reference = '';

    public ?int $pendingUserId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email',
        'terms' => 'accepted',
    ];

    public function render()
    {
        return view('livewire.register-flow');
    }

    public function submitDetails(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make(str()->random(16)),
            'status' => 'pending',
        ]);

        $this->pendingUserId = $user->id;
        $this->step = 'plan';
    }

    public function submitPlan(): void
    {
        $this->validate([
            'plan' => 'required|in:basic,standard,premium',
            'payment_reference' => 'required|string|max:100',
        ]);

        $user = User::findOrFail($this->pendingUserId);
        $user->plan = $this->plan;
        $user->payment_reference = $this->payment_reference;
        $user->status = 'awaiting_approval';
        $user->save();

        session()->flash('status', 'Registration submitted. We will approve your access shortly.');
        $this->reset(['name','email','terms','plan','payment_reference','pendingUserId']);
        $this->step = 'done';
    }
}



