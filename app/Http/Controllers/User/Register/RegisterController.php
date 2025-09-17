<?php

namespace App\Http\Controllers\User\Register;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    protected $expiresIn;

    public function __construct()
    {
        $this->expiresIn = config('jwt.expiration.user');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

         $token = Auth::login($user);

         return (new UserResource($user))->additional([
             'meta' => [
                 'access_token' => $token,
                 'token_type' => 'bearer',
                 'expires_in' => $this->expiresIn,
             ]
         ]);
    }
}
