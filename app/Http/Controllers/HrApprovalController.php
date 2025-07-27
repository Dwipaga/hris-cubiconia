<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HrApprovalController extends Controller
{
    public function index()
    {
        $users = User::where('group_id', 7)->get();
        return view('hr-approval.index', compact('users'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $groups = Group::all();
        return view('hr-approval.form', compact('user', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'dokumen_kontrak' => 'nullable|file|mimes:pdf',
            'group_id' => 'required|exists:groups,group_id'
        ]);

        $user = User::findOrFail($id);

        if ($request->hasFile('dokumen_kontrak')) {
            $fileName = 'signed_contract_' . $user->user_id . '_' . time() . '.pdf';
            $signedContractPath = $request->file('dokumen_kontrak')->storeAs('documents/signed_contracts', $fileName, 'public');
            $user->dokumen_kontrak = basename($signedContractPath);
        }

        $user->group_id = $request->group_id;
        $user->save();

        return redirect()->route('hr-approval.index')->with('success', 'Data karyawan berhasil di-approve.');
    }
}
