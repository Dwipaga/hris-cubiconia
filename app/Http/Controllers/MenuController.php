<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Group;
use App\Models\MenuRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class MenuController extends Controller
{
    /**
     * Display a listing of the menus with DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menus = Menu::with('parent')
            ->orderBy('menu_type')
            ->orderBy('menu_parent')
            ->orderBy('menu_order')
            ->get();

        return view('menus.index', compact('menus'));
    }

    /**
     * Display a tree view of the menus.
     *
     * @return \Illuminate\Http\Response
     */
    public function tree()
    {
        // Get all menus grouped by type
        $menus = Menu::whereNull('menu_parent')
            ->orWhere('menu_parent', 0)
            ->orderBy('menu_order')
            ->with(['children' => function ($query) {
                $query->orderBy('menu_order');
            }])
            ->get()
            ->groupBy('menu_type');

        $menuTypes = ['side-menu' => [], 'top-menu' => []];
        
        foreach ($menus as $type => $menuItems) {
            $menuTypes[$type] = $menuItems;
        }

        return view('menus.tree', compact('menuTypes'));
    }

    /**
     * Show the form for creating a new menu.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $type = $request->type ?? 'side-menu';
        $parentId = $request->parent ?? 0;
        
        $groups = Group::all();
        
        $parents = Menu::whereNull('menu_parent')
            ->orWhere('menu_parent', 0)
            ->where('menu_type', $type)
            ->get();
        
        $menu_role = [];
        
        return view('menus.form', compact('parents', 'groups', 'menu_role', 'type', 'parentId'));
    }

    /**
     * Store a newly created menu in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'menu_name' => 'required',
            'menu_type' => 'required',
            'status' => 'required',
        ]);

        DB::beginTransaction();
        
        try {
            // Get the highest order for the menu type and parent
            $lastOrder = Menu::where('menu_type', $request->menu_type)
                ->where('menu_parent', $request->menu_parent ?? 0)
                ->max('menu_order') ?? 0;
            
            // Create the menu
            $menu = new Menu();
            $menu->menu_name = $request->menu_name;
            $menu->menu_description = $request->menu_description;
            $menu->menu_url = $request->menu_url;
            $menu->menu_icon = $request->menu_icon;
            $menu->menu_parent = $request->menu_parent ?? 0;
            $menu->menu_type = $request->menu_type;
            $menu->menu_order = $lastOrder + 1;
            $menu->status = $request->status;
            $menu->save();
            
            // Save menu role access
            if ($request->has('group_user') && is_array($request->group_user)) {
                foreach ($request->group_user as $groupId) {
                    $menuRole = new MenuRole();
                    $menuRole->menu_id = $menu->menu_id;
                    $menuRole->group_id = $groupId;
                    $menuRole->save();
                }
            }
            
            DB::commit();
            
            return redirect()->route('menus.index')
                ->with('success', 'Menu created successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to create menu: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified menu.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        $groups = Group::all();
        
        // Get parent menu options based on menu type
        $parents = Menu::whereNull('menu_parent')
            ->orWhere('menu_parent', 0)
            ->where('menu_type', $menu->menu_type)
            ->where('menu_id', '!=', $id) // Exclude itself
            ->get();
        
        // Get assigned roles
        $menu_role = MenuRole::where('menu_id', $id)
            ->pluck('group_id')
            ->toArray();
        
        return view('menus.form', compact('menu', 'parents', 'groups', 'menu_role'));
    }

    /**
     * Update the specified menu in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'menu_name' => 'required',
            'menu_type' => 'required',
        ]);

        DB::beginTransaction();
        
        try {
            $menu = Menu::findOrFail($id);
            
            // Check if menu type or parent has changed
            $typeOrParentChanged = $menu->menu_type != $request->menu_type || 
                                   $menu->menu_parent != ($request->menu_parent ?? 0);
            
            // Update menu
            $menu->menu_name = $request->menu_name;
            $menu->menu_description = $request->menu_description;
            $menu->menu_url = $request->menu_url;
            $menu->menu_icon = $request->menu_icon;
            $menu->menu_parent = $request->menu_parent ?? 0;
            $menu->menu_type = $request->menu_type;
            $menu->status = $request->has('status') ? 1 : 0;
            
            // If menu type or parent changed, reset order
            if ($typeOrParentChanged) {
                $lastOrder = Menu::where('menu_type', $request->menu_type)
                    ->where('menu_parent', $request->menu_parent ?? 0)
                    ->max('menu_order') ?? 0;
                
                $menu->menu_order = $lastOrder + 1;
            }
            
            $menu->save();
            
            // Update menu role access
            // First delete all existing roles
            MenuRole::where('menu_id', $id)->delete();
            
            // Then add the new roles
            if ($request->has('group_user') && is_array($request->group_user)) {
                foreach ($request->group_user as $groupId) {
                    $menuRole = new MenuRole();
                    $menuRole->menu_id = $menu->menu_id;
                    $menuRole->group_id = $groupId;
                    $menuRole->save();
                }
            }
            
            DB::commit();
            
            return redirect()->route('menus.index')
                ->with('success', 'Menu updated successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to update menu: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified menu from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $menu = Menu::findOrFail($id);
            
            // Check if menu has children
            $children = Menu::where('menu_parent', $id)->get();
            
            // Delete children first if any
            foreach ($children as $child) {
                // Delete child menu roles
                MenuRole::where('menu_id', $child->menu_id)->delete();
                
                // Delete child
                $child->delete();
            }
            
            // Delete menu roles
            MenuRole::where('menu_id', $id)->delete();
            
            // Delete menu
            $menu->delete();
            
            // Reorder remaining menus
            $this->reorderMenus($menu->menu_type, $menu->menu_parent);
            
            DB::commit();
            
            return redirect()->route('menus.index')
                ->with('success', 'Menu deleted successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to delete menu: ' . $e->getMessage());
        }
    }

    /**
     * Move menu up or down in order.
     *
     * @param  int  $id
     * @param  string  $direction
     * @return \Illuminate\Http\Response
     */
    public function move($id, $direction)
    {
        DB::beginTransaction();
        
        try {
            $menu = Menu::findOrFail($id);
            $currentOrder = $menu->menu_order;
            
            if ($direction === 'up' && $currentOrder > 1) {
                // Find the menu above
                $targetMenu = Menu::where('menu_type', $menu->menu_type)
                    ->where('menu_parent', $menu->menu_parent)
                    ->where('menu_order', $currentOrder - 1)
                    ->first();
                
                if ($targetMenu) {
                    // Swap orders
                    $targetMenu->menu_order = $currentOrder;
                    $targetMenu->save();
                    
                    $menu->menu_order = $currentOrder - 1;
                    $menu->save();
                }
            } elseif ($direction === 'down') {
                // Find the menu below
                $targetMenu = Menu::where('menu_type', $menu->menu_type)
                    ->where('menu_parent', $menu->menu_parent)
                    ->where('menu_order', $currentOrder + 1)
                    ->first();
                
                if ($targetMenu) {
                    // Swap orders
                    $targetMenu->menu_order = $currentOrder;
                    $targetMenu->save();
                    
                    $menu->menu_order = $currentOrder + 1;
                    $menu->save();
                }
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Menu order updated');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to update menu order: ' . $e->getMessage());
        }
    }

    /**
     * Get parent menus based on selected menu type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getParents(Request $request)
    {
        $type = $request->type;
        $menuId = $request->menu_id ?? 0;
        
        $parents = Menu::whereNull('menu_parent')
            ->orWhere('menu_parent', 0)
            ->where('menu_type', $type);
        
        // Exclude itself if editing
        if ($menuId > 0) {
            $parents->where('menu_id', '!=', $menuId);
        }
        
        $parents = $parents->pluck('menu_name', 'menu_id');
        
        return response()->json($parents);
    }

    /**
     * Display the menu roles management page.
     *
     * @return \Illuminate\Http\Response
     */
    public function roles()
    {
        $groups = Group::all();
        
        // Get all menus by type
        $menusByType = Menu::orderBy('menu_type')
            ->orderBy('menu_parent')
            ->orderBy('menu_order')
            ->whereNull('menu_parent')
            ->orWhere('menu_parent', 0)
            ->get()
            ->groupBy('menu_type');
        
        // Get child menus
        $childMenus = [];
        foreach (Menu::where('menu_parent', '>', 0)->orderBy('menu_order')->get() as $child) {
            $childMenus[$child->menu_parent][] = $child;
        }
        
        // Get menu access data
        $menuAccess = [];
        $roles = MenuRole::all();
        
        foreach ($roles as $role) {
            if (!isset($menuAccess[$role->menu_id])) {
                $menuAccess[$role->menu_id] = [];
            }
            
            $menuAccess[$role->menu_id][] = $role->group_id;
        }
        
        return view('menus.roles', compact('groups', 'menusByType', 'childMenus', 'menuAccess'));
    }

    /**
     * Get menu access for a specific group.
     *
     * @param  int  $groupId
     * @return \Illuminate\Http\Response
     */
    public function getGroupAccess($groupId)
    {
        $menuIds = MenuRole::where('group_id', $groupId)
            ->pluck('menu_id')
            ->toArray();
        
        return response()->json($menuIds);
    }

    /**
     * Save menu access for a group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveGroupAccess(Request $request)
    {
        $groupId = $request->group_id;
        $menuIds = $request->menu_ids ?? [];
        
        DB::beginTransaction();
        
        try {
            // Delete all existing roles for the group
            MenuRole::where('group_id', $groupId)->delete();
            
            // Add new roles
            foreach ($menuIds as $menuId) {
                $menuRole = new MenuRole();
                $menuRole->menu_id = $menuId;
                $menuRole->group_id = $groupId;
                $menuRole->save();
            }
            
            DB::commit();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle menu access for a group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toggleAccess(Request $request)
    {
        $menuId = $request->menu_id;
        $groupId = $request->group_id;
        $isGranted = $request->is_granted;
        
        try {
            if ($isGranted) {
                // Check if access already exists
                $exists = MenuRole::where('menu_id', $menuId)
                    ->where('group_id', $groupId)
                    ->exists();
                
                if (!$exists) {
                    $menuRole = new MenuRole();
                    $menuRole->menu_id = $menuId;
                    $menuRole->group_id = $groupId;
                    $menuRole->save();
                }
            } else {
                // Remove access
                MenuRole::where('menu_id', $menuId)
                    ->where('group_id', $groupId)
                    ->delete();
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign menu access from one group to others.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkAssignAccess(Request $request)
    {
        $sourceGroupId = $request->source_group_id;
        $targetGroupIds = $request->target_group_ids ?? [];
        $overwriteExisting = $request->has('overwrite_existing');
        
        DB::beginTransaction();
        
        try {
            // Get source group access
            $sourceMenuIds = MenuRole::where('group_id', $sourceGroupId)
                ->pluck('menu_id')
                ->toArray();
            
            foreach ($targetGroupIds as $targetGroupId) {
                if ($overwriteExisting) {
                    // Delete all existing access
                    MenuRole::where('group_id', $targetGroupId)->delete();
                }
                
                // Add new access
                foreach ($sourceMenuIds as $menuId) {
                    // Check if access already exists
                    $exists = MenuRole::where('menu_id', $menuId)
                        ->where('group_id', $targetGroupId)
                        ->exists();
                    
                    if (!$exists) {
                        $menuRole = new MenuRole();
                        $menuRole->menu_id = $menuId;
                        $menuRole->group_id = $targetGroupId;
                        $menuRole->save();
                    }
                }
            }
            
            DB::commit();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export menu roles data to CSV.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportRoles()
    {
        $groups = Group::all();
        $menus = Menu::orderBy('menu_type')
            ->orderBy('menu_parent')
            ->orderBy('menu_order')
            ->get();
        
        // Get menu access data
        $menuAccess = [];
        $roles = MenuRole::all();
        
        foreach ($roles as $role) {
            if (!isset($menuAccess[$role->menu_id])) {
                $menuAccess[$role->menu_id] = [];
            }
            
            $menuAccess[$role->menu_id][] = $role->group_id;
        }
        
        // Create CSV content
        $headers = ['Menu ID', 'Menu Name', 'Menu Type'];
        
        foreach ($groups as $group) {
            $headers[] = $group->group_name;
        }
        
        $csvContent = implode(',', $headers) . "\n";
        
        foreach ($menus as $menu) {
            $row = [
                $menu->menu_id,
                '"' . str_replace('"', '""', $menu->menu_name) . '"',
                $menu->menu_type
            ];
            
            foreach ($groups as $group) {
                $hasAccess = isset($menuAccess[$menu->menu_id]) && 
                             in_array($group->group_id, $menuAccess[$menu->menu_id]);
                
                $row[] = $hasAccess ? 'Yes' : 'No';
            }
            
            $csvContent .= implode(',', $row) . "\n";
        }
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="menu_roles_' . date('Y-m-d') . '.csv"',
        ];
        
        return Response::make($csvContent, 200, $headers);
    }

    /**
     * Reorder menus after deletion.
     *
     * @param  string  $type
     * @param  int  $parent
     * @return void
     */
    private function reorderMenus($type, $parent)
    {
        $menus = Menu::where('menu_type', $type)
            ->where('menu_parent', $parent)
            ->orderBy('menu_order')
            ->get();
        
        $order = 1;
        
        foreach ($menus as $menu) {
            $menu->menu_order = $order++;
            $menu->save();
        }
    }
}