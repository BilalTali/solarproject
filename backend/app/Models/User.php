<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            if (!$user->qr_token) {
                $user->qr_token = bin2hex(random_bytes(16));
            }
        });
    }

    /**
     * SECURITY: Only include fields safe for mass-assignment.
     * Do NOT add: role, status, agent_id, qr_token, approved_at, approved_by.
     * These must be assigned explicitly to prevent privilege escalation.
     */
    protected $fillable = [
        'name', 'father_name', 'dob', 'blood_group', 'email', 'mobile', 'password',
        'district', 'state', 'area', 'aadhaar_number', 'whatsapp_number',
        'occupation', 'profile_photo', 'religion', 'gender', 'marital_status',
        'permanent_address', 'current_address', 'pincode', 'landmark',
        'pan_number', 'voter_id', 'bank_name', 'bank_account_number',
        'bank_ifsc', 'bank_branch', 'upi_id', 'qualification', 'experience_years',
        'languages_known', 'reference_name', 'reference_mobile', 'territory',
        'target_monthly', 'letter_number', 'joining_date', 'signature_image',
        'aadhaar_document', 'pan_document', 'education_level', 'education_cert',
        'resume', 'mou_signed', 'super_agent_id', 'created_by_super_agent_id',
    ];

    protected $appends = ['profile_photo_url', 'profile_completion'];

    protected $hidden = [
        'password', 'remember_token', 'aadhaar_number', 'bank_account_number'
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'approved_at'       => 'datetime',
            'dob'               => 'date',
            'joining_date'      => 'date',
            'password'          => 'hashed',
            'aadhaar_number'    => 'encrypted',
            'bank_account_number' => 'encrypted',
            'managed_states'    => 'array',
            'languages_known'   => 'array',
        ];
    }

    // ====== Accessors ======

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo) {
            return null;
        }

        if (filter_var($this->profile_photo, FILTER_VALIDATE_URL)) {
            return $this->profile_photo;
        }

        return url('storage/' . $this->profile_photo);
    }

    /**
     * Calculate profile completion percentage
     */
    public function getProfileCompletionAttribute(): int
    {
        $fields = [
            'father_name', 'dob', 'blood_group', 'email', 'mobile',
            'state', 'district', 'area', 'aadhaar_number', 'whatsapp_number',
            'occupation', 'religion', 'gender', 'marital_status',
            'permanent_address', 'current_address', 'pincode',
            'pan_number', 'voter_id',
            'bank_name', 'bank_account_number', 'bank_ifsc', 'bank_branch',
            'qualification', 'experience_years', 'languages_known',
            'reference_name', 'reference_mobile', 'territory'
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($this->{$field})) {
                $completed++;
            }
        }

        return (int) round(($completed / count($fields)) * 100);
    }

    // ====== Relationships ======

    /** Agent → their assigned Super Agent */
    public function superAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_agent_id');
    }

    /** Super Agent → all agents assigned to them */
    public function managedAgents(): HasMany
    {
        return $this->hasMany(User::class, 'super_agent_id');
    }

    /** Super Agent → agents they originally created */
    public function createdAgents(): HasMany
    {
        return $this->hasMany(User::class, 'created_by_super_agent_id');
    }

    /** Super Agent → leads they created directly */
    public function createdLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'created_by_super_agent_id');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_agent_id');
    }

    public function assignedSuperAgentLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_super_agent_id');
    }

    public function submittedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'submitted_by_agent_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'payee_id')->where('payee_role', 'agent');
    }

    public function createdBySuperAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_super_agent_id');
    }

    public function overrideCommissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'payee_id')->where('payee_role', 'super_agent');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function qrScanLogs(): HasMany
    {
        return $this->hasMany(QrScanLog::class);
    }

    // ====== Query Scopes ======

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', 'admin');
    }

    public function scopeSuperAgents(Builder $query): Builder
    {
        return $query->where('role', 'super_agent');
    }

    public function scopeAgents(Builder $query): Builder
    {
        return $query->where('role', 'agent');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    // ====== Helper Methods ======

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAgent(): bool
    {
        return $this->role === 'super_agent';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function hasAssignedSuperAgent(): bool
    {
        return !is_null($this->super_agent_id);
    }
}
