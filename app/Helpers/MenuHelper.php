<?php

namespace App\Helpers;

use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class MenuHelper
{
    /**
     * Get menu items based on conditions
     *
     * @param array $where Conditions for the query
     * @param string $type Result type ('result' or 'row')
     * @param array $orderBy Order by clause
     * @return mixed
     */
    public static function get($where = [], $type = 'result', $orderBy = [])
    {
        $query = DB::table('menu');
        
        if (!empty($where)) {
            $query->where($where);
        }
        
        if (!empty($orderBy)) {
            $query->orderBy($orderBy[0], $orderBy[1]);
        }
        
        return $type == 'row' ? $query->first() : $query->get();
    }
    
    /**
     * Insert a new menu
     *
     * @param array $data Data to insert
     * @return int|bool
     */
    public static function insert($data)
    {
        return Menu::insertGetId($data);
    }
    
    /**
     * Update a menu
     *
     * @param array $where Conditions for the update
     * @param array $data Data to update
     * @return bool
     */
    public static function update($where, $data)
    {
        return Menu::where($where)->update($data);
    }
    
    /**
     * Delete a menu
     *
     * @param array $where Conditions for deletion
     * @return bool
     */
    public static function delete($where)
    {
        return Menu::where($where)->delete();
    }

    /**
     * Assign menu roles to user groups
     * 
     * @param int $menuId Menu ID
     * @param array $groupIds Group IDs
     * @return void
     */
    public static function assignGroupsToMenu($menuId, $groupIds = [])
    {
        // Delete existing roles
        DB::table('menu_role')->where('menu_id', $menuId)->delete();
        
        // Insert new roles
        foreach ($groupIds as $groupId) {
            DB::table('menu_role')->insert([
                'group_id' => $groupId,
                'menu_id' => $menuId,
                'status' => 1,
            ]);
        }
    }

    /**
     * Get menu roles for a menu
     * 
     * @param int $menuId Menu ID
     * @return array
     */
    public static function getMenuRoles($menuId)
    {
        return DB::table('group')
            ->select('group.group_id')
            ->join('menu_role', 'group.group_id', '=', 'menu_role.group_id')
            ->where('menu_role.menu_id', $menuId)
            ->pluck('group_id')
            ->toArray();
    }

    /**
     * Reorder menu items when one is deleted or moved
     *
     * @param object $menu Menu object
     * @return void
     */
    public static function reorderAfterDelete($menu)
    {
        DB::table('menu')
            ->where([
                'menu_type' => $menu->menu_type, 
                'menu_parent' => $menu->menu_parent, 
                'menu_order' => '>', $menu->menu_order
            ])
            ->decrement('menu_order');
    }
}