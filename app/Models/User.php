<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Services\EmailService;
use Illuminate\Auth\Passwords\PasswordBroker;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = [
        'super_admin' => 'Super Admin',
        'hotel_admin' => 'Hotel Admin',
        'membership_admin' => 'Membership Admin',
        'manager' => 'Manager',
        'staff' => 'Front Office / Staff',
        'finance' => 'Finance',
        'management' => 'Management',
        'member' => 'Member',
    ];

    /** Update #18 — role yang akunnya wajib melewati approval sebelum login. */
    public const APPROVAL_REQUIRED_ROLES = ['manager', 'staff'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'hotel_id',
        'member_id',
        'is_active',
        'approval_status',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function isStaffSide(): bool
    {
        return ! $this->isMember();
    }

    /** CPC §10/§12 — manajer berwenang menyetujui penukaran voucer. */
    public function isManagerLike(): bool
    {
        return in_array($this->role, ['manager', 'super_admin', 'hotel_admin']);
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending';
    }

    /** Hotel scoping: super admin & management lihat semua hotel */
    public function hasGlobalAccess(): bool
    {
        return in_array($this->role, ['super_admin', 'management', 'membership_admin']);
    }

    /**
     * Override notifikasi reset password agar memakai template HTML profesional
     * (email layout master dengan logo, header & footer).
     */
    public function sendPasswordResetNotification($token): void
    {
        $expires = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ]);

        app(EmailService::class)->sendPasswordReset($this, $resetUrl, $expires);
    }
}
