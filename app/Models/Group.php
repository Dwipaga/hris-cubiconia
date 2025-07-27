<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'group_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_name',
        'group_desc',
        'status',
    ];

    /**
     * Get the users for the group.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'group_id', 'group_id');
    }

    /**
     * Get the group roles for the group.
     */
    public function groupRoles(): HasMany
    {
        return $this->hasMany(GroupRole::class, 'group_id', 'group_id');
    }

    /**
     * Get the menu roles for the group.
     */
    public function menuRoles(): HasMany
    {
        return $this->hasMany(MenuRole::class, 'group_id', 'group_id');
    }
}
