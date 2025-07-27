<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class UsersController extends Controller
{
    public function index()
    {
        try {
            $users = User::join('groups', 'users.group_id', '=', 'groups.group_id')
                ->where('users.group_id', '!=', '7')
                ->paginate(10);
            
            return view('user.index', compact('users'));
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengambil data pengguna: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $groups = DB::table('groups')->get();
            $labels = [];
            $data = [];
            
            return view('user.form', ['groups' => $groups, 'user' => null, 'labels' => $labels, 'data' => $data]);
        } catch (Exception $e) {
            return redirect()->route('user.index')
                ->with('error', 'Terjadi kesalahan saat memuat form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                'username' => 'required|string|max:50|unique:users',
                'tanggal_lahir' => 'nullable|date',
                'tanggal_masuk' => 'nullable|date',
                'tanggal_akhir_kontrak' => 'nullable|date',
                'npwp' => 'required|string|max:20|unique:users',
                'jenis_kontrak' => 'nullable|string|max:50',
                'dokumen_kontrak' => 'nullable|file|max:100',
                'group_id' => 'required',
                'nik' => 'required',
                'divisi' => 'required',
                'alamat' => 'required',
                'tempat_lahir' => 'required',
                'email' => 'required|email|unique:users',
                'firstname' => 'required|string|max:100',
                'lastname' => 'required|string|max:100',
                'phone' => 'required|string|max:20',
                'password' => 'required|string|min:8|confirmed'
            ]);

            if ($validated->fails()) {
                return redirect()->back()
                    ->withErrors($validated)
                    ->withInput()
                    ->with('error', 'Validasi gagal. Periksa kembali data yang dimasukkan.');
            }

            $validated = $validated->validated();

            // Handle photo upload
            if ($request->hasFile('photo')) {
                try {
                    $file = $request->file('photo');
                    $originalName = Auth::user()->user_id;
                    $extension = $file->getClientOriginalExtension();
                    $photoPath = 'user-photos/' . $originalName . '.' . $extension;
                    
                    if (!Storage::disk('public')->put($photoPath, file_get_contents($file))) {
                        throw new Exception('Gagal mengupload foto');
                    }
                    
                    $validated['photo'] = $originalName . '.' . $extension;
                } catch (Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Gagal mengupload foto: ' . $e->getMessage());
                }
            }

            // Handle dokumen kontrak upload
            if ($request->hasFile('dokumen_kontrak')) {
                try {
                    $fileName = 'signed_contract_' . rand() . '_' . time() . '.pdf';
                    $signedContractPath = $request->file('dokumen_kontrak')->storeAs('documents/signed_contracts', $fileName, 'public');
                    
                    if (!$signedContractPath) {
                        throw new Exception('Gagal mengupload dokumen kontrak');
                    }
                    
                    $validated['dokumen_kontrak'] = $signedContractPath;
                } catch (Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Gagal mengupload dokumen kontrak: ' . $e->getMessage());
                }
            }

            // Prepare user data
            $userData = [
                'username' => $validated['username'],
                'email' => $validated['email'],
                'group_id' => $validated['group_id'],
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'],
                'phone' => $validated['phone'],
                'password' => md5($validated['password']),
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? null,
                'tanggal_akhir_kontrak' => $validated['tanggal_akhir_kontrak'] ?? null,
                'npwp' => $validated['npwp'],
                'nik' => $validated['nik'],
                'alamat' => $validated['alamat'],
                'divisi' => $validated['divisi'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'jenis_kontrak' => $validated['jenis_kontrak'] ?? null,
                'dokumen_kontrak' => $validated['dokumen_kontrak'] ?? null,
            ];

            if (isset($validated['photo'])) {
                $userData['photo'] = $validated['photo'];
            }

            // Create user
            $user = User::create($userData);
            
            if (!$user) {
                throw new Exception('Gagal membuat pengguna baru');
            }

            // Generate employee ID
            $divisi = $user->divisi;
            if ($divisi == 'PROGRAMMER') {
                $divisi = 'PRG';
            } else if ($divisi == 'CONSULTANT') {
                $divisi = 'CST';
            }

            if ($user->group_id == 2) {
                $divisi = 'HRD';
            }
            if ($user->group_id == 3) {
                $divisi = 'LEAD';
            }
            
            $id_karyawan = 'CBC-' . $divisi . '-' . date('Ymd') . '-' . rand(1000, 9999);
            
            if (!$user->update(['id_karyawan' => $id_karyawan])) {
                throw new Exception('Gagal mengupdate ID karyawan');
            }

            return redirect()->route('user.index')
                ->with('success', 'Pengguna berhasil dibuat.');

        } catch (Exception $e) {
            // Clean up uploaded files if user creation failed
            if (isset($validated['photo']) && Storage::disk('public')->exists('user-photos/' . $validated['photo'])) {
                Storage::disk('public')->delete('user-photos/' . $validated['photo']);
            }
            
            if (isset($validated['dokumen_kontrak']) && Storage::disk('public')->exists($validated['dokumen_kontrak'])) {
                Storage::disk('public')->delete($validated['dokumen_kontrak']);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat pengguna: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $groups = DB::table('groups')->get();
            $user = User::findOrFail($id);

            $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();

            $evaluations = DB::table('evaluations')
                ->selectRaw('CONCAT(groups.group_name, "(", users.firstname, " ", users.lastname, ")") as penilai, MONTH(bulan_penilaian) as bulan, YEAR(bulan_penilaian) as tahun, total_akhir')
                ->join('users', 'evaluations.penilai_id', 'users.user_id')
                ->join('groups', 'users.group_id', 'groups.group_id')
                ->where('asesi_ternilai_id', $user->user_id)
                ->where('bulan_penilaian', '>=', $sixMonthsAgo)
                ->orderBy('bulan_penilaian')
                ->get();

            // Get unique penilai untuk color mapping
            $uniquePenilai = $evaluations->pluck('penilai')->unique()->values();

            // Color palette
            $colorPalette = [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF9F40'
            ];

            // Create penilai color mapping
            $penilaiColors = [];
            foreach ($uniquePenilai as $index => $penilai) {
                $penilaiColors[$penilai] = $colorPalette[$index % count($colorPalette)];
            }

            // Prepare chart data
            $labels = [];
            $datasets = [];

            // Group evaluations by penilai
            $groupedEvaluations = $evaluations->groupBy('penilai');

            foreach ($groupedEvaluations as $penilai => $penilaiEvaluations) {
                $dataPoints = [];
                $allLabels = [];

                // Create all month labels from last 6 months
                for ($i = 5; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i)->startOfMonth();
                    $monthLabel = $date->translatedFormat('F Y');
                    $allLabels[] = $monthLabel;

                    // Find evaluation for this month
                    $evaluation = $penilaiEvaluations->first(function ($e) use ($date) {
                        return $e->bulan == $date->month && $e->tahun == $date->year;
                    });

                    $dataPoints[] = $evaluation ? $evaluation->total_akhir : null;
                }

                // Set labels (only need once)
                if (empty($labels)) {
                    $labels = $allLabels;
                }

                // Create dataset for this penilai
                $datasets[] = [
                    'label' => $penilai,
                    'data' => $dataPoints,
                    'borderColor' => $penilaiColors[$penilai],
                    'backgroundColor' => $penilaiColors[$penilai] . '20',
                    'fill' => false,
                    'tension' => 0.1,
                    'pointBackgroundColor' => $penilaiColors[$penilai],
                    'pointBorderColor' => $penilaiColors[$penilai],
                    'pointHoverBackgroundColor' => $penilaiColors[$penilai],
                    'pointHoverBorderColor' => $penilaiColors[$penilai],
                ];
            }

            // Legacy data support
            $data = [];
            foreach ($evaluations as $e) {
                $data[] = $e->total_akhir;
            }

            return view('user.form', compact('user', 'groups', 'labels', 'data', 'datasets', 'penilaiColors', 'uniquePenilai'));

        } catch (Exception $e) {
            return redirect()->route('user.index')
                ->with('error', 'Terjadi kesalahan saat memuat data pengguna: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'username' => "required|string|max:50|unique:users,username,{$id},user_id",
                'email' => "required|email|unique:users,email,{$id},user_id",
                'firstname' => 'required|string|max:100',
                'lastname' => 'required|string|max:100',
                'phone' => 'required|string|max:20',
                'group_id' => 'required',
                'divisi' => 'required',
                'nik' => 'required',
                'alamat' => 'required',
                'npwp' => "nullable|string|max:20|unique:users,npwp,{$id},user_id",
                'tanggal_lahir' => 'nullable|date',
                'tanggal_masuk' => 'nullable|date',
                'tanggal_akhir_kontrak' => 'nullable|date',
                'jenis_kontrak' => 'nullable|string|max:50',
                'photo' => 'nullable|image|mimes:jpg,jpeg,png',
                'dokumen_kontrak' => 'nullable|file',
                'password' => 'nullable|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validasi gagal. Periksa kembali data yang dimasukkan.');
            }

            $validated = $validator->validated();
            
            // Remove password from validated data if not provided
            if (!$request->filled('password')) {
                unset($validated['password']);
                unset($validated['password_confirmation']);
            }
            
            $user->fill($validated);

            // Handle photo upload
            if ($request->hasFile('photo')) {
                try {
                    // Delete old photo if exists
                    if ($user->photo && Storage::disk('public')->exists('user-photos/' . $user->photo)) {
                        Storage::disk('public')->delete('user-photos/' . $user->photo);
                    }
                    
                    $photoName = $user->user_id . '.' . $request->photo->extension();
                    
                    if (!$request->photo->storeAs('user-photos', $photoName, 'public')) {
                        throw new Exception('Gagal mengupload foto baru');
                    }
                    
                    $user->photo = $photoName;
                } catch (Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Gagal mengupload foto: ' . $e->getMessage());
                }
            }

            // Handle dokumen kontrak upload
            if ($request->hasFile('dokumen_kontrak')) {
                try {
                    $fileName = 'signed_contract_' . $user->user_id . '_' . time() . '.pdf';
                    $signedContractPath = $request->file('dokumen_kontrak')->storeAs('documents/signed_contracts', $fileName, 'public');
                    
                    if (!$signedContractPath) {
                        throw new Exception('Gagal mengupload dokumen kontrak');
                    }
                    
                    $user->dokumen_kontrak = $signedContractPath;
                } catch (Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Gagal mengupload dokumen kontrak: ' . $e->getMessage());
                }
            }

            // Handle password update (only if provided)
            if ($request->filled('password')) {
                $user->password = md5($request->password);
            }

            if (!$user->save()) {
                throw new Exception('Gagal menyimpan perubahan data pengguna');
            }

            return redirect()->route('user.index')
                ->with('success', 'Pengguna berhasil diperbarui.');

        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui pengguna: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Delete photo if exists
            if ($user->photo && Storage::disk('public')->exists('user-photos/' . $user->photo)) {
                try {
                    Storage::disk('public')->delete('user-photos/' . $user->photo);
                } catch (Exception $e) {
                    // Log error but continue with user deletion
                    \Log::warning('Gagal menghapus foto pengguna: ' . $e->getMessage());
                }
            }

            // Delete dokumen kontrak if exists
            if ($user->dokumen_kontrak && Storage::disk('public')->exists($user->dokumen_kontrak)) {
                try {
                    Storage::disk('public')->delete($user->dokumen_kontrak);
                } catch (Exception $e) {
                    // Log error but continue with user deletion
                    \Log::warning('Gagal menghapus dokumen kontrak: ' . $e->getMessage());
                }
            }

            if (!$user->delete()) {
                throw new Exception('Gagal menghapus pengguna dari database');
            }

            return redirect()->route('user.index')
                ->with('success', 'Pengguna berhasil dihapus.');

        } catch (Exception $e) {
            return redirect()->route('user.index')
                ->with('error', 'Terjadi kesalahan saat menghapus pengguna: ' . $e->getMessage());
        }
    }
}