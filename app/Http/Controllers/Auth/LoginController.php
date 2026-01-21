<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // If user is already authenticated, redirect to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // Attempt authentication
            $credentials = $request->only('email', 'password');
            $remember = $request->boolean('remember');

            if (Auth::attempt($credentials, $remember)) {
                // Regenerate session to prevent session fixation
                $request->session()->regenerate();

                return $this->authenticated($request, Auth::user());
            }

            // Authentication failed - return with error
            return back()->withErrors([
                'email' => 'Email atau password tidak valid.',
            ])->withInput($request->except('password'));
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['email' => 'Terjadi kesalahan saat login. Silakan coba lagi.']);
        }
    }

    /**
     * Log the current user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Check if user is active (is_active is cast as boolean in User model)
        if (!$user->is_active) {
            Auth::logout();
            \Log::info('Login blocked: User is inactive', [
                'user_id' => $user->id, 
                'email' => $user->email, 
                'is_active' => $user->is_active,
                'is_active_type' => gettype($user->is_active)
            ]);
            return redirect('/login')->withErrors(['email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.']);
        }

        // Flash success message with user name
        session()->flash('success', 'Selamat datang, ' . $user->name . '!');

        // Redirect based on role
        switch ($user->role) {
            case 'super_admin':
                return redirect('/dashboard');
            case 'admin_gudang':
                return redirect('/dashboard'); // Fixed: use unified dashboard
            case 'staff_gudang':
                return redirect('/dashboard'); // Fixed: use unified dashboard
            default:
                return redirect('/dashboard');
        }
    }
}