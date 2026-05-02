<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Models\AdminHelpdesk;
use App\Models\KategoriSistem;
use App\Models\KnowledgeBase;
use App\Models\NodeDiagnosis;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Notifications\TiketMasukNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiagnosisMandiriController extends Controller
{
    public function index()
    {
        $kategori = KategoriSistem::whereHas('nodes')
            ->whereDoesntHave('nodes', function ($q) {
                $q->where('tipe_node', 'solusi')
                  ->where(function ($q2) {
                      $q2->whereNull('kb_id')
                         ->orWhereNull('sop_internal_id')
                         ->orWhereNull('bidang_id');
                  });
            })
            ->get();

        return view('opd.buat-pengaduan.index', compact('kategori'));
    }

    public function mulai(string $kategoriId)
    {
        $kategori = KategoriSistem::findOrFail($kategoriId);

        $allNodes = NodeDiagnosis::where('kategori_id', $kategoriId)->get();

        if ($allNodes->isEmpty()) {
            return redirect()->route('opd.diagnosis.tiket', [
                'kategori_id'        => $kategoriId,
                'kategori_nama'      => $kategori->nama_kategori,
                'kategori_deskripsi' => $kategori->deskripsi ?? '',
                'diagnosa'           => '',
            ])->with('info', 'Belum ada alur diagnosis untuk kategori ini. Silakan buat tiket langsung.');
        }

        $referencedIds = $allNodes
            ->flatMap(fn($n) => [$n->id_next_ya, $n->id_next_tidak])
            ->filter()
            ->unique()
            ->toArray();

        $rootNode = $allNodes
            ->where('tipe_node', 'pertanyaan')
            ->whereNotIn('id', $referencedIds)
            ->first();

        if (!$rootNode) {
            return redirect()->route('opd.diagnosis.tiket', [
                'kategori_id'        => $kategoriId,
                'kategori_nama'      => $kategori->nama_kategori,
                'kategori_deskripsi' => $kategori->deskripsi ?? '',
                'diagnosa'           => '',
            ]);
        }

        return redirect()->to(
            route('opd.diagnosis.node', $rootNode->id) . '?' . http_build_query([
                'kategori_id'        => $kategoriId,
                'kategori_nama'      => $kategori->nama_kategori,
                'kategori_deskripsi' => $kategori->deskripsi ?? '',
                'diagnosa'           => '',
                'q'                  => 1,
            ])
        );
    }

    public function showNode(string $nodeId, Request $request)
    {
        $node = NodeDiagnosis::findOrFail($nodeId);

        $kategoriId        = $request->query('kategori_id', '');
        $kategoriNama      = $request->query('kategori_nama', '');
        $kategoriDeskripsi = $request->query('kategori_deskripsi', '');
        $diagnosa          = $request->query('diagnosa', '');
        $qNum              = (int) $request->query('q', 1);

        if ($node->tipe_node === 'solusi') {
            $kb = $node->kb_id ? KnowledgeBase::find($node->kb_id) : null;
            $bidangId = $node->bidang_id ?? '';
            return view('opd.buat-pengaduan.solusi', compact(
                'node', 'kb', 'kategoriId', 'kategoriNama', 'kategoriDeskripsi', 'diagnosa', 'bidangId'
            ));
        }

        $baseQuery = [
            'kategori_id'        => $kategoriId,
            'kategori_nama'      => $kategoriNama,
            'kategori_deskripsi' => $kategoriDeskripsi,
            'q'                  => $qNum + 1,
        ];

        $urlYa = $node->id_next_ya
            ? route('opd.diagnosis.node', $node->id_next_ya) . '?' . http_build_query(
                array_merge($baseQuery, ['diagnosa' => trim($diagnosa . ' > Ya')])
              )
            : null;

        $urlTidak = $node->id_next_tidak
            ? route('opd.diagnosis.node', $node->id_next_tidak) . '?' . http_build_query(
                array_merge($baseQuery, ['diagnosa' => trim($diagnosa . ' > Tidak')])
              )
            : null;

        return view('opd.buat-pengaduan.node', compact(
            'node', 'kategoriId', 'kategoriNama', 'kategoriDeskripsi', 'diagnosa', 'qNum', 'urlYa', 'urlTidak'
        ));
    }

    public function storeTiket(Request $request)
    {
        $request->validate([
            'subjek_masalah'  => 'required|string|max:255',
            'detail_masalah'  => 'required|string',
            'foto_bukti'      => 'nullable|array|max:5',
            'foto_bukti.*'    => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $opd = Auth::user()->opd;
        if (!$opd) {
            abort(403, 'Data OPD tidak ditemukan.');
        }

        $tiketId   = 'TKT-' . strtoupper(Str::random(10));
        $fotoPaths = [];

        if ($request->hasFile('foto_bukti')) {
            foreach ($request->file('foto_bukti') as $foto) {
                $fotoPaths[] = $foto->store('tiket/foto', 'public');
            }
        }

        $rekomendasi = $request->input('rekomendasi_penanganan');
        if (!in_array($rekomendasi, ['admin', 'eskalasi'])) {
            $rekomendasi = null;
        }

        $tiket = Tiket::create([
            'id'                      => $tiketId,
            'opd_id'                  => $opd->id,
            'kb_id'                   => $request->input('kb_id') ?: null,
            'sop_internal_id'         => $request->input('sop_internal_id') ?: null,
            'bidang_id'               => $request->input('bidang_id') ?: null,
            'rekomendasi_penanganan'  => $rekomendasi,
            'kategori_id'             => $request->input('kategori_id') ?: null,
            'subjek_masalah'          => $request->input('subjek_masalah'),
            'detail_masalah'          => $request->input('detail_masalah'),
            'spesifikasi_perangkat'   => $request->input('spesifikasi_perangkat'),
            'lokasi'                  => $request->input('lokasi'),
            'foto_bukti'              => $fotoPaths ?: null,
        ]);

        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
            'created_at'   => now(),
        ]);

        // Notifikasi ke Admin Helpdesk sesuai bidang dari node solusi
        $adminQuery = AdminHelpdesk::with('user');
        $bidangId = $request->input('bidang_id');
        if ($bidangId) {
            $adminQuery->where('bidang_id', $bidangId);
        }
        $namaOpd = $opd->nama_opd ?? 'OPD';
        $url     = route('admin_helpdesk.tiket.menunggu');
        $adminQuery->get()->each(function ($admin) use ($tiket, $namaOpd, $url) {
            $admin->user?->notify(new TiketMasukNotification($tiket->id, $namaOpd, $url));
        });

        return redirect()->route('opd.tiket.index')
                         ->with('success', 'Tiket #' . $tiketId . ' berhasil dikirim! Admin Helpdesk akan memverifikasi pengaduan Anda.');
    }

    public function showTiket(Request $request)
    {
        $kategoriId        = $request->query('kategori_id', '');
        $kategoriNama      = $request->query('kategori_nama', '');
        $kategoriDeskripsi = $request->query('kategori_deskripsi', '');
        $kbId              = $request->query('kb_id', '');
        $sopInternalId     = $request->query('sop_internal_id', '');
        $bidangId          = $request->query('bidang_id', '');
        $rekomendasi       = $request->query('rekomendasi_penanganan', 'admin');

        if (!in_array($rekomendasi, ['admin', 'eskalasi'])) {
            $rekomendasi = 'admin';
        }

        return view('opd.buat-pengaduan.tiket', compact(
            'kategoriId', 'kategoriNama', 'kategoriDeskripsi', 'kbId', 'sopInternalId', 'bidangId', 'rekomendasi'
        ));
    }
}
