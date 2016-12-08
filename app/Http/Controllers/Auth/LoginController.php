<?php
namespace FDamel\Http\Controllers\Auth;
use FDamel\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;


use Illuminate\Http\Request;
use FDamel\ActivationService;


class LoginController extends Controller{

    use AuthenticatesUsers;
    protected $redirectTo = '/home';



    public function __construct(ActivationService $activationService)
    {
        $this->middleware('guest', ['except' => 'logout']);
        $this->activationService = $activationService;
    }


    public function authenticated(Request $request, $user){
        if (!$user->activated) {
            $this->activationService->sendActivationMail($user);
            auth()->logout();
            return back()->with('warning', 'You need to confirm your account. We have sent you an activation code, please check your email.');
        }
        return redirect()->intended($this->redirectPath());
    }
}