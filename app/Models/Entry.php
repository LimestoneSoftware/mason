<?php

namespace App\Models;

use App\Traits\MenuItemable;
use App\Traits\Metable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Entry extends Model
{
    use HasFactory, SoftDeletes, Metable, MenuItemable;

    const ICON = 'fa-file';

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_SCHEDULED = 'scheduled';

    protected $fillable = [
        'name',
        'locale_id',
        'title',
        'content',
        'summary',
        'author_id',
        'cover_id',
        'cover_file',
        'published_at',
        'taxonomies',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Static Methods
     */

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder
                ->orderBy('published_at', 'desc')
                ->orderBy('created_at', 'desc');
        });

        static::saving(function ($entry) {
            $entry->name ??= Str::slug($entry->title);
        });
    }

    public static function statusOptions()
    {
        return [static::STATUS_DRAFT, static::STATUS_PUBLISHED, static::STATUS_SCHEDULED];
    }

    /**
     * Scopes
     */

    public static function scopeByName($query, $name)
    {
        return is_iterable($name)
            ? $query->whereIn('name', $name)
            : $query->where('name', $name);
    }

    public static function scopeByType($query, $entryType)
    {
        return $query->whereIn('type_id', prepareValueForScope($entryType, EntryType::class));
    }

    public static function scopeByLocale($query, $locale)
    {
        return $query->whereIn('locale_id', prepareValueForScope($locale, Locale::class));
    }

    public static function scopeByAuthor($query, $author)
    {
        return $query->whereIn('author_id', prepareValueForScope($author, User::class));
    }

    public static function scopeByStatus($query, string $status)
    {
        switch ($status) {
            case static::STATUS_DRAFT:
                return $query->whereNull('published_at');

            case static::STATUS_PUBLISHED:
                return $query->where('published_at', '<=', now());

            case static::STATUS_SCHEDULED:
                return $query->where('published_at', '>', now());
        }
    }

    public static function scopeFilter($query, $filters)
    {
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['locale_id'])) {
            $query->byLocale($filters['locale_id']);
        }

        if (isset($filters['author_id'])) {
            $query->byAuthor($filters['author_id']);
        }

        return $query;
    }

    public static function scopeSearch($query, $term)
    {
        return $query
            ->where('title', 'LIKE', "%{$term}%")
            ->orWhere('content', 'LIKE', "%{$term}%")
            ->orWhere('summary', 'LIKE', "%{$term}%");
    }

    /**
     * Helpers
     */

    public function __toString()
    {
        return "{$this->title}";
    }

    public function getUrl($absolute = true)
    {
        if ($this->exists() && $entry = $this) {
            if (isset($this->locale) && ! $this->locale->is_default) {
                return route('locale.entry', ['locale' => $this->locale->name, $entry], $absolute);
            } else {
                return route('entry', [$entry], $absolute);
            }
        }
    }

    public function publish()
    {
        $this->update(['published_at' => now()]);
    }

    public function view()
    {
        $views = [
            "{$this->locale->name}/{$this->type->name}.{$this->name}",
            "{$this->locale->name}/{$this->type->name}.default",
            "{$this->locale->name}/{$this->type->name}",
            "{$this->type->name}.{$this->name}",
            "{$this->type->name}.default",
            "{$this->type->name}",
        ];

        foreach ($views as $view) {
            if (view()->exists($view)) {
                return $view;
            }
        }
    }

    /**
     * Accessors & Mutators
     */

    public function getTextAttribute()
    {
        return strip_tags($this->attributes['content']);
    }

    public function getSummaryAttribute()
    {
        return $this->attributes['summary'] ?? Str::limit($this->text, 150);
    }

    public function getStatusAttribute()
    {
        if (isset($this->published_at)) {
            if ($this->published_at <= now()) {
                return static::STATUS_PUBLISHED;
            } else {
                return static::STATUS_SCHEDULED;
            }
        } else {
            return static::STATUS_DRAFT;
        }
    }

    public function getUrlAttribute()
    {
        return $this->getUrl(true);
    }

    public function getAbsoluteUrlAttribute()
    {
        return $this->getUrl(true);
    }

    public function getRelativeUrlAttribute()
    {
        return $this->getUrl(false);
    }

    public function setCoverFileAttribute($file)
    {
        $media = new Media(['file' => $file]);
        $media->parent()->associate($this);

        if ($media->save()) {
            $this->cover()->associate($media);
        }
    }

    public function setTaxonomiesAttribute($taxonomies)
    {
        $this->taxonomies()->sync($taxonomies);
    }

    /**
     * Relationships
     */

    public function type()
    {
        return $this->belongsTo(EntryType::class);
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function cover()
    {
        return $this->belongsTo(Media::class);
    }

    public function taxonomies()
    {
        return $this->belongsToMany(Taxonomy::class);
    }
}
