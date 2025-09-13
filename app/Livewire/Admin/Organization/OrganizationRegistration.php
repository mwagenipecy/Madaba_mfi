<?php

namespace App\Livewire\Admin\Organization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrganizationRegistration extends Component
{
    use WithFileUploads;

    // Organization Details
    public $organization_name = '';
    public $organization_type = '';
    public $registration_number = '';
    public $license_number = '';
    public $organization_email = '';
    public $organization_phone = '';
    public $address = '';
    public $city = '';
    public $state = '';
    public $country = 'Nigeria';
    public $postal_code = '';
    public $authorized_capital = '';
    public $incorporation_date = '';
    public $description = '';
    public $logo;

    // Admin User Details
    public $first_name = '';
    public $last_name = '';
    public $user_email = '';
    public $user_phone = '';
    public $password = '';
    public $password_confirmation = '';

    // Component State
    public $current_step = 1;
    public $total_steps = 3;
    public $is_loading = false;
    public $registration_complete = false;
    public $organization_id = null;
    public $user_id = null;

    // Organization Types
    public $organization_types = [
        'microfinance_bank' => 'Microfinance Bank',
        'cooperative_society' => 'Cooperative Society',
        'ngo' => 'Non-Governmental Organization',
        'credit_union' => 'Credit Union',
        'other' => 'Other'
    ];

    // Nigerian States
    public $states = [
        'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 
        'Borno', 'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 
        'FCT', 'Gombe', 'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 
        'Kogi', 'Kwara', 'Lagos', 'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 
        'Oyo', 'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara'
    ];

    protected function rules()
    {
        $rules = [];

        if ($this->current_step == 1) {
            $rules = [
                'organization_name' => ['required', 'string', 'min:3', 'max:255'],
                'organization_type' => ['required', Rule::in(array_keys($this->organization_types))],
                'organization_email' => ['required', 'email', 'unique:organizations,email'],
                'organization_phone' => ['required', 'string', 'min:10', 'max:15'],
                'registration_number' => ['nullable', 'string', 'max:50', 'unique:organizations,registration_number'],
                'license_number' => ['nullable', 'string', 'max:50', 'unique:organizations,license_number'],
            ];
        }

        if ($this->current_step == 2) {
            $rules = [
                'address' => ['required', 'string', 'max:500'],
                'city' => ['required', 'string', 'max:100'],
                'state' => ['required', Rule::in($this->states)],
                'country' => ['required', 'string', 'max:100'],
                'postal_code' => ['nullable', 'string', 'max:10'],
                'authorized_capital' => ['nullable', 'numeric', 'min:0'],
                'incorporation_date' => ['nullable', 'date', 'before:today'],
                'description' => ['nullable', 'string', 'max:1000'],
                'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
            ];
        }

        if ($this->current_step == 3) {
            $rules = [
                'first_name' => ['required', 'string', 'min:2', 'max:100'],
                'last_name' => ['required', 'string', 'min:2', 'max:100'],
                'user_email' => ['required', 'email', 'unique:users,email'],
                'user_phone' => ['nullable', 'string', 'min:10', 'max:15'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'password_confirmation' => ['required'],
            ];
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'organization_name.required' => 'Organization name is required.',
            'organization_name.min' => 'Organization name must be at least 3 characters.',
            'organization_email.unique' => 'This email is already registered.',
            'user_email.unique' => 'This email is already registered.',
            'registration_number.unique' => 'This registration number is already in use.',
            'license_number.unique' => 'This license number is already in use.',
            'logo.image' => 'Logo must be an image file.',
            'logo.max' => 'Logo size must not exceed 2MB.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }

    public function nextStep()
    {
        $this->validate();
        
        if ($this->current_step < $this->total_steps) {
            $this->current_step++;
        }
    }

    public function previousStep()
    {
        if ($this->current_step > 1) {
            $this->current_step--;
        }
    }

    public function register()
    {
        $this->validate();
        $this->is_loading = true;

        try {
            DB::transaction(function () {
                // Create Organization
                $organizationData = [
                    'name' => $this->organization_name,
                    'slug' => Str::slug($this->organization_name),
                    'type' => $this->organization_type,
                    'email' => $this->organization_email,
                    'phone' => $this->organization_phone,
                    'address' => $this->address,
                    'city' => $this->city,
                    'state' => $this->state,
                    'country' => $this->country,
                    'postal_code' => $this->postal_code,
                    'registration_number' => $this->registration_number,
                    'license_number' => $this->license_number,
                    'authorized_capital' => $this->authorized_capital ? floatval($this->authorized_capital) : null,
                    'incorporation_date' => $this->incorporation_date,
                    'description' => $this->description,
                    'status' => 'pending_approval',
                ];

                // Handle logo upload
                if ($this->logo) {
                    $logoPath = $this->logo->store('organization-logos', 'public');
                    $organizationData['logo_path'] = $logoPath;
                }

                $organization = Organization::create($organizationData);
                $this->organization_id = $organization->id;

                // Create Admin User
                $user = User::create([
                    'organization_id' => $organization->id,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->user_email,
                    'phone' => $this->user_phone,
                    'password' => Hash::make($this->password),
                    'role' => 'admin',
                    'status' => 'active',
                    'employee_id' => 'ADM-' . str_pad($organization->id, 4, '0', STR_PAD_LEFT),
                    'permissions' => [
                        'manage_users',
                        'manage_clients',
                        'manage_loans',
                        'manage_savings',
                        'view_reports',
                        'manage_settings',
                    ],
                ]);

                $this->user_id = $user->id;

                // Send welcome email (implement as needed)
                // Mail::to($user->email)->send(new OrganizationWelcomeMail($organization, $user));
            });

            $this->registration_complete = true;
            
            session()->flash('success', 'Organization registered successfully! Please check your email for further instructions.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Registration failed. Please try again.');
            logger()->error('Organization registration failed: ' . $e->getMessage());
        } finally {
            $this->is_loading = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.organization.organization-registration');
    }
}