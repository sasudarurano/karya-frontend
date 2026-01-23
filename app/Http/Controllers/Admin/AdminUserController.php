<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AdminUserController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Tampilkan daftar user
     */
    public function index()
    {
        // FIX: Ambil token admin
        $token = Session::get('api_token');

        // Kirim token ke API untuk otentikasi admin
        $response = $this->api->getAllUsers($token);
        
        $users = [];
        $error_message = null;
        $debug_info = null;

        if ($response->successful()) {
            $users = $response->json()['data'] ?? [];
            
            Log::info('Admin getAllUsers response:', [
                'total_users' => count($users),
            ]);
        } else {
            Log::error('Failed to fetch users for admin', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            // DEBUG: Simpan info untuk ditampilkan di view
            $debug_info = [
                'status' => $response->status(),
                'body' => $response->body(),
                'token_exists' => !empty($token),
                'token_length' => strlen($token ?? ''),
                'api_url' => env('API_BASE_URL', 'http://localhost:3000/api'),
            ];
            
            if ($response->status() === 401) {
                return redirect()->route('login')->with('error', 'Sesi kadaluarsa, silakan login kembali.');
            }
            
            $error_message = 'Gagal mengambil data user. Status: ' . $response->status();
        }

        return view('admin.users.index', compact('users', 'error_message', 'debug_info'));
    }

    /**
     * Tampilkan form create user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store user baru
     */
    public function store(Request $request)
    {
        $token = Session::get('api_token');

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|same:password',
            'identifier' => 'required|string|unique:users,identifier',
            'role' => 'required|in:mahasiswa,verifikator,admin,superadmin,dosen,kemahasiswaan,kaprodi',
        ]);

        // Remove password confirmation before sending to backend
        unset($validated['password_confirmation']);

        // FIX: Kirim token saat create user (Admin Action)
        $response = $this->api->createUser($validated, $token);

        if ($response->successful()) {
            Log::info('User created successfully', [
                'email' => $validated['email'],
                'role' => $validated['role'],
            ]);
            return redirect()->route('admin.users.index')
                ->with('success', "User {$validated['email']} berhasil dibuat.");
        }

        Log::error('Failed to create user', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        $errors = [];
        if ($response->status() === 422) {
            $errors = $response->json()['errors'] ?? [];
        }

        return back()->withInput()
            ->with('error', 'Gagal membuat user.')
            ->with('errors', $errors);
    }

    /**
     * Tampilkan form edit user
     */
    public function edit($id)
    {
        $token = Session::get('api_token');
        
        // FIX: Kirim token saat fetch detail user
        $response = $this->api->getUserById($id, $token);
        
        if (!$response->successful()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User tidak ditemukan atau akses ditolak.');
        }

        $user = $response->json()['data'] ?? null;
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $token = Session::get('api_token');

        Log::info('AdminUserController:update - Request received', [
            'user_id' => $id,
            'all_input' => $request->all(),
        ]);

        // Validasi input - gunakan role values yang sama dengan frontend
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|in:mhs,verifikator,kaprodi,dosen,kemahasiswaan,superadmin',
        ]);

        Log::info('AdminUserController:update - Validation passed', [
            'user_id' => $id,
            'validated_data' => $validated,
        ]);

        // FIX: Pastikan data yang dikirim bersih
        $data = [
            'full_name' => $request->full_name,
            'email' => $request->email,
            'role' => $request->role, 
            // Jangan kirim identifier karena biasanya read-only di backend untuk update
        ];

        Log::info('AdminUserController:update - Sending to API', [
            'user_id' => $id,
            'data' => $data,
            'token_exists' => !empty($token),
        ]);

        // FIX: Kirim token ke fungsi update
        $response = $this->api->updateUser($id, $data, $token);

        Log::info('AdminUserController:update - API Response', [
            'user_id' => $id,
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body' => $response->json(),
        ]);

        if ($response->successful()) {
            Log::info('User updated successfully', [
                'user_id' => $id,
                'role_new' => $validated['role'],
            ]);
            
            // Redirect ke index atau tetap di edit dengan pesan sukses
            return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
        }

        Log::error('Failed to update user', [
            'user_id' => $id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        $errors = [];
        if ($response->status() === 422) {
            $errors = $response->json()['errors'] ?? [];
        }

        return back()->withInput()
            ->with('error', 'Gagal memperbarui user: ' . ($response->json()['message'] ?? 'Error Server'))
            ->with('errors', $errors);
    }

    /**
     * Change user role to verifikator (Helper function)
     */
    public function changeToVerifikator($id)
    {
        $token = Session::get('api_token');
        
        // FIX: Kirim token
        $response = $this->api->changeUserRoleToVerifikator($id, $token);

        if ($response->successful()) {
            Log::info('User role changed to verifikator', ['user_id' => $id]);
            return back()->with('success', 'User role berhasil diubah menjadi verifikator.');
        }

        Log::error('Failed to change user role', [
            'user_id' => $id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return back()->with('error', 'Gagal mengubah role user.');
    }

    /**
     * Verify/Validate user (untuk mahasiswa yang baru register)
     */
    public function verifyUser($id)
    {
        $token = Session::get('api_token');
        
        $response = $this->api->verifyUser($id, $token);

        if ($response->successful()) {
            Log::info('User verified successfully', ['user_id' => $id]);
            return back()->with('success', 'Akun mahasiswa berhasil divalidasi.');
        }

        Log::error('Failed to verify user', [
            'user_id' => $id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return back()->with('error', 'Gagal memvalidasi akun mahasiswa.');
    }
}