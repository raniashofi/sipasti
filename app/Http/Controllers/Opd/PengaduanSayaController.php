<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Models\StatusTiket;
use App\Models\Tiket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PengaduanSayaController extends Controller
{
    public function index(Request $request)
    {
        $opd = Auth::user()->opd;
        if (!$opd) {
            abort(403, 'Data OPD tidak ditemukan.');
        }

        $query = Tiket::where('opd_id', $opd->id)
            ->with('latestStatus')
            ->orderByDesc('created_at');

        // Filter status
        if ($request->filled('status')) {
            $query->whereHas('latestStatus', fn($q) =>
                $q->where('status_tiket', $request->status)
            );
        }

        // Search by ID or subjek
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) =>
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('subjek_masalah', 'like', "%{$search}%")
            );
        }

        $tikets = $query->paginate(10)->withQueryString();

        return view('opd.pengaduan-saya.index', compact('tikets'));
    }

    public function show(string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with([
                          'kb',
                          'latestStatus',
                          'statusTiket' => fn($q) => $q->orderBy('created_at', 'asc'),
                      ])
                      ->findOrFail($id);

        return view('opd.pengaduan-saya.detail', compact('tiket'));
    }

    public function chat(string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)->findOrFail($id);

        return view('opd.pengaduan-saya.chat', compact('tiket'));
    }

    /**
     * OPD mengkonfirmasi tiket sudah selesai + kirim penilaian.
     */
    public function konfirm(Request $request, string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with('latestStatus')
                      ->findOrFail($id);

        if ($tiket->latestStatus?->status_tiket !== 'selesai') {
            return back()->with('error', 'Tiket tidak dalam status selesai.');
        }

        $request->validate([
            'penilaian'          => 'required|integer|min:1|max:5',
            'komentar_penutupan' => 'nullable|string|max:1000',
        ]);

        $tiket->update([
            'penilaian'          => $request->input('penilaian'),
            'komentar_penutupan' => $request->input('komentar_penutupan'),
        ]);

        return redirect()->route('opd.tiket.index')
            ->with('success', 'Tiket #' . $id . ' telah ditutup. Terima kasih atas penilaian Anda!');
    }

    /**
     * OPD membuka kembali tiket yang sudah selesai karena masalah belum teratasi.
     */
    public function bukaKembali(Request $request, string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with('latestStatus')
                      ->findOrFail($id);

        if ($tiket->latestStatus?->status_tiket !== 'selesai') {
            return back()->with('error', 'Tiket tidak dalam status selesai.');
        }

        $request->validate([
            'alasan'     => 'required|string|max:1000',
            'file_bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file_bukti')) {
            $filePath = $request->file('file_bukti')->store('tiket/bukti', 'public');
        }

        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'dibuka_kembali',
            'catatan'      => $request->input('alasan'),
            'file_bukti'   => $filePath,
            'created_at'   => now(),
        ]);

        return redirect()->route('opd.tiket.show', $id)
            ->with('success', 'Tiket telah dibuka kembali. Admin Helpdesk akan meninjau laporan Anda.');
    }

    /**
     * Tampilkan form edit tiket (hanya saat status perlu_revisi).
     */
    public function edit(string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with('latestStatus')
                      ->findOrFail($id);

        if ($tiket->latestStatus?->status_tiket !== 'perlu_revisi') {
            return redirect()->route('opd.tiket.show', $id)
                ->with('error', 'Tiket hanya dapat diedit saat berstatus Perlu Revisi.');
        }

        return view('opd.pengaduan-saya.edit', compact('tiket'));
    }

    /**
     * Simpan perubahan tiket dan kembalikan ke verifikasi_admin.
     */
    public function update(Request $request, string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with('latestStatus')
                      ->findOrFail($id);

        if ($tiket->latestStatus?->status_tiket !== 'perlu_revisi') {
            return redirect()->route('opd.tiket.show', $id)
                ->with('error', 'Tiket hanya dapat diedit saat berstatus Perlu Revisi.');
        }

        $request->validate([
            'subjek_masalah' => 'required|string|max:255',
            'detail_masalah' => 'required|string',
            'foto_bukti'     => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        // Handle foto baru jika diupload
        $fotoPath = $tiket->foto_bukti;
        if ($request->hasFile('foto_bukti')) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto_bukti')->store('tiket/foto', 'public');
        }

        $tiket->update([
            'subjek_masalah'        => $request->input('subjek_masalah'),
            'detail_masalah'        => $request->input('detail_masalah'),
            'spesifikasi_perangkat' => $request->input('spesifikasi_perangkat'),
            'lokasi'                => $request->input('lokasi'),
            'foto_bukti'            => $fotoPath,
        ]);

        // Kembalikan status ke verifikasi_admin setelah revisi
        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
            'catatan'      => 'Tiket telah direvisi oleh OPD dan dikembalikan untuk verifikasi ulang.',
            'created_at'   => now(),
        ]);

        return redirect()->route('opd.tiket.show', $id)
            ->with('success', 'Tiket berhasil diperbarui dan dikirim kembali untuk verifikasi.');
    }
}
