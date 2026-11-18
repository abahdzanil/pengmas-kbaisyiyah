<?php

namespace App\Models;

use Database\Factories\GalleryCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'sort_order'])]
class GalleryCategory extends Model
{
    /** @use HasFactory<GalleryCategoryFactory> */
    use HasFactory;

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    /** id => name pairs for the admin select (referenced from config/ecms.php). */
    public static function selectOptions()
    {
        return static::query()->ordered()->pluck('name', 'id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
