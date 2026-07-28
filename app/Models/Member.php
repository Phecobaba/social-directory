<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'address',
        'branch_id',
        'title',
        'surname',
        'other_names',
        'date_of_birth',
        'place_of_birth',
        'town_of_origin',
        'village',
        'local_government_of_origin',
        'state_of_origin',
        'occupation',
        'height',
        'phone_number',
        'next_of_kin_name',
        'next_of_kin_relationship',
        'next_of_kin_phone',
        'father_name',
        'mother_name',
        'wife_name',
        'house_address',
        'office_address',
        'photo',
        'dynamic_fields',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'dynamic_fields' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Member $member) {
            if ((! $member->surname || ! $member->other_names) && $member->full_name) {
                $parts = preg_split('/\s+/', trim((string) $member->full_name), 2);
                $member->surname = $member->surname ?: ($parts[0] ?? null);
                $member->other_names = $member->other_names ?: ($parts[1] ?? null);
            }

            if (! $member->full_name && ($member->surname || $member->other_names)) {
                $member->full_name = trim(($member->surname ?? '') . ' ' . ($member->other_names ?? ''));
            }

            if (! $member->house_address && $member->address) {
                $member->house_address = $member->address;
            }

            if (! $member->address && $member->house_address) {
                $member->address = $member->house_address;
            }

            if (! $member->branch_id) {
                $member->branch_id = Branch::query()->value('id');
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getFullNameAttribute(): string
    {
        $structuredName = trim(($this->surname ?? '') . ' ' . ($this->other_names ?? ''));

        if ($structuredName !== '') {
            return $structuredName;
        }

        return (string) ($this->attributes['full_name'] ?? '');
    }
}
