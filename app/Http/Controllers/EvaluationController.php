<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Penilaian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class EvaluationController extends Controller
{
    public function index()
    {
        $userGroupId = Auth::user()->group_id;
        $userId = Auth::user()->user_id;
        $userDivisi = Auth::user()->divisi;
        $currentMonth = Carbon::now()->startOfMonth();
    
        // Simplified query based on your objective: group 2&3 assess group 4, group 4 views own results
        if ($userGroupId == 2) {
            // Group 2 assesses all users in group 4
            $asesiUsers = User::select('user_id', 'firstname', 'group_id', 'divisi')
                ->where('group_id', 4)
                ->get();
                
        } elseif ($userGroupId == 3) {
            // Group 3 assesses users in group 4 with same division
            $asesiUsers = User::select('user_id', 'firstname', 'group_id', 'divisi')
                ->where('group_id', 4)
                ->where('divisi', $userDivisi)
                ->get();
                
        } elseif ($userGroupId == 4) {
            // Group 4 can only view their own evaluation results
            $asesiUsers = User::select('user_id', 'firstname', 'group_id', 'divisi')
                ->where('user_id', $userId)
                ->get();
                
        } else {
            // Fallback for other groups - use penilaian relationships
            $asesiGroupIds = Penilaian::where('asesor_id', $userGroupId)
                ->where('is_active', true)
                ->pluck('asesi_id');
                
            $asesiUsers = User::select('user_id', 'firstname', 'group_id', 'divisi')
                ->whereIn('group_id', $asesiGroupIds)
                ->get();
        }
    
        // Early return if no users found
        if ($asesiUsers->isEmpty()) {
            return view('evaluations.index', [
                'penilaians' => collect(),
                'asesiUsers' => collect(),
                'evaluationResults' => collect(),
            ]);
        }
    
        // Get all evaluations in single query with eager loading
        // For group 4, we need to get evaluations WHERE they are the asesi (being evaluated)
        // For other groups, we get evaluations WHERE they are the penilai (doing the evaluation)
        $evaluationQuery = Evaluation::with([
            'asesiTernilai' => function ($query) {
                $query->select('user_id', 'firstname', 'group_id');
            },
            'penilai' => function ($query) {
                $query->select('user_id', 'firstname');
            }
        ]);
    
        if ($userGroupId == 4) {
            // For group 4, get evaluations where they are being evaluated (asesi)
            $evaluationResults = $evaluationQuery
                ->where('asesi_ternilai_id', $userId)
                ->where('bulan_penilaian', $currentMonth)
                ->select([
                    'id as evaluation_id',
                    'asesi_ternilai_id',
                    'penilai_id',
                    'total_akhir as total_score',
                    'bulan_penilaian',
                    'created_at',
                    'updated_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // For other groups, get evaluations where they are the evaluator (penilai)
            $evaluationResults = $evaluationQuery
                ->where('penilai_id', $userId)
                ->where('bulan_penilaian', $currentMonth)
                ->whereIn('asesi_ternilai_id', $asesiUsers->pluck('user_id'))
                ->select([
                    'id as evaluation_id',
                    'asesi_ternilai_id',
                    'penilai_id',
                    'total_akhir as total_score',
                    'bulan_penilaian',
                    'created_at',
                    'updated_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();
        }
    
        // Mark users who have evaluations using collection method (no additional queries)
        $evaluatedUserIds = $evaluationResults->pluck('asesi_ternilai_id');
        $asesiUsers->each(function ($user) use ($evaluatedUserIds) {
            $user->has_evaluation = $evaluatedUserIds->contains($user->user_id);
        });
    
        // Set group names efficiently without additional queries
        $asesiUsers->each(function ($user) use ($userGroupId) {
            if ($userGroupId == 3 || $userGroupId == 4) {
                $user->group_name = $user->divisi ?? 'N/A';
            } else {
                // For group 2 or others, you can set group name as needed
                $user->group_name = $user->divisi ?? 'N/A'; // or get from cache/config
            }
        });
    
        // Create empty penilaians collection since it's not actually used in the logic
        $penilaians = collect();
    
        return view('evaluations.index', compact(
            'penilaians',
            'asesiUsers',
            'evaluationResults',
        ));
    }
    public function create($asesi_id)
    {
        $asesi = User::where('user_id', $asesi_id)->first();
        $penilai = Auth::user();
        $currentMonth = Carbon::now()->startOfMonth();

        // Check if evaluation already exists for this month
        $existingEvaluation = Evaluation::where('asesi_ternilai_id', $asesi->user_id)
            ->where('penilai_id', $penilai->user_id)
            ->where('bulan_penilaian', $currentMonth)
            ->exists();

        if ($existingEvaluation) {
            return redirect()->route('evaluations.index')
                ->with('error', 'Evaluation for this user has already been submitted for this month');
        }

        $penilaians = Penilaian::where('asesi_id', $asesi->group_id)
            ->where('asesor_id', $penilai->group_id)
            ->where('is_active', true)
            ->get();

        if ($penilaians->isEmpty()) {
            abort(403, 'Unauthorized: You are not allowed to evaluate this user');
        }

        return view('evaluations.create', compact('asesi', 'penilaians'));
    }

    public function store(Request $request, $asesi_id)
    {
        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'required|numeric|min:1|max:100'
        ]);

        $asesi = User::where('user_id', $asesi_id)->first();
        $penilai = Auth::user();
        $currentMonth = Carbon::now()->startOfMonth();

        // Double-check to prevent race conditions
        $existingEvaluation = Evaluation::where('asesi_ternilai_id', $asesi->id)
            ->where('penilai_id', $penilai->id)
            ->where('bulan_penilaian', $currentMonth)
            ->exists();

        if ($existingEvaluation) {
            return redirect()->route('evaluations.index')
                ->with('error', 'Evaluation for this user has already been submitted for this month');
        }

        $penilaians = Penilaian::where('asesi_id', $asesi->group_id)
            ->where('asesor_id', $penilai->group_id)
            ->where('is_active', true)
            ->get();

        $detail_penilaian = [];
        $total_score = 0;
        $total_bobot = 0;

        foreach ($penilaians as $penilaian) {
            $score = $request->scores[$penilaian->id];
            $detail_penilaian[] = [
                'penilaian_id' => $penilaian->id,
                'hasil' => $score
            ];
            $total_score += $score * $penilaian->bobot;
            $total_bobot += $penilaian->bobot;
        }

        $total_akhir = $total_bobot > 0 ? $total_score / $total_bobot : 0;

        Evaluation::create([
            'asesi_ternilai_id' => $asesi->user_id,
            'penilai_id' => $penilai->user_id,
            'detail_penilaian' => $detail_penilaian,
            'total_akhir' => $total_akhir,
            'bulan_penilaian' => $currentMonth
        ]);

        return redirect()->route('evaluations.index')
            ->with('success', 'Evaluation submitted successfully');
    }

    public function showEvaluation($asesi_id)
    {
        $asesi = User::where('user_id', $asesi_id)->first();

        $penilai = Auth::user();
        $currentMonth = Carbon::now()->startOfMonth();

        $evaluation = Evaluation::where('asesi_ternilai_id', $asesi->user_id)
            ->where('penilai_id', $penilai->user_id)
            ->where('bulan_penilaian', $currentMonth)
            ->first();

        if (!$evaluation) {
            return response()->json(['error' => 'Evaluation not found'], 404);
        }

        // Fetch penilaian details for display
        $penilaianDetails = collect($evaluation->detail_penilaian)->map(function ($detail) {
            $penilaian = Penilaian::find($detail['penilaian_id']);
            return [
                'penilaian' => $penilaian ? $penilaian->penilaian : 'Unknown',
                'bobot' => $penilaian ? $penilaian->bobot : 0,
                'score' => $detail['hasil']
            ];
        });

        return response()->json([
            'asesi_name' => $asesi->firstname,
            'month' => $currentMonth->format('F Y'),
            'total_score' => $evaluation->total_akhir,
            'details' => $penilaianDetails
        ]);
    }

    public function exportPdf($asesi_id)
    {
        $asesi = User::where('user_id', $asesi_id)->first();
        $penilai = Auth::user();
        $currentMonth = Carbon::now()->startOfMonth();

        $evaluation = Evaluation::where('asesi_ternilai_id', $asesi->user_id);

        if (Auth::user()->group_id == 2 || Auth::user()->group_id == 3) {

            $evaluation = $evaluation->where('penilai_id', $penilai->user_id);
        } 
            $evaluation = $evaluation->where('bulan_penilaian', $currentMonth)
            ->first();

        if (!$evaluation) {
            return redirect()->route('evaluations.index')
                ->with('error', 'Evaluation not found for PDF export');
        }

        // Fetch group name for division
        $group = \App\Models\Group::where('group_id', $asesi->group_id)->first();
        $group_name = $group ? $group->group_name : 'N/A';

        // Fetch penilaian details for PDF
        $penilaianDetails = collect($evaluation->detail_penilaian)->map(function ($detail) {
            $penilaian = Penilaian::find($detail['penilaian_id']);
            return [
                'penilaian' => $penilaian ? $penilaian->penilaian : 'Unknown',
                'bobot' => $penilaian ? $penilaian->bobot : 0,
                'score' => $detail['hasil']
            ];
        });

        $data = [
            'asesi_name' => $asesi->firstname,
            'month' => $currentMonth->format('F Y'),
            'total_score' => $evaluation->total_akhir,
            'details' => $penilaianDetails,
            'group_name' => $group_name,
            'logo_path' => public_path('assets/img/cubiconia.png'),
            'company_name' => 'PT. CUBICONIA KANAYA PRATAMA',
            'address' => 'Signature Park Grande CTB/L1/03, MT Haryono St No.Kav. 20, Cawang, Jakarta 16360',
            'phone' => 'Phone: 0822-2118-8192',
            'email' => 'Email: hello@cubiconia.com',
            'title' => 'LEMBAR HASIL EVALUASI KINERJA KARYAWAN'
        ];

        $pdf = Pdf::loadView('evaluations.pdf', $data);
        return $pdf->download('Evaluation_' . $asesi->firstname . '_' . $currentMonth->format('Y_m') . '.pdf');
    }
}
