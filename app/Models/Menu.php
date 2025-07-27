<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'menu_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'menu_name',
        'menu_url',
        'menu_parent',
        'menu_type',
        'menu_order',
        'menu_icon',
        'menu_description',
        'status',
    ];

    /**
     * Get the menu roles for the menu.
     */
    /**
     * Get the parent menu.
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'menu_parent', 'menu_id');
    }

    /**
     * Get the child menus.
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'menu_parent', 'menu_id');
    }

    /**
     * Get the user groups that have access to this menu.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'menu_roles', 'menu_id', 'group_id');
    }

  
}