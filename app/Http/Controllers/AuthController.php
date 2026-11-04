<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $d = $r->validate(['email' => 'required|email', 'password' => 'required']);
        $user = User::where('email', $d['email'])->first();
        if (!$user || !Hash::check($d['password'], $user->password)) {
            return response()->json(['message' => 'Email or password is incorrect.'], 422);
        }
        return ['token' => $user->createToken('spa')->plainTextToken, 'user' => $user->load('employee')];
    }
    public function me(Request $r) { return $r->user()->load('employee'); }
    public function logout(Request $r) { $r->user()->currentAccessToken()->delete(); return response()->noContent(); }
}
