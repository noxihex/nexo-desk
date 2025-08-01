<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        // Exemplo de dados dinâmicos para dropdowns de grupo, setor e empresa
        $grupos = ['Grupo 1', 'Grupo 2', 'Grupo 3']; // Exemplo de grupos
        $setores = ['NOC', 'Projetos', 'Infra']; // Exemplo de setores
        $empresas = ['Empresa A', 'Empresa B', 'Empresa C']; // Exemplo de empresas

        return view('auth.register', compact('grupos', 'setores', 'empresas'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'grupo' => ['required', 'string'], // Validação do grupo
            'setor' => ['required', 'string'], // Validação do setor
            'empresa' => ['required', 'string', 'max:255'], // Validação do campo empresa
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // Criar o usuário com os novos campos, incluindo empresa
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'grupo' => $data['grupo'],  // Salvando o campo grupo
            'setor' => $data['setor'],  // Salvando o campo setor
            'empresa' => $data['empresa'],  // Salvando o campo empresa
        ]);

        // Atribuir o papel "analista" ao usuário recém-registrado
        $user->assignRole('analista');

        return $user;
    }
}
