<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TicketTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'html_template',
        'css_styles',
        'template_variables',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'template_variables' => 'array',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    /**
     * Get the default template
     */
    public static function getDefault()
    {
        return self::where('is_default', true)
                   ->where('is_active', true)
                   ->first();
    }

    /**
     * Get active templates
     */
    public static function getActive()
    {
        return self::where('is_active', true)
                   ->orderBy('sort_order')
                   ->orderBy('name')
                   ->get();
    }

    /**
     * Set as default template
     */
    public function setAsDefault()
    {
        // Remove default from other templates
        self::where('is_default', true)->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Get template variables as array
     */
    public function getTemplateVariablesArray()
    {
        return $this->template_variables ?? [];
    }

    /**
     * Render template with data
     */
    public function render($data = [])
    {
        $html = $this->html_template;
        $css = $this->css_styles;

        // Replace variables in template
        foreach ($data as $key => $value) {
            $html = str_replace("{{" . $key . "}}", $value, $html);
        }

        return [
            'html' => $html,
            'css' => $css,
        ];
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default template
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
