<?php
// app/Models/Admin.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';

    // Admin Roles
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_STAFF = 'staff';
    const ROLE_WAREHOUSE_STAFF = 'warehouse_staff';

    // Account Statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
        'img',
        'status',
        'password_change_required',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_change_required' => 'boolean',
    ];

    /**
     * Các accessor sẽ được tự động thêm vào khi model chuyển thành array/JSON.
     * @var array
     */
    protected $appends = [
        'avatar_url',
        'role_name',
        'role_badge_class',
        'status_text',
        'status_badge_class',
    ];

    // Accessor for avatar URL
    public function getAvatarUrlAttribute()
    {
        if ($this->img && Storage::disk('public')->exists($this->img)) {
            return Storage::url($this->img);
        }
        return 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=AD';
    }

    // Helper to check if the admin is a Super Admin
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    // Helper to get displayable role name
    public function getRoleNameAttribute(): string
    {
        if ($this->role === null) {
            return 'Chờ xét duyệt';
        }
        switch ($this->role) {
            case self::ROLE_SUPER_ADMIN:
                return 'Super Admin';
            case self::ROLE_ADMIN:
                return 'Quản trị viên';
            case self::ROLE_STAFF:
                return 'Nhân viên Hỗ trợ';
            case self::ROLE_WAREHOUSE_STAFF:
                return 'Nhân viên Kho';
            default:
                return 'Không rõ vai trò';
        }
    }

    // Helper to get badge class for role
    public function getRoleBadgeClassAttribute(): string
    {
        if ($this->role === null) {
            return 'bg-warning text-dark';
        }
        switch ($this->role) {
            case self::ROLE_SUPER_ADMIN:
                return 'bg-danger';
            case self::ROLE_ADMIN:
                return 'bg-primary';
            case self::ROLE_STAFF:
                return 'bg-info text-dark';
            case self::ROLE_WAREHOUSE_STAFF:
                return 'bg-secondary';
            default:
                return 'bg-light text-dark';
        }
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getStatusTextAttribute(): string
    {
        return $this->isActive() ? 'Hoạt động' : 'Bị khóa';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->isActive() ? 'bg-success' : 'bg-danger';
    }
}
