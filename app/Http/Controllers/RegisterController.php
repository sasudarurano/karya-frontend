<?php

namespace App\Http\Controllers;

use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RegisterController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Show registration form
     */
    public function showRegisterForm()
    {
        // If already logged in, redirect to dashboard
        if (Session::has('api_token')) {
            return redirect()->route('dashboard');
        }

        // Get program studi list for dropdown
        $prodiResponse = $this->api->getAllProgramStudi();
        
        if ($prodiResponse->successful()) {
            $responseData = $prodiResponse->json();
            if (isset($responseData['data']['data'])) {
                $programStudiList = $responseData['data']['data'];
            } else {
                $programStudiList = $responseData['data'] ?? [];
            }
            
            // Debug log structure
        } else {
            $programStudiList = [];
            \Log::error('Failed to fetch program studi', [
                'status' => $prodiResponse->status(),
                'body' => $prodiResponse->body(),
            ]);
        }

        return view('auth.register', compact('programStudiList'));
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:6|confirmed',
            'full_name' => 'required|string|max:100',
            'nid' => 'required|string|max:20',
            'program_studi_id' => 'required|string',
            'role' => 'required|in:mhs,dosen' // Only allow mhs and dosen for public registration
        ]);

        try {
            // Prepare data dan convert types untuk backend
            $data = $request->all();
            
            $response = $this->api->register($data);

            if ($response->successful()) {
                return redirect()->route('login')
                    ->with('success', 'Registrasi berhasil! Silakan login.');
            }

            // Handle validation errors dari backend (422)
            if ($response->status() === 422) {
                $body = $response->json();
                $errors = $body['errors'] ?? $body['details'] ?? [];
                $message = $body['message'] ?? 'Validasi gagal.';
                
                // Log untuk debugging
                
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors($errors)
                    ->with('error', $message);
            }

            $errorMessage = $response->json()['message'] ?? 'Registrasi gagal. Silakan coba lagi.';
            
            \Log::error('Registration failed:', [
                'status' => $response->status(),
                'message' => $errorMessage,
                'body' => $response->body()
            ]);
            
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => $errorMessage]);
        } catch (\Exception $e) {
            \Log::error('Registration exception: ' . $e->getMessage());
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }
}
