<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id', 'document_type', 'file_path', 'original_filename', 'uploaded_by'
    ];

    protected $appends = ['download_url'];

    public function getDownloadUrlAttribute(): string
    {
        return route('api.v1.leads.documents.download', [
            'ulid' => $this->lead->ulid,
            'id'   => $this->id
        ]);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
