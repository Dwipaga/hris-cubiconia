<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\EmailHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && md5($request->password) === $user->password) {

            Auth::login($user);
            if ($user->group_id == 2) {

                return redirect()->route('dashboard')
                    ->with('success', 'Login successful!');
            } else if ($user->group_id != 7){
                return redirect()->route('evaluations.index')
                    ->with('success', 'Login successful!');
            } else {
                return redirect()->route('employee-data.create')
                    ->with('success', 'Login successful!');
            }
        }

        return back()->with('error', 'Invalid credentials');
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/')->with('success', 'Logged out successfully');
    }

    /**
     * Send password reset link
     */
    // public function forgotPassword(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if ($user) {
    //         $token = md5(time() . $user->email);
    //         $html = view('users.emailforgot', ['token' => $token])->render();
    //         $sendEmail = EmailHelper::send(
    //             $html,
    //             $user->email,
    //             'Forgot Password',
    //             $user->firstname . ' ' . $user->lastname
    //         );
    //         // dd($sendEmail);

    //         if (!$sendEmail) {
    //             return back()->with('error', 'Failed to send email');
    //         };

    //         $user->update(['token' => $token]);


    //         return back()->with('status', 'Password reset link has been sent!');
    //     }

    //     return back()->with('error', 'Email not found');
    // }

    /**
     * Reset password
     */
    public function resetPassword($token, Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6'
        ]);



        $user = User::where('email', $request->email)
            ->where('token', $token)
            ->first();
        $user->password = md5($request->password);
        $user->save();

        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        return redirect()->route('login')->with('success', 'Password reset successful!');
    }
}
