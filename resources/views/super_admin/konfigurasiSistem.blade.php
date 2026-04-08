<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Konfigurasi Sistem — Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        .ps::-webkit-scrollbar { width:6px; height:6px; }
        .ps::-webkit-scrollbar-track { background:transparent; }
        .ps::-webkit-scrollbar-thumb { background:#CBD5E1; border-radius:9999px; }
        .ps::-webkit-scrollbar-thumb:hover { background:#94A3B8; }
        .canvas-bg {
            background-color: #F0F4F8;
            background-image: radial-gradient(circle, #CBD5E1 1px, transparent 1px);
            background-size: 24px 24px;
        }
        /* Flow node styles */
        .fn-q  { cursor:pointer; background:#EFF6FF; border:2px solid #BFDBFE; border-radius:12px; padding:12px 16px; min-width:200px; max-width:260px; text-align:center; transition:all .2s; position:relative; z-index:2; }
        .fn-q:hover  { border-color:#01458E; box-shadow:0 4px 16px rgba(1,69,142,.15); transform:translateY(-1px); }
        .fn-q.fn-sel { border-color:#01458E; box-shadow:0 0 0 3px rgba(1,69,142,.2); }
        .fn-s  { cursor:pointer; background:#F5F3FF; border:2px solid #DDD6FE; border-radius:12px; padding:12px 16px; min-width:180px; max-width:240px; text-align:center; transition:all .2s; position:relative; z-index:2; }
        .fn-s:hover  { border-color:#7C3AED; box-shadow:0 4px 16px rgba(124,58,237,.15); transform:translateY(-1px); }
        .fn-s.fn-sel { border-color:#7C3AED; box-shadow:0 0 0 3px rgba(124,58,237,.2); }
        .fn-end { background:#F9FAFB; border:2px dashed #D1D5DB; border-radius:10px; padding:8px 14px; min-width:120px; text-align:center; position:relative; z-index:2; }
        /* Tree node styles */
        .tn-q { cursor:pointer; border-left:3px solid #BFDBFE; background:#EFF6FF; border-radius:8px; padding:7px 10px; margin:0 10px 2px 0; max-width:240px; transition:all .15s; }
        .tn-q:hover, .tn-q.tn-sel { border-left-color:#01458E; background:#DBEAFE; }
        .tn-s { cursor:pointer; border-left:3px solid #DDD6FE; background:#F5F3FF; border-radius:8px; padding:7px 10px; margin:0 10px 2px 0; max-width:240px; transition:all .15s; }
        .tn-s:hover, .tn-s.tn-sel { border-left-color:#7C3AED; background:#EDE9FE; }
        .panel-resize-handle { position:absolute; right:0; top:0; bottom:0; width:4px; cursor:col-resize; z-index:10; transition:background .15s; }
        .panel-resize-handle:hover, .panel-resize-handle.resizing { background:#BFDBFE; }
    </style>
</head>
<body class="bg-[#F0F4F8]">

    @include('layouts.sidebarSuperAdmin')

    <script>
    /* ─── Global bridge ─── */
    let _ki = null;
    function _sn(nodeId, katId) {
        if (!_ki) return;
        const kat  = _ki.kategoris.find(k => k.id === katId);   if (!kat)  return;
        const node = kat.nodes.find(n => n.id === nodeId);        if (!node) return;
        _ki.aktifKatId    = katId;
        _ki.expandedKatId = katId;
        _ki.aktifNodeId   = node.id;
        _ki.nodeForm      = { ...node };
        _ki.katForm       = { id:kat.id, nama_kategori:kat.nama_kategori, deskripsi:kat.deskripsi, bidang_id:kat.bidang_id };
        _ki.panel         = 'edit-node';
    }

    function konfigPage() {
        const csrf = document.querySelector('meta[name=csrf-token]').content;

        return {
            kategoris : @json($kategorisData),
            bidangs   : @json($bidangsData),
            articles  : @json($articlesData),

            panel          : null,
            expandedKatId  : null,
            aktifKatId     : null,
            aktifNodeId    : null,
            loading        : false,
            toast          : null,
            toastType      : 'success',
            leftPanelWidth : 260,
            isResizing     : false,

            // Zoom functionality
            zoomLevel      : 1,

            simOpen        : false,
            simCurrentNode : null,
            simHistory     : [],

            deleteConfirmOpen: false,
            deleteType       : null,
            deleteItem       : null,

            katForm : { id:'', nama_kategori:'', deskripsi:'', bidang_id:'' },
            nodeForm: {
                id:'', kategori_id:'', tipe_node:'pertanyaan',
                teks_pertanyaan:'', hint_konteks:'',
                judul_solusi:'', penjelasan_solusi:'', prioritas:'',
                id_next_ya:'', id_next_tidak:'', kb_id:'', kode:'',
                routing_ya_type:'', routing_ya_child_kode:'', routing_ya_child_text:'',
                routing_tidak_type:'', routing_tidak_child_kode:'', routing_tidak_child_text:'',
            },

            init() { _ki = this; },

            get aktifKat()        { return this.kategoris.find(k => k.id === this.aktifKatId) || null; },
            get aktifNodes()      { return this.aktifKat?.nodes || []; },
            get otherNodes()      { return this.aktifNodes.filter(n => n.id !== this.nodeForm.id); },
            get pertanyaanNodes() { return this.otherNodes.filter(n => n.tipe_node === 'pertanyaan'); },
            get filteredArticles() {
                const katId = this.nodeForm.kategori_id || this.aktifKatId;
                if (!katId) return [];
                return this.articles.filter(a => a.kategori_id === katId);
            },

            // Zoom Controls
            zoomIn() { this.zoomLevel = Math.min(this.zoomLevel + 0.1, 1.5); },
            zoomOut() { this.zoomLevel = Math.max(this.zoomLevel - 0.1, 0.4); },
            resetZoom() { this.zoomLevel = 1; },

            get flowHtml() {
                if (!this.aktifKat) return this._emptyFlow();
                const nodes = this.aktifNodes;
                if (!nodes.length) return this._emptyFlow(
                    'Belum ada node',
                    'Tambahkan pertanyaan atau solusi melalui tombol "+ Node Baru" di atas.');
                const nm  = {}; nodes.forEach(n => nm[n.id] = n);
                const ref = new Set();
                nodes.forEach(n => { if (n.id_next_ya) ref.add(n.id_next_ya); if (n.id_next_tidak) ref.add(n.id_next_tidak); });
                const roots = nodes.filter(n => !ref.has(n.id));
                if (!roots.length) return this._emptyFlow('Root tidak ditemukan', 'Mungkin ada circular reference pada node.');
                const katId = this.aktifKatId;
                const sel   = this.aktifNodeId;

                const startNode = `
                    <div style="display:inline-block;background:#ECFDF5;border:2px solid #6EE7B7;border-radius:12px;padding:8px 28px;text-align:center;position:relative;z-index:2;">
                        <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#059669;margin-bottom:2px;">✦ MULAI</div>
                        <div style="font-size:13px;font-weight:700;color:#065F46;">${this.aktifKat.nama_kategori}</div>
                    </div>`;
                const arrowDown = '<div style="width:2px;height:24px;background:#CBD5E1;margin:0 auto;position:relative;z-index:1;"></div>';
                const body = roots.map(r => this._flowNode(r, nm, katId, sel, new Set())).join('<div style="margin-top:24px;"></div>');
                return `<div style="display:flex;flex-direction:column;align-items:center;padding:48px 32px;min-width:480px;">${startNode}${arrowDown}${body}</div>`;
            },

            _flowNode(n, nm, katId, sel, vis) {
                if (!n || vis.has(n.id))
                    return `<div style="padding:6px 12px;background:#FEF3C7;border:1px solid #FCD34D;border-radius:8px;font-size:11px;color:#92400E;position:relative;z-index:2;">↺ Siklus terdeteksi</div>`;
                const v2   = new Set([...vis, n.id]);
                const isSel = sel === n.id;
                const selS  = isSel ? 'box-shadow:0 0 0 3px rgba(1,69,142,.25);' : '';

                if (n.tipe_node === 'solusi') {
                    return `<div class="fn-s${isSel?' fn-sel':''}" onclick="_sn('${n.id}','${katId}')" style="${selS}">
                        <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#7C3AED;margin-bottom:4px;">• SOLUSI · ${n.kode}</div>
                        <div style="font-size:12px;font-weight:600;color:#4C1D95;line-height:1.4;">${this._trunc(n.judul_solusi||'—',50)}</div>
                        ${n.kb_judul ? `<div style="font-size:10px;color:#8B5CF6;margin-top:3px;">${this._trunc(n.kb_judul,40)}</div>` : ''}
                    </div>`;
                }

                const nya  = n.id_next_ya     ? nm[n.id_next_ya]     : null;
                const ntdk = n.id_next_tidak  ? nm[n.id_next_tidak]  : null;
                const endBox = `<div class="fn-end"><div style="font-size:9px;font-weight:600;color:#9CA3AF;font-family:monospace;">— Akhir —</div></div>`;
                const yaH  = nya  ? this._flowNode(nya,  nm, katId, sel, v2) : endBox;
                const tdkH = ntdk ? this._flowNode(ntdk, nm, katId, sel, v2) : endBox;

                // Trik CSS untuk membuat garis cabang T-Branch yang rapi sempurna
                return `<div style="display:flex;flex-direction:column;align-items:center;">
                    <div class="fn-q${isSel?' fn-sel':''}" onclick="_sn('${n.id}','${katId}')" style="${selS}">
                        <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#1D4ED8;margin-bottom:4px;">? PERTANYAAN · ${n.kode}</div>
                        <div style="font-size:12px;font-weight:600;color:#1E3A5F;line-height:1.4;">${this._trunc(n.teks_pertanyaan||'—',60)}</div>
                        ${n.hint_konteks ? `<div style="font-size:10px;color:#6B7280;margin-top:3px;">${this._trunc(n.hint_konteks,45)}</div>` : ''}
                    </div>
                    <div style="width:2px;height:16px;background:#CBD5E1;position:relative;z-index:1;"></div>
                    <div style="display:flex;justify-content:center;width:100%;">

                        <div style="display:flex;flex-direction:column;align-items:center;flex:1;position:relative;padding:0 8px;">
                            <div style="position:absolute;top:0;right:0;width:50%;border-top:2px solid #CBD5E1;"></div>
                            <div style="width:2px;height:16px;background:#CBD5E1;position:relative;z-index:1;"></div>
                            <div style="padding:3px 14px;background:#DCFCE7;border:1px solid #86EFAC;border-radius:12px;font-size:10px;font-weight:700;color:#16A34A;margin-bottom:12px;box-shadow:0 2px 4px rgba(22,163,74,.1);position:relative;z-index:2;">YA</div>
                            ${yaH}
                        </div>

                        <div style="display:flex;flex-direction:column;align-items:center;flex:1;position:relative;padding:0 8px;">
                            <div style="position:absolute;top:0;left:0;width:50%;border-top:2px solid #CBD5E1;"></div>
                            <div style="width:2px;height:16px;background:#CBD5E1;position:relative;z-index:1;"></div>
                            <div style="padding:3px 14px;background:#FEE2E2;border:1px solid #FCA5A5;border-radius:12px;font-size:10px;font-weight:700;color:#DC2626;margin-bottom:12px;box-shadow:0 2px 4px rgba(220,38,38,.1);position:relative;z-index:2;">TIDAK</div>
                            ${tdkH}
                        </div>

                    </div>
                </div>`;
            },

            _emptyFlow(title = 'Pilih kategori', desc = 'Klik kategori di panel kiri untuk melihat alur diagnosis') {
                return `<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;text-align:center;padding:40px;">
                    <div style="width:64px;height:64px;background:white;border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.06);">
                        <svg style="width:32px;height:32px;color:#D1D5DB;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                    </div>
                    <p style="font-size:14px;font-weight:600;color:#6B7280;margin-bottom:6px;">${title}</p>
                    <p style="font-size:12px;color:#9CA3AF;max-width:280px;line-height:1.6;">${desc}</p>
                </div>`;
            },

            katTreeHtml(kat) {
                const nodes = kat.nodes;
                if (!nodes.length)
                    return `<div style="padding:10px 14px;font-size:11px;color:#9CA3AF;font-style:italic;">Belum ada node</div>`;
                const nm  = {}; nodes.forEach(n => nm[n.id] = n);
                const ref = new Set();
                nodes.forEach(n => { if (n.id_next_ya) ref.add(n.id_next_ya); if (n.id_next_tidak) ref.add(n.id_next_tidak); });
                const roots = nodes.filter(n => !ref.has(n.id));
                const katId = kat.id;
                const sel   = this.aktifNodeId;
                if (!roots.length) return nodes.map(n => this._treeLeaf(n, katId, sel, 0)).join('');
                return roots.map(r => this._treeNode(r, nm, katId, sel, new Set(), 0)).join('');
            },

            _treeNode(n, nm, katId, sel, vis, depth) {
                if (!n || vis.has(n.id)) return '';
                const v2  = new Set([...vis, n.id]);
                const box = this._treeLeaf(n, katId, sel, depth);
                if (n.tipe_node === 'solusi') return box;
                const nya  = n.id_next_ya    ? nm[n.id_next_ya]    : null;
                const ntdk = n.id_next_tidak ? nm[n.id_next_tidak] : null;
                if (!nya && !ntdk) return box;
                const ml = (depth * 12) + 12;
                const yaSec  = nya  ? `<div style="margin-left:${ml}px;border-left:1px dashed #E5E7EB;padding-left:8px;padding-bottom:2px;"><div style="font-size:9px;font-weight:700;color:#16A34A;padding:3px 0 2px;">↳ YA</div>${this._treeNode(nya, nm, katId, sel, v2, depth+1)}</div>` : '';
                const tdkSec = ntdk ? `<div style="margin-left:${ml}px;border-left:1px dashed #E5E7EB;padding-left:8px;padding-bottom:2px;"><div style="font-size:9px;font-weight:700;color:#DC2626;padding:3px 0 2px;">↳ TIDAK</div>${this._treeNode(ntdk, nm, katId, sel, v2, depth+1)}</div>` : '';
                return box + yaSec + tdkSec;
            },

            _treeLeaf(n, katId, sel, depth) {
                const isSel = sel === n.id;
                const isQ   = n.tipe_node === 'pertanyaan';
                const cls   = (isQ ? 'tn-q' : 'tn-s') + (isSel ? ' tn-sel' : '');
                const ml    = depth > 0 ? `margin-left:${depth * 12}px;` : '';
                const badge = isQ
                    ? `<span style="font-size:9px;font-weight:700;color:#1D4ED8;font-family:monospace;flex-shrink:0;">?</span>`
                    : `<span style="font-size:9px;font-weight:700;color:#7C3AED;font-family:monospace;flex-shrink:0;">•</span>`;
                const text  = isQ ? (n.teks_pertanyaan||'—') : (n.judul_solusi||'—');
                const short = text.length > 38 ? text.substring(0,38)+'…' : text;
                return `<div class="${cls}" style="${ml}" onclick="_sn('${n.id}','${katId}')">
                    <div style="display:flex;align-items:flex-start;gap:5px;">
                        <div style="margin-top:1px;">${badge}</div>
                        <div style="min-width:0;">
                            <div style="font-size:9px;color:#9CA3AF;font-family:monospace;">${n.kode}</div>
                            <div style="font-size:11px;font-weight:500;color:${isQ?'#1E3A5F':'#4C1D95'};line-height:1.3;">${short}</div>
                        </div>
                    </div>
                </div>`;
            },

            _trunc(s, l) { return s && s.length > l ? s.substring(0,l)+'…' : (s||'—'); },

            openSim() {
                if (!this.aktifNodes.length) { this.showToast('Tidak ada node untuk disimulasikan.', 'error'); return; }
                const nodes = this.aktifNodes;
                const nm  = {}; nodes.forEach(n => nm[n.id] = n);
                const ref = new Set(); nodes.forEach(n => { if (n.id_next_ya) ref.add(n.id_next_ya); if (n.id_next_tidak) ref.add(n.id_next_tidak); });
                const roots = nodes.filter(n => !ref.has(n.id));
                this.simCurrentNode = roots[0] || nodes[0];
                this.simHistory     = [this.simCurrentNode];
                this.simOpen        = true;
            },
            simAnswer(ans) {
                if (!this.simCurrentNode) return;
                const nm = {}; this.aktifNodes.forEach(n => nm[n.id] = n);
                const nextId = ans === 'ya' ? this.simCurrentNode.id_next_ya : this.simCurrentNode.id_next_tidak;
                this.simCurrentNode = nextId ? (nm[nextId] || null) : null;
                this.simHistory.push(this.simCurrentNode);
            },
            simReset() {
                this.simCurrentNode = this.simHistory[0] || null;
                this.simHistory     = [this.simCurrentNode];
            },

            showToast(msg, type = 'success') {
                this.toast = msg; this.toastType = type;
                setTimeout(() => this.toast = null, 3000);
            },
            async apiFetch(url, method, body = null) {
                const opts = { method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'} };
                if (body) opts.body = JSON.stringify(body);
                const res = await fetch(url, opts);
                if (!res.ok) { const e = await res.json().catch(()=>({})); throw new Error(e.message||'Terjadi kesalahan.'); }
                return res.json();
            },

            openAddKategori() {
                this.katForm = { id:'', nama_kategori:'', deskripsi:'', bidang_id:'' };
                this.aktifNodeId = null;
                this.panel = 'add-kat';
            },
            selectKategori(kat) {
                this.aktifKatId  = kat.id; this.aktifNodeId = null;
                this.expandedKatId = (this.expandedKatId === kat.id) ? null : kat.id;
                this.katForm = { id:kat.id, nama_kategori:kat.nama_kategori, deskripsi:kat.deskripsi, bidang_id:kat.bidang_id };
                this.panel = 'edit-kat';
                this.resetZoom();
            },
            async saveKategori() {
                if (!this.katForm.nama_kategori.trim()) { this.showToast('Nama kategori wajib diisi.', 'error'); return; }
                this.loading = true;
                try {
                    const isNew = !this.katForm.id;
                    const url   = isNew ? '/super-admin/konfigurasi/kategori' : `/super-admin/konfigurasi/kategori/${this.katForm.id}`;
                    const data  = await this.apiFetch(url, isNew ? 'POST' : 'PUT', this.katForm);
                    if (isNew) { this.kategoris.push({...data, nodes:[]}); this.aktifKatId=data.id; this.expandedKatId=data.id; }
                    else { const i=this.kategoris.findIndex(k=>k.id===data.id); if(i!==-1) this.kategoris.splice(i,1,{...this.kategoris[i],...data}); }
                    this.katForm.id = data.id; this.panel = 'edit-kat';
                    this.showToast('Kategori berhasil disimpan.');
                } catch(e) { this.showToast(e.message,'error'); }
                finally { this.loading = false; }
            },
            openDeleteKategori() {
                this.deleteType = 'kategori';
                this.deleteItem = this.katForm.nama_kategori;
                this.deleteConfirmOpen = true;
            },
            async deleteKategori() {
                this.loading = true;
                try {
                    await this.apiFetch(`/super-admin/konfigurasi/kategori/${this.katForm.id}`, 'DELETE');
                    this.kategoris = this.kategoris.filter(k => k.id !== this.katForm.id);
                    this.aktifKatId=null; this.aktifNodeId=null; this.panel=null; this.deleteConfirmOpen=false;
                    this.showToast('Kategori berhasil dihapus.');
                } catch(e) { this.showToast(e.message,'error'); }
                finally { this.loading = false; }
            },

            openAddNode(katId) {
                this.aktifKatId=katId; this.aktifNodeId=null; this.expandedKatId=katId;
                this.nodeForm = {
                    id:'', kategori_id:katId, tipe_node:'pertanyaan',
                    teks_pertanyaan:'', hint_konteks:'',
                    judul_solusi:'', penjelasan_solusi:'', prioritas:'',
                    id_next_ya:'', id_next_tidak:'', kb_id:'', kode:'',
                    routing_ya_type:'', routing_ya_child_kode:'', routing_ya_child_text:'',
                    routing_tidak_type:'', routing_tidak_child_kode:'', routing_tidak_child_text:'',
                };
                this.panel = 'add-node';
            },
            selectNode(node, katId) {
                this.aktifKatId=katId; this.aktifNodeId=node.id; this.expandedKatId=katId;
                this.nodeForm = { ...node }; this.panel = 'edit-node';
            },
            async saveNode() {
                this.loading = true;
                try {
                    const isNew = !this.nodeForm.id;
                    const url   = isNew ? '/super-admin/konfigurasi/node' : `/super-admin/konfigurasi/node/${this.nodeForm.id}`;
                    const resp  = await this.apiFetch(url, isNew ? 'POST' : 'PUT', this.nodeForm);
                    const data       = resp.node ?? resp;
                    const newNodes   = resp.new_nodes ?? [];
                    const deletedIds = resp.deleted_node_ids ?? [];
                    const kat = this.kategoris.find(k => k.id === (this.nodeForm.kategori_id || this.aktifKatId));
                    if (kat) {
                        if (deletedIds.length) {
                            kat.nodes = kat.nodes.filter(n => !deletedIds.includes(n.id));
                            kat.nodes.forEach(n => {
                                if (deletedIds.includes(n.id_next_ya))    n.id_next_ya    = '';
                                if (deletedIds.includes(n.id_next_tidak)) n.id_next_tidak = '';
                            });
                        }
                        if (isNew) {
                            kat.nodes.push(data);
                        } else {
                            const i = kat.nodes.findIndex(n => n.id === data.id);
                            if (i !== -1) kat.nodes.splice(i, 1, data);
                        }
                        newNodes.forEach(n => { if (!kat.nodes.find(x => x.id === n.id)) kat.nodes.push(n); });
                    }
                    this.aktifNodeId = data.id;
                    this.nodeForm    = { ...data };
                    this.panel       = 'edit-node';
                    const extra = newNodes.length ? ` (${newNodes.length} node anak dibuat otomatis)` : '';
                    this.showToast('Node berhasil disimpan.' + extra);
                } catch(e) { this.showToast(e.message,'error'); }
                finally { this.loading = false; }
            },
            openDeleteNode() {
                const label = this.nodeForm.tipe_node === 'pertanyaan' ? this.nodeForm.teks_pertanyaan : this.nodeForm.judul_solusi;
                this.deleteType = 'node';
                this.deleteItem = label || '(Tanpa judul)';
                this.deleteConfirmOpen = true;
            },
            async deleteNode() {
                this.loading = true;
                try {
                    const resp = await this.apiFetch(`/super-admin/konfigurasi/node/${this.nodeForm.id}`, 'DELETE');
                    const deletedIds = resp.deleted_node_ids ?? [this.nodeForm.id];
                    const kat = this.kategoris.find(k => k.id === this.nodeForm.kategori_id);
                    if (kat) {
                        kat.nodes = kat.nodes.filter(n => !deletedIds.includes(n.id));
                        kat.nodes.forEach(n => {
                            if (deletedIds.includes(n.id_next_ya))    n.id_next_ya    = '';
                            if (deletedIds.includes(n.id_next_tidak)) n.id_next_tidak = '';
                        });
                    }
                    this.aktifNodeId=null; this.panel='edit-kat'; this.deleteConfirmOpen=false;
                    if (this.aktifKat) this.katForm = { id:this.aktifKat.id, nama_kategori:this.aktifKat.nama_kategori, deskripsi:this.aktifKat.deskripsi, bidang_id:this.aktifKat.bidang_id };
                    const extra = deletedIds.length > 1 ? ` (${deletedIds.length} node dihapus termasuk anak)` : '';
                    this.showToast('Node berhasil dihapus.' + extra);
                } catch(e) { this.showToast(e.message,'error'); }
                finally { this.loading = false; }
            },

            startResize(e) {
                e.preventDefault();
                this.isResizing = true;
                const startX = e.clientX;
                const startW = this.leftPanelWidth;
                const onMove = (ev) => {
                    this.leftPanelWidth = Math.max(200, Math.min(520, startW + ev.clientX - startX));
                };
                const onUp = () => {
                    this.isResizing = false;
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup',  onUp);
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup',  onUp);
            },
        };
    }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div class="ml-64 flex h-screen overflow-hidden" x-data="konfigPage()">

        {{-- ── Toast ── --}}
        <div x-show="toast"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed top-5 right-5 z-50 flex items-center gap-2.5 px-5 py-3 rounded-2xl shadow-lg text-sm font-medium text-white"
             :style="toastType==='error'?'background:#DC2626;':'background:#16A34A;'"
             style="display:none;">
            <svg x-show="toastType==='success'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <svg x-show="toastType==='error'"   class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            <span x-text="toast"></span>
        </div>

        {{-- ── Delete Confirmation Modal ── --}}
        <div x-show="deleteConfirmOpen" @click.self="deleteConfirmOpen=false"
             class="fixed inset-0 z-50 flex items-center justify-center"
             style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">
            <div class="bg-white rounded-3xl shadow-2xl w-[380px] overflow-hidden">
                <div class="px-6 py-4 text-white" style="background:#DC2626;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.2);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">⚠ Hapus <span x-text="deleteType==='kategori' ? 'Kategori' : 'Node'"></span></p>
                                <p class="text-xs mt-0.5" style="color:#FECACA;" x-text="deleteType==='kategori' ? 'Tindakan tidak dapat dibatalkan' : 'Hapus node ini'"></p>
                            </div>
                        </div>
                        <button @click="deleteConfirmOpen=false" class="text-red-200 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-700 mb-2">Anda yakin ingin menghapus?</p>
                    <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 mb-4">
                        <p class="text-sm font-semibold text-red-900" x-text="deleteItem"></p>
                        <template x-if="deleteType==='kategori'">
                            <p class="text-xs text-red-700 mt-1.5">Semua node di dalam kategori ini akan ikut dihapus dan tidak dapat dipulihkan.</p>
                        </template>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex gap-3">
                    <button @click="deleteConfirmOpen=false"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button @click="deleteType==='kategori' ? deleteKategori() : deleteNode()" :disabled="loading"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 disabled:opacity-50 transition-all"
                            style="background:#DC2626;">
                        <span x-text="loading ? 'Menghapus…' : 'Hapus'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Simulation Modal ── --}}
        <div x-show="simOpen" @click.self="simOpen=false"
             class="fixed inset-0 z-40 flex items-center justify-center"
             style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">
            <div class="bg-white rounded-3xl shadow-2xl w-[360px] overflow-hidden">
                <div class="px-6 py-4 text-white" style="background:#01458E;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-bold text-sm">▶ Simulasi Diagnosis</p>
                            <p class="text-xs mt-0.5" style="color:#93C5FD;" x-text="aktifKat?.nama_kategori||''"></p>
                        </div>
                        <button @click="simOpen=false" class="text-blue-300 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="p-6 min-h-[220px] flex flex-col justify-center">
                    <template x-if="simCurrentNode && simCurrentNode.tipe_node === 'pertanyaan'">
                        <div>
                            <p class="text-[10px] font-bold text-[#01458E] mb-2 font-mono" x-text="simCurrentNode.kode"></p>
                            <p class="text-sm font-semibold text-gray-800 mb-1 leading-snug" x-text="simCurrentNode.teks_pertanyaan"></p>
                            <p x-show="simCurrentNode.hint_konteks" class="text-xs text-gray-400 mb-4 leading-snug" x-text="simCurrentNode.hint_konteks"></p>
                            <div class="flex gap-2 mt-4">
                                <button @click="simAnswer('ya')"
                                        class="flex-1 py-2.5 rounded-xl text-xs font-semibold border transition-colors"
                                        style="background:#F0FDF4;border-color:#86EFAC;color:#16A34A;">
                                    ✓ Ya
                                </button>
                                <button @click="simAnswer('tidak')"
                                        class="flex-1 py-2.5 rounded-xl text-xs font-semibold border transition-colors"
                                        style="background:#FEF2F2;border-color:#FCA5A5;color:#DC2626;">
                                    ✕ Tidak
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="simCurrentNode && simCurrentNode.tipe_node === 'solusi'">
                        <div class="text-center">
                            <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-[10px] font-bold text-purple-600 mb-1">Solusi Ditemukan</p>
                            <p class="text-sm font-semibold text-gray-800 mb-2 leading-snug" x-text="simCurrentNode.judul_solusi"></p>
                            <p x-show="simCurrentNode.penjelasan_solusi" class="text-xs text-gray-500 mb-2 leading-relaxed" x-text="simCurrentNode.penjelasan_solusi"></p>
                            <p x-show="simCurrentNode.kb_judul" class="text-xs text-purple-500 font-medium" x-text="'📄 ' + simCurrentNode.kb_judul"></p>
                        </div>
                    </template>
                    <template x-if="!simCurrentNode">
                        <div class="text-center py-4">
                            <div class="w-10 h-10 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-600 mb-1">Akhir Alur</p>
                            <p class="text-xs text-gray-400">Tidak ada node lanjutan yang ditentukan.</p>
                        </div>
                    </template>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                    <button @click="simReset()" x-show="simHistory.length > 1"
                            class="text-xs text-gray-400 hover:text-gray-600 transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Ulangi
                    </button>
                    <span x-show="simHistory.length <= 1"></span>
                    <button @click="simOpen=false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════
             LEFT PANEL — Pohon Diagnosis
        ════════════════════════════════════════════════════ --}}
        <div :style="`width:${leftPanelWidth}px`"
             class="shrink-0 bg-white border-r border-gray-100 flex flex-col overflow-hidden relative"
             :class="isResizing ? 'select-none' : ''"
             style="width:280px;">
            <div @mousedown="startResize($event)"
                 :class="isResizing ? 'resizing' : ''"
                 class="panel-resize-handle"></div>

            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div>
                    <p class="text-sm font-bold text-gray-900">Pohon Diagnosis</p>
                    <p class="text-[11px] text-gray-400 mt-0.5" x-text="kategoris.length + ' kategori'"></p>
                </div>
                <button @click="openAddKategori()"
                        class="w-7 h-7 rounded-full flex items-center justify-center text-white hover:opacity-80 transition-opacity"
                        style="background:#01458E;" title="Tambah Kategori">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto ps py-2">
                <template x-for="kat in kategoris" :key="kat.id">
                    <div class="mb-1">
                        <div @click="selectKategori(kat)"
                             :class="aktifKatId===kat.id ? 'border-l-[3px] border-[#01458E] bg-blue-50/60' : 'border-l-[3px] border-transparent hover:bg-gray-50'"
                             class="flex items-center gap-2.5 mb-4 px-4 py-2.5 cursor-pointer transition-colors">
                            <svg :class="expandedKatId===kat.id ? 'rotate-90' : ''"
                                 class="w-3 h-3 text-gray-400 transition-transform duration-150 shrink-0"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate" x-text="kat.nama_kategori"></p>
                                <p class="text-[10px] text-gray-400" x-text="kat.nodes.length + ' node'"></p>
                            </div>
                        </div>

                        <div x-show="expandedKatId===kat.id"
                             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                             class="ml-2 pb-1">
                            <div x-html="katTreeHtml(kat)"></div>
                            <button @click.stop="openAddNode(kat.id)"
                                    class="w-full text-left flex items-center gap-1.5 px-4 py-2 text-[11px] text-gray-400 hover:text-[#01458E] transition-colors rounded-lg">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Node
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="kategoris.length===0" class="px-5 py-10 text-center">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5 text-[#01458E]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-500 mb-1">Belum ada kategori</p>
                    <p class="text-[11px] text-gray-300">Klik + di atas untuk mulai.</p>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════
             CENTER — Canvas Flow Diagram
        ════════════════════════════════════════════════════ --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- Topbar --}}
            <header class="bg-white border-b border-gray-100 px-6 py-3.5 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-400 text-xs">Konfigurasi</span>
                    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="font-bold text-gray-800 text-sm" x-text="aktifKat ? aktifKat.nama_kategori : 'Alur Diagnosis'"></span>
                    <span x-show="aktifKat" class="text-[10px] px-2 py-0.5 rounded-full bg-blue-50 font-semibold"
                          style="color:#01458E;" x-text="aktifNodes.length + ' node'"></span>
                </div>
                <div class="flex items-center gap-2">
                    <button x-show="aktifKat" @click="openAddNode(aktifKatId)"
                            class="flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold border border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Node Baru
                    </button>
                    <button x-show="aktifKat && aktifNodes.length > 0" @click="openSim()"
                            class="flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold text-white hover:opacity-90 transition-opacity"
                            style="background:#01458E;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                        </svg>
                        Simulasi
                    </button>
                    <button @click="openAddKategori()"
                            class="flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold border text-[#01458E] hover:bg-blue-50 transition-colors"
                            style="border-color:#01458E;">
                        + Kategori
                    </button>
                </div>
            </header>

            {{-- Legend & Zoom Controls --}}
            <div class="px-6 pt-3 pb-0 flex items-center justify-between shrink-0">
                <template x-if="aktifKat">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 rounded border-2 border-blue-400 bg-blue-50"></div>
                            <span class="text-[10px] text-gray-500 font-medium">Pertanyaan</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 rounded border-2 border-purple-400 bg-purple-50"></div>
                            <span class="text-[10px] text-gray-500 font-medium">Solusi</span>
                        </div>
                        <span class="text-[10px] text-gray-400 ml-2">Klik node untuk mengedit</span>
                    </div>
                </template>

                {{-- Zoom Controls Widget --}}
                <div class="flex items-center ml-auto bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden h-8">
                    <button @click="zoomOut()" class="w-8 h-full flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition-colors" title="Zoom Out">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                    </button>
                    <div class="w-12 h-full flex items-center justify-center border-x border-gray-100 bg-gray-50/50">
                        <span class="text-[10px] font-bold text-gray-600 font-mono" x-text="Math.round(zoomLevel * 100) + '%'"></span>
                    </div>
                    <button @click="zoomIn()" class="w-8 h-full flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition-colors" title="Zoom In">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </button>
                    <button @click="resetZoom()" class="w-8 h-full flex items-center justify-center border-l border-gray-200 text-gray-400 hover:bg-gray-100 hover:text-blue-600 transition-colors" title="Reset Zoom">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
            </div>

            {{-- Flow canvas dengan proper Zoom support & Scroll --}}
            <div id="canvas-scroll"
                 class="flex-1 ps canvas-bg mt-3 overflow-auto flex justify-center" style="min-height:0;">
                <div class="min-w-max min-h-max p-8 transition-transform duration-200 origin-top"
                     :style="`transform: scale(${zoomLevel}); transform-origin: top center;`">
                    <div x-html="flowHtml"></div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════
             RIGHT PANEL — Editor
        ════════════════════════════════════════════════════ --}}
        <div x-show="panel"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-x-4"
             class="w-80 shrink-0 bg-white border-l border-gray-100 flex flex-col overflow-hidden"
             style="display:none;">

            {{-- ══ Kategori panel ══ --}}
            <template x-if="panel === 'add-kat' || panel === 'edit-kat'">
                <div class="flex flex-col h-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm" x-text="panel==='add-kat' ? 'Tambah Kategori' : 'Edit Kategori'"></h3>
                            <p x-show="panel==='edit-kat'" class="text-[11px] font-semibold mt-0.5 text-[#01458E]"
                               x-text="'C-' + katForm.id?.substring(0,5)?.toUpperCase()"></p>
                        </div>
                        <button @click="panel=null" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto ps px-6 py-4 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Kategori <span class="text-red-400">*</span></label>
                            <input type="text" x-model="katForm.nama_kategori" placeholder="Contoh: Jaringan/Internet"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deskripsi</label>
                            <textarea x-model="katForm.deskripsi" rows="3" placeholder="Deskripsi singkat kategori..."
                                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ditangani oleh Bidang</label>
                            <select x-model="katForm.bidang_id"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] bg-white text-gray-700">
                                <option value="">— Pilih Bidang —</option>
                                @foreach($bidangsData as $b)
                                    <option value="{{ $b['id'] }}">{{ $b['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex gap-2 shrink-0">
                        <button x-show="panel==='edit-kat'" @click="openDeleteKategori()" :disabled="loading"
                                class="px-4 py-2.5 rounded-xl text-sm font-semibold text-red-500 border border-red-200 hover:bg-red-50 transition-colors disabled:opacity-50">
                            Hapus
                        </button>
                        <button @click="saveKategori()" :disabled="loading"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 disabled:opacity-50"
                                style="background:#01458E;">
                            <span x-text="loading ? 'Menyimpan…' : 'Simpan'"></span>
                        </button>
                    </div>
                </div>
            </template>

            {{-- ══ Node panel ══ --}}
            <template x-if="panel === 'add-node' || panel === 'edit-node'">
                <div class="flex flex-col h-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm"
                                x-text="panel==='add-node' ? 'Tambah Node' : (nodeForm.tipe_node==='pertanyaan' ? 'Edit Pertanyaan' : 'Edit Solusi')"></h3>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span x-show="nodeForm.tipe_node==='pertanyaan'"
                                      class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-600">? Pertanyaan</span>
                                <span x-show="nodeForm.tipe_node==='solusi'"
                                      class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-100 text-purple-600" style="display:none;">• Solusi</span>
                                <span x-show="panel==='edit-node'" class="text-[10px] text-gray-400" x-text="nodeForm.kode"></span>
                            </div>
                        </div>
                        <button @click="panel='edit-kat'; aktifNodeId=null" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto ps px-6 py-4 space-y-4">

                        <div x-show="panel==='add-node'">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe Node</label>
                            <div class="flex gap-2">
                                <button type="button" @click="nodeForm.tipe_node='pertanyaan'"
                                        :style="nodeForm.tipe_node==='pertanyaan' ? 'background:#EFF6FF;border-color:#01458E;color:#01458E;' : 'background:#fff;border-color:#E5E7EB;color:#9CA3AF;'"
                                        class="flex-1 py-2 rounded-xl text-xs font-semibold border transition-all">
                                    ? Pertanyaan
                                </button>
                                <button type="button"
                                        @click="aktifNodes.length > 0 && (nodeForm.tipe_node='solusi')"
                                        :disabled="aktifNodes.length === 0"
                                        :title="aktifNodes.length === 0 ? 'Node pertama harus bertipe Pertanyaan' : ''"
                                        :style="aktifNodes.length === 0
                                            ? 'background:#F9FAFB;border-color:#E5E7EB;color:#D1D5DB;cursor:not-allowed;'
                                            : (nodeForm.tipe_node==='solusi' ? 'background:#F5F3FF;border-color:#7C3AED;color:#7C3AED;' : 'background:#fff;border-color:#E5E7EB;color:#9CA3AF;')"
                                        class="flex-1 py-2 rounded-xl text-xs font-semibold border transition-all">
                                    • Solusi
                                </button>
                            </div>
                            <p x-show="aktifNodes.length === 0" class="text-[10px] text-amber-600 mt-1.5 font-medium">
                                ⚠ Node pertama dalam kategori harus bertipe Pertanyaan
                            </p>
                        </div>

                        <template x-if="nodeForm.tipe_node === 'pertanyaan'">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Teks Pertanyaan <span class="text-red-400">*</span></label>
                                    <textarea x-model="nodeForm.teks_pertanyaan" rows="3"
                                              placeholder="Masukkan pertanyaan diagnosis..."
                                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 resize-none"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Hint Konteks</label>
                                    <textarea x-model="nodeForm.hint_konteks" rows="2"
                                              placeholder="Petunjuk tambahan untuk pengguna..."
                                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 resize-none"></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Routing Jawaban</label>
                                    <p class="text-[11px] text-gray-400 mb-2.5 leading-snug">Node selesai jika <strong>kedua</strong> cabang → Solusi KB. Memilih "Lanjutan" akan membuat node otomatis.</p>
                                    <div class="space-y-3">

                                        <div class="rounded-xl border border-green-200 overflow-hidden">
                                            <div class="px-3 py-2 flex items-center justify-between" style="background:#F0FDF4;">
                                                <p class="text-[11px] font-bold text-green-700">✓ Jika YA</p>
                                                <span x-show="nodeForm.routing_ya_type==='pertanyaan'" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-600">Lanjutan</span>
                                                <span x-show="nodeForm.routing_ya_type==='solusi'" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-600" style="display:none;">Solusi</span>
                                                <span x-show="!nodeForm.routing_ya_type" class="text-[9px] font-semibold px-1.5 py-0.5 rounded bg-gray-100 text-gray-400">Belum diatur</span>
                                            </div>
                                            <div class="p-3 space-y-2">
                                                <div class="flex gap-1.5">
                                                    <button type="button" @click="nodeForm.routing_ya_type='pertanyaan'"
                                                            :style="nodeForm.routing_ya_type==='pertanyaan' ? 'background:#EFF6FF;border-color:#01458E;color:#01458E;font-weight:700;' : 'background:#fff;border-color:#E5E7EB;color:#9CA3AF;'"
                                                            class="flex-1 py-2 rounded-lg text-[10px] font-semibold border transition-all">? Pertanyaan Lanjutan</button>
                                                    <button type="button" @click="nodeForm.routing_ya_type='solusi'"
                                                            :style="nodeForm.routing_ya_type==='solusi' ? 'background:#F5F3FF;border-color:#7C3AED;color:#7C3AED;font-weight:700;' : 'background:#fff;border-color:#E5E7EB;color:#9CA3AF;'"
                                                            class="flex-1 py-2 rounded-lg text-[10px] font-semibold border transition-all">• Solusi KB</button>
                                                </div>
                                                <div x-show="nodeForm.routing_ya_type==='pertanyaan'" style="display:none;" class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2.5">
                                                    <template x-if="nodeForm.routing_ya_child_kode">
                                                        <div class="flex items-start gap-2">
                                                            <span class="shrink-0 mt-0.5 text-[9px] font-bold font-mono bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded" x-text="nodeForm.routing_ya_child_kode"></span>
                                                            <div>
                                                                <p class="text-[10px] font-semibold text-[#01458E]">Node sudah terhubung</p>
                                                                <p class="text-[11px] text-gray-600 leading-snug mt-0.5" x-text="nodeForm.routing_ya_child_text || '(Teks pertanyaan belum diisi)'"></p>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="!nodeForm.routing_ya_child_kode">
                                                        <p class="text-[11px] text-blue-600 font-medium">✦ Node pertanyaan baru akan dibuat otomatis saat disimpan</p>
                                                    </template>
                                                </div>
                                                <div x-show="nodeForm.routing_ya_type==='solusi'" style="display:none;" class="rounded-lg border border-purple-100 bg-purple-50 px-3 py-2.5">
                                                    <template x-if="nodeForm.routing_ya_child_kode">
                                                        <div class="flex items-start gap-2">
                                                            <span class="shrink-0 mt-0.5 text-[9px] font-bold font-mono bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded" x-text="nodeForm.routing_ya_child_kode"></span>
                                                            <div>
                                                                <p class="text-[10px] font-semibold text-purple-700">Node solusi sudah terhubung</p>
                                                                <p class="text-[11px] text-gray-600 leading-snug mt-0.5" x-text="nodeForm.routing_ya_child_text || '(Judul belum diisi)'"></p>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="!nodeForm.routing_ya_child_kode">
                                                        <p class="text-[11px] text-purple-600 font-medium">✦ Node solusi baru akan dibuat otomatis saat disimpan</p>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-xl border border-red-200 overflow-hidden">
                                            <div class="px-3 py-2 flex items-center justify-between" style="background:#FFF5F5;">
                                                <p class="text-[11px] font-bold text-red-600">✕ Jika TIDAK</p>
                                                <span x-show="nodeForm.routing_tidak_type==='pertanyaan'" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-600">Lanjutan</span>
                                                <span x-show="nodeForm.routing_tidak_type==='solusi'" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-600" style="display:none;">Solusi</span>
                                                <span x-show="!nodeForm.routing_tidak_type" class="text-[9px] font-semibold px-1.5 py-0.5 rounded bg-gray-100 text-gray-400">Belum diatur</span>
                                            </div>
                                            <div class="p-3 space-y-2">
                                                <div class="flex gap-1.5">
                                                    <button type="button" @click="nodeForm.routing_tidak_type='pertanyaan'"
                                                            :style="nodeForm.routing_tidak_type==='pertanyaan' ? 'background:#EFF6FF;border-color:#01458E;color:#01458E;font-weight:700;' : 'background:#fff;border-color:#E5E7EB;color:#9CA3AF;'"
                                                            class="flex-1 py-2 rounded-lg text-[10px] font-semibold border transition-all">? Pertanyaan Lanjutan</button>
                                                    <button type="button" @click="nodeForm.routing_tidak_type='solusi'"
                                                            :style="nodeForm.routing_tidak_type==='solusi' ? 'background:#F5F3FF;border-color:#7C3AED;color:#7C3AED;font-weight:700;' : 'background:#fff;border-color:#E5E7EB;color:#9CA3AF;'"
                                                            class="flex-1 py-2 rounded-lg text-[10px] font-semibold border transition-all">• Solusi KB</button>
                                                </div>
                                                <div x-show="nodeForm.routing_tidak_type==='pertanyaan'" style="display:none;" class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2.5">
                                                    <template x-if="nodeForm.routing_tidak_child_kode">
                                                        <div class="flex items-start gap-2">
                                                            <span class="shrink-0 mt-0.5 text-[9px] font-bold font-mono bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded" x-text="nodeForm.routing_tidak_child_kode"></span>
                                                            <div>
                                                                <p class="text-[10px] font-semibold text-[#01458E]">Node sudah terhubung</p>
                                                                <p class="text-[11px] text-gray-600 leading-snug mt-0.5" x-text="nodeForm.routing_tidak_child_text || '(Teks pertanyaan belum diisi)'"></p>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="!nodeForm.routing_tidak_child_kode">
                                                        <p class="text-[11px] text-blue-600 font-medium">✦ Node pertanyaan baru otomatis dibuat</p>
                                                    </template>
                                                </div>
                                                <div x-show="nodeForm.routing_tidak_type==='solusi'" style="display:none;" class="rounded-lg border border-purple-100 bg-purple-50 px-3 py-2.5">
                                                    <template x-if="nodeForm.routing_tidak_child_kode">
                                                        <div class="flex items-start gap-2">
                                                            <span class="shrink-0 mt-0.5 text-[9px] font-bold font-mono bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded" x-text="nodeForm.routing_tidak_child_kode"></span>
                                                            <div>
                                                                <p class="text-[10px] font-semibold text-purple-700">Node solusi sudah terhubung</p>
                                                                <p class="text-[11px] text-gray-600 leading-snug mt-0.5" x-text="nodeForm.routing_tidak_child_text || '(Judul belum diisi)'"></p>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="!nodeForm.routing_tidak_child_kode">
                                                        <p class="text-[11px] text-purple-600 font-medium">✦ Node solusi baru otomatis dibuat</p>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <div x-show="nodeForm.routing_ya_type==='solusi' && nodeForm.routing_tidak_type==='solusi'"
                                             class="flex items-center gap-2 px-3 py-2 rounded-xl bg-green-50 border border-green-200">
                                            <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <p class="text-[11px] font-semibold text-green-700">Node selesai — kedua cabang mengarah ke solusi</p>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="nodeForm.tipe_node === 'solusi'">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Judul Solusi <span class="text-red-400">*</span></label>
                                    <input type="text" x-model="nodeForm.judul_solusi" placeholder="Judul solusi..."
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Penjelasan Solusi</label>
                                    <textarea x-model="nodeForm.penjelasan_solusi" rows="3" placeholder="Langkah-langkah solusi..."
                                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 resize-none"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prioritas</label>
                                    <select x-model="nodeForm.prioritas"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] bg-white text-gray-700">
                                        <option value="">— Pilih Prioritas —</option>
                                        <option value="rendah">Rendah</option>
                                        <option value="sedang">Sedang</option>
                                        <option value="tinggi">Tinggi</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Artikel Solusi (KB)</label>
                                    <template x-if="filteredArticles.length > 0">
                                        <div>
                                            <select x-model="nodeForm.kb_id"
                                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] bg-white text-gray-700">
                                                <option value="">— Pilih Artikel —</option>
                                                <template x-for="a in filteredArticles" :key="a.id">
                                                    <option :value="a.id" x-text="a.judul"></option>
                                                </template>
                                            </select>
                                            <p x-show="nodeForm.kb_judul" class="text-[11px] text-[#01458E] mt-1.5 font-medium" x-text="'✓ ' + nodeForm.kb_judul"></p>
                                        </div>
                                    </template>
                                    <template x-if="filteredArticles.length === 0">
                                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                                            <p class="text-xs font-semibold text-amber-700">Belum ada artikel KB</p>
                                            <p class="text-[11px] text-amber-600 mt-1 leading-snug">Tambahkan artikel KB terlebih dahulu lalu hubungkan di sini.</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 flex gap-2 shrink-0">
                        <button x-show="panel==='edit-node'" @click="openDeleteNode()" :disabled="loading"
                                class="px-4 py-2.5 rounded-xl text-sm font-semibold text-red-500 border border-red-200 hover:bg-red-50 transition-colors disabled:opacity-50">
                            Hapus
                        </button>
                        <button @click="saveNode()" :disabled="loading"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 disabled:opacity-50"
                                style="background:#01458E;">
                            <span x-text="loading ? 'Menyimpan…' : 'Simpan'"></span>
                        </button>
                    </div>
                </div>
            </template>

        </div>

    </div>

</body>
</html>
