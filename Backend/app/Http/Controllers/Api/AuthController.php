<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
        ]);
        $user = User::create($data);
        $token = $user->createToken('mindease-web')->plainTextToken;
        return response()->json(['success'=>true,'message'=>'Registration successful.','user'=>$user,'token'=>$token],201);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email'=>'required|email','password'=>'required|string']);
        $user = User::where('email',$data['email'])->first();
        if (!$user || !Hash::check($data['password'],$user->password)) {
            throw ValidationException::withMessages(['email'=>['Incorrect email or password.']]);
        }
        $token = $user->createToken('mindease-web')->plainTextToken;
        return response()->json(['success'=>true,'message'=>'Login successful.','user'=>$user,'token'=>$token]);
    }

    public function me(Request $request){ return response()->json(['user'=>$request->user()]); }

    public function logout(Request $request){
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['success'=>true,'message'=>'Logged out successfully.']);
    }
}
