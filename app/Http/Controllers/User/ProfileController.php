<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load(['profile', 'rank', 'kycDocuments']);

        return Inertia::render('User/Profile/Index', [
            'user' => $user,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|confirmed|min:8',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated.');
    }

    public function updateTransactionPassword(Request $request)
    {
        $request->validate([
            'current_transaction_password' => 'required',
            'transaction_password' => 'required|confirmed|min:6',
        ]);

        if (!Hash::check($request->current_transaction_password, $request->user()->transaction_password)) {
            return back()->withErrors(['current_transaction_password' => 'Invalid current transaction password.']);
        }

        $request->user()->update([
            'transaction_password' => Hash::make($request->transaction_password),
        ]);

        return back()->with('success', 'Transaction password updated.');
    }
}
