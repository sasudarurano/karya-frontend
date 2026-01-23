<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProgramStudiController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Display list of all program studi
     */
    public function index()
    {
        try {
            $response = $this->api->getAllProgramStudi();

            if ($response->successful()) {
                $programStudiList = $response->json()['data'] ?? [];
                return view('admin.program-studi.index', compact('programStudiList'));
            }

            return back()->with('error', 'Gagal mengambil data program studi.');
        } catch (\Exception $e) {
            Log::error("Admin prodi index error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.program-studi.create');
    }

    /**
     * Store new program studi
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20',
            'faculty' => 'required|string|max:100',
            'description' => 'nullable|string'
        ]);

        $payload = $request->only(['code', 'name', 'faculty', 'description']);

        try {
            $response = $this->api->createProgramStudi($payload);

            if ($response->successful()) {
                return redirect()->route('admin.program-studi.index')
                    ->with('success', 'Program Studi berhasil ditambahkan!');
            }

            // Jika backend mengembalikan error validasi
            if ($response->status() === 422) {
                $body = $response->json();
                $errors = $body['errors'] ?? $body['details'] ?? [];
                $message = $body['message'] ?? 'Validasi gagal.';
                return back()
                    ->withInput()
                    ->withErrors($errors)
                    ->with('error', $message);
            }

            return back()
                ->withInput()
                ->with('error', $response->json()['message'] ?? 'Gagal menambahkan program studi.');
        } catch (\Exception $e) {
            Log::error("Admin prodi store error: " . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        try {
            $response = $this->api->getProgramStudiById($id);

            if ($response->successful()) {
                $programStudi = $response->json()['data'] ?? null;
                return view('admin.program-studi.edit', compact('programStudi'));
            }

            return back()->with('error', 'Program studi tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error("Admin prodi edit error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Update program studi
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20',
            'faculty' => 'required|string|max:100',
            'description' => 'nullable|string'
        ]);

        $payload = $request->only(['code', 'name', 'faculty', 'description']);

        try {
            $response = $this->api->updateProgramStudi($id, $payload);

            if ($response->successful()) {
                return redirect()->route('admin.program-studi.index')
                    ->with('success', 'Program Studi berhasil diperbarui!');
            }

            // Jika backend mengembalikan error validasi
            if ($response->status() === 422) {
                $body = $response->json();
                $errors = $body['errors'] ?? $body['details'] ?? [];
                $message = $body['message'] ?? 'Validasi gagal.';
                return back()
                    ->withInput()
                    ->withErrors($errors)
                    ->with('error', $message);
            }

            return back()
                ->withInput()
                ->with('error', $response->json()['message'] ?? 'Gagal memperbarui program studi.');
        } catch (\Exception $e) {
            Log::error("Admin prodi update error: " . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Delete program studi
     */
    public function destroy($id)
    {
        try {
            $response = $this->api->deleteProgramStudi($id);

            if ($response->successful()) {
                return redirect()->route('admin.program-studi.index')
                    ->with('success', 'Program Studi berhasil dihapus!');
            }

            return back()->with('error', $response->json()['message'] ?? 'Gagal menghapus program studi.');
        } catch (\Exception $e) {
            Log::error("Admin prodi delete error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }
}
