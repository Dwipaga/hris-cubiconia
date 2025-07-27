<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GeneralService
{
    /**
     * Authentication check
     */
    public function auth()
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }
    }

    /**
     * Get menu based on type
     */
    public function getMenu($type)
    {
        $result = [];
        
        if (Auth::check()) {
            $group_id = Auth::user()->group_id;
            
            // Get parent menus
            $menu = DB::table('menus')
                ->join('menu_roles', 'menus.menu_id', '=', 'menu_roles.menu_id')
                ->where('menus.menu_type', $type)
                ->where('menus.menu_parent', 0)
                ->where('menus.status', 1)
                ->where(function ($query) use ($group_id) {
                    $query->where('menu_roles.group_id', $group_id)
                        ->orWhere('menu_roles.group_id', 0);
                })
                ->orderBy('menus.menu_order', 'ASC')
                ->get();
            
            // Get submenu for each parent menu
            foreach ($menu as $key => $value) {
                $submenu = DB::table('menus')
                    ->join('menu_roles', 'menus.menu_id', '=', 'menu_roles.menu_id')
                    ->where('menus.menu_parent', $value->menu_id)
                    ->where('menus.status', 1)
                    ->where(function ($query) use ($group_id) {
                        $query->where('menu_roles.group_id', $group_id)
                            ->orWhere('menu_roles.group_id', 0);
                    })
                    ->orderBy('menus.menu_order', 'ASC')
                    ->get();
                
                $result[$key] = [
                    'root' => $value,
                    'child' => $submenu
                ];
            }
            
            return $result;
        } else {
            // Route to homepage if not in asesi controller
            $route = request()->route();
            if ($route && $route->getActionName() != 'App\Http\Controllers\AsesiController') {
                return redirect('/');
            }
        }
    }

    /**
     * Authenticate menu access
     */
    public function authMenu($type)
    {
        $group_id = Auth::user()->group_id;
        $segment1 = request()->segment(1);
        $segment2 = request()->segment(2);
        
        $url = $segment1;
        if ($segment1 && $segment2) {
            $url = $segment1 . '/' . $segment2;
        }
        
        $check = DB::table('menus')
            ->join('menu_roles', 'menus.menu_id', '=', 'menu_roles.menu_id')
            ->where('menus.menu_url', $url)
            ->where('menus.menu_type', $type)
            ->where('menus.status', 1)
            ->where(function ($query) use ($group_id) {
                $query->where('menu_roles.group_id', $group_id)
                    ->orWhere('menu_roles.group_id', 4);
            })
            ->first();
        
        if (!$check) {
            return redirect()->route('main.forbidden');
        }
    }

    /**
     * Get notifications
     */
    public function getNotification($type)
    {
        return DB::table('notifikasi')
            ->orderBy('created', 'DESC')
            ->get();
    }

    /**
     * Get messages
     */
    public function getMessage()
    {
        $result = [];
        
        // $messages = DB::table('message')
        //     ->select('message.message_id', 'message.message', 'message.status', 'message.created', 
        //             'user.photo', 'user.firstname', 'user.lastname')
        //     ->join('user', 'message.sender_id', '=', 'user.user_id')
        //     ->where('message.receiver_id', Auth::id())
        //     ->orderBy('message.created', 'DESC')
        //     ->get();
        
        // foreach ($messages as $value) {
        //     $timeago = $this->timeago($value->created);
        //     $result[] = [
        //         'message_id' => $value->message_id,
        //         'message' => $value->message,
        //         'photo' => $value->photo,
        //         'sender' => $value->firstname . ' ' . $value->lastname,
        //         'created' => $value->created,
        //         'timeago' => $timeago,
        //         'status' => $value->status,
        //     ];
        // }
        
        return $result;
    }

    /**
     * Calculate time ago in Indonesian
     */
    public function timeago($date)
    {
        $timestamp = strtotime($date);
        
        $strTime = ["detik", "menit", "jam", "hari", "bulan", "tahun"];
        $length = ["60", "60", "24", "30", "12", "10"];
        
        $currentTime = time();
        if ($currentTime >= $timestamp) {
            $diff = time() - $timestamp;
            for ($i = 0; $diff >= $length[$i] && $i < count($length) - 1; $i++) {
                $diff = $diff / $length[$i];
            }
            
            $diff = round($diff);
            return $diff . " " . $strTime[$i] . " yang lalu";
        }
    }

    /**
     * Upload image with compression
     */
    public function upload_image($file, $filename, $path)
    {
        $this->create_dir($path, 0755);
        
        $img = \Image::make($file['tmp_name']);
        $img->save(public_path($path . $filename), 40);
        
        return url($path . $filename);
    }

    /**
     * Create directory if not exists
     */
    public function create_dir($path, $code)
    {
        if (!file_exists(public_path($path))) {
            mkdir(public_path($path), $code, true);
        }
    }

    /**
     * Check NIK via API
     */
    public function checkNik($nik = "")
    {
        $client = new \GuzzleHttp\Client();
        
        try {
            $response = $client->request('GET', env('URL_DEV_SIKI') . 'lisensi-api/ref-permohonan-skk', [
                'query' => ['nik' => '6171051806930002'],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'App-Id' => env('URL_DEV_SIKI_APP_ID'),
                    'App-Key' => env('URL_DEV_SIKI_APP_KEY')
                ]
            ]);
            
            return json_decode($response->getBody());
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Check if site is in maintenance mode
     */
    public function checkMaintenance()
    {
        $maintenance = DB::table('sys_reference')
            ->where('ref_category', 'MAINTENANCE_STATUS')
            ->first();
        
        if ($maintenance && $maintenance->ref_value) {
            return redirect()->route('main.maintenance');
        }
    }
}