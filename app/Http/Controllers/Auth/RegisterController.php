<?php
namespace FDamel\Http\Controllers\Auth;
use FDamel\User;
use Validator;
use FDamel\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use FDamel\ActivationService;
class RegisterController extends Controller{
    use RegistersUsers;
    protected $redirectTo = '/home';
    protected $activationService;
    public function __construct(ActivationService $activationService){
        $this->middleware('guest');
        $this->activationService = $activationService;
    }
    protected function validator(array $data){
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }
    protected function create(array $data){
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
    public function register(Request $request){
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }
        $user = $this->create($request->all());
        $this->activationService->sendActivationMail($user);
        return redirect('/login')->with('status', 'We sent you an activation code. Check your email.');
    }
    public function activateUser($token){
        if ($user = $this->activationService->activateUser($token)) {
            auth()->login($user);
            return redirect($this->redirectPath());
        }
        abort(404);
    }
}