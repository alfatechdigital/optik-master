@extends('layouts.admin')
@section('title', 'POS / Kasir')
@section('page-title', 'Point of Sale')

@push('styles')
<style>
.pos-search-result {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
    background: #fff; border: 1px solid #dee2e6; border-radius: 0 0 8px 8px;
    max-height: 280px; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,.1);
}
.pos-product-item {
    padding: 10px 14px; cursor: pointer; display: flex;
    justify-content: space-between; align-items: center;
    border-bottom: 1px solid #f0f0f0; transition: background .1s;
}
.pos-product-item:hover { background: #f8f9ff; }
.cart-table td { vertical-align: middle; }
.numpad-btn { font-size: .95rem; font-weight: 600; }
#total-display { font-size: 2rem; font-weight: 700; color: #1e2a5e; }
#kembalian-display { font-size: 1.4rem; font-weight: 600; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('transactions.store') }}" id="pos-form">
@csrf
<div class="row g-3">
    {{-- LEFT: Produk & Cart --}}
    <div class="col-lg-8">
        {{-- Search Produk --}}
        <div class="card mb-3">
            <div class="card-body p-3">
                <div class="position-relative">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="search-product"
                               class="form-control form-control-lg"
                               placeholder="Cari produk / kode / merek... (ketik minimal 2 huruf)"
                               autocomplete="off">
                    </div>
                    <div class="pos-search-result d-none" id="search-result"></div>
                </div>
            </div>
        </div>

        {{-- Cart --}}
        <div class="card">
            <div class="card-header p-3 d-flex justify-content-between align-items-center">
                <div><i class="bi bi-cart3 text-primary me-2"></i>Keranjang Belanja</div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCart()">
                    <i class="bi bi-trash me-1"></i>Kosongkan
                </button>
            </div>
            <div class="table-responsive">
                <table class="table cart-table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40%">Produk</th>
                            <th>Harga</th>
                            <th style="width:120px">Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr id="cart-empty">
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-25"></i>
                                Belum ada produk dipilih
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Diskon --}}
            <div class="p-3 border-top bg-light">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label class="form-label mb-0 fw-semibold">Diskon:</label>
                    </div>
                    <div class="col-auto">
                        <div class="input-group input-group-sm">
                            <input type="number" id="diskon-persen" name="diskon_persen"
                                   class="form-control" style="width:80px"
                                   min="0" max="100" value="0" placeholder="0">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-auto text-muted">atau</div>
                    <div class="col-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="number" id="diskon-nominal" name="diskon_nominal"
                                   class="form-control" style="width:130px"
                                   min="0" value="0" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Summary & Payment --}}
    <div class="col-lg-4">
        {{-- Pasien (opsional) --}}
        <div class="card mb-3">
            <div class="card-header p-3"><i class="bi bi-person text-primary me-2"></i>Pasien (Opsional)</div>
            <div class="card-body p-3">
                <select name="patient_id" id="patient-select" class="form-select form-select-sm mb-2">
                    <option value="">-- Tanpa Pasien (Umum) --</option>
                    @foreach($patients as $p)
                    <option value="{{ $p->id }}">{{ $p->no_rm }} — {{ $p->nama }}</option>
                    @endforeach
                </select>
                <select name="medical_record_id" id="med-rec-select" class="form-select form-select-sm">
                    <option value="">-- Pilih Rekam Medis --</option>
                </select>
            </div>
        </div>

        {{-- Total --}}
        <div class="card mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span id="subtotal-text">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Diskon</span>
                    <span id="diskon-text" class="text-danger">- Rp 0</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total Bayar</span>
                    <div id="total-display">Rp 0</div>
                </div>
            </div>
        </div>

        {{-- Metode Bayar & Nominal --}}
        <div class="card mb-3">
            <div class="card-header p-3"><i class="bi bi-credit-card text-primary me-2"></i>Pembayaran</div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Metode Bayar</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['tunai'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS','debit'=>'Debit','kredit'=>'Kredit'] as $val => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metode_bayar"
                                   id="mp_{{ $val }}" value="{{ $val }}" {{ $val=='tunai'?'checked':'' }}>
                            <label class="form-check-label" for="mp_{{ $val }}">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Jumlah Bayar</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="bayar" id="bayar-input"
                               class="form-control form-control-lg fw-bold"
                               min="0" value="0" required>
                    </div>
                </div>
                {{-- Nominal cepat --}}
                <div class="d-flex flex-wrap gap-1 mb-3">
                    @foreach([50000,100000,200000,500000] as $nom)
                    <button type="button" class="btn btn-outline-secondary btn-sm numpad-btn"
                            onclick="setBayar({{ $nom }})">
                        {{ number_format($nom/1000) }}rb
                    </button>
                    @endforeach
                    <button type="button" class="btn btn-outline-primary btn-sm"
                            onclick="setBayarPas()">Pas</button>
                </div>
                <div class="d-flex justify-content-between align-items-center p-2 rounded"
                     style="background:#f0fdf4">
                    <span class="fw-semibold">Kembalian</span>
                    <div id="kembalian-display" class="text-success">Rp 0</div>
                </div>
                <textarea name="catatan" class="form-control form-control-sm mt-2"
                          rows="2" placeholder="Catatan transaksi..."></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold" id="btn-bayar">
            <i class="bi bi-check-circle me-2"></i>Proses Pembayaran
        </button>
    </div>
</div>
</form>

{{-- Hidden inputs untuk items --}}
<div id="hidden-items"></div>

@endsection

@push('scripts')
<script>
const SEARCH_URL   = "{{ route('transactions.product.search') }}";
const MED_REC_URL  = "{{ route('transactions.product.search') }}".replace('product/search','');
const CSRF         = document.querySelector('meta[name=csrf-token]').content;

let cart = {};

// ===================== SEARCH PRODUK =====================
let searchTimeout;
document.getElementById('search-product').addEventListener('input', function () {
    clearTimeout(searchTimeout);
    const q = this.value.trim();
    if (q.length < 2) { document.getElementById('search-result').classList.add('d-none'); return; }
    searchTimeout = setTimeout(() => fetchProducts(q), 300);
});

async function fetchProducts(q) {
    const res  = await fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`);
    const data = await res.json();
    const el   = document.getElementById('search-result');
    if (!data.length) {
        el.innerHTML = '<div class="pos-product-item text-muted">Produk tidak ditemukan</div>';
    } else {
        el.innerHTML = data.map(p => `
            <div class="pos-product-item" onclick='addToCart(${JSON.stringify(p)})'>
                <div>
                    <div class="fw-semibold">${p.nama}</div>
                    <small class="text-muted">${p.kode_produk} ${p.merek ? '· '+p.merek : ''}</small>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-primary">Rp ${formatNum(p.harga_jual)}</div>
                    <small class="text-muted">Stok: ${p.stok}</small>
                </div>
            </div>`).join('');
    }
    el.classList.remove('d-none');
}

document.addEventListener('click', e => {
    if (!e.target.closest('#search-product') && !e.target.closest('#search-result')) {
        document.getElementById('search-result').classList.add('d-none');
    }
});

// ===================== CART =====================
function addToCart(p) {
    if (cart[p.id]) {
        if (cart[p.id].qty >= p.stok) { alert(`Stok ${p.nama} hanya ${p.stok}`); return; }
        cart[p.id].qty++;
    } else {
        cart[p.id] = { ...p, harga_satuan: p.harga_jual, qty: 1 };
    }
    document.getElementById('search-product').value = '';
    document.getElementById('search-result').classList.add('d-none');
    renderCart();
}

function changeQty(id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) delete cart[id];
    renderCart();
}

function removeItem(id) {
    delete cart[id];
    renderCart();
}

function clearCart() {
    cart = {};
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cart-body');
    const empty = document.getElementById('cart-empty');
    const items = Object.values(cart);

    if (!items.length) {
        tbody.innerHTML = `<tr id="cart-empty">
            <td colspan="5" class="text-center text-muted py-5">
                <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-25"></i>Belum ada produk dipilih
            </td></tr>`;
        updateTotal();
        syncHiddenInputs();
        return;
    }

    tbody.innerHTML = items.map((item, i) => `
        <tr>
            <td class="ps-3">
                <div class="fw-semibold">${item.nama}</div>
                <small class="text-muted">${item.kode_produk}</small>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" style="width:110px"
                       value="${item.harga_satuan}" min="0"
                       onchange="updateHarga(${item.id}, this.value)">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <button type="button" class="btn btn-outline-secondary" onclick="changeQty(${item.id},-1)">−</button>
                    <input type="text" class="form-control text-center" value="${item.qty}" readonly style="max-width:45px">
                    <button type="button" class="btn btn-outline-secondary" onclick="changeQty(${item.id},1)">+</button>
                </div>
            </td>
            <td class="fw-semibold">Rp ${formatNum(item.harga_satuan * item.qty)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${item.id})">
                    <i class="bi bi-x"></i>
                </button>
            </td>
        </tr>`).join('');

    updateTotal();
    syncHiddenInputs();
}

function updateHarga(id, val) {
    if (cart[id]) { cart[id].harga_satuan = parseFloat(val) || 0; renderCart(); }
}

// ===================== TOTAL =====================
function updateTotal() {
    const items       = Object.values(cart);
    const subtotal    = items.reduce((s, i) => s + (i.harga_satuan * i.qty), 0);
    const diskonP     = parseFloat(document.getElementById('diskon-persen').value) || 0;
    let   diskonNom   = parseFloat(document.getElementById('diskon-nominal').value) || 0;
    if (diskonP > 0) diskonNom = Math.round(subtotal * diskonP / 100);

    const total       = Math.max(0, subtotal - diskonNom);
    const bayar       = parseFloat(document.getElementById('bayar-input').value) || 0;
    const kembalian   = bayar - total;

    document.getElementById('subtotal-text').textContent  = 'Rp ' + formatNum(subtotal);
    document.getElementById('diskon-text').textContent    = '- Rp ' + formatNum(diskonNom);
    document.getElementById('total-display').textContent  = 'Rp ' + formatNum(total);
    document.getElementById('kembalian-display').textContent = 'Rp ' + formatNum(Math.max(0, kembalian));
    document.getElementById('kembalian-display').style.color = kembalian < 0 ? '#dc3545' : '#16a34a';
}

document.getElementById('diskon-persen').addEventListener('input', updateTotal);
document.getElementById('diskon-nominal').addEventListener('input', updateTotal);
document.getElementById('bayar-input').addEventListener('input', updateTotal);

function setBayar(val) {
    document.getElementById('bayar-input').value = val;
    updateTotal();
}
function setBayarPas() {
    const items     = Object.values(cart);
    const subtotal  = items.reduce((s, i) => s + (i.harga_satuan * i.qty), 0);
    const diskonP   = parseFloat(document.getElementById('diskon-persen').value) || 0;
    let   diskonNom = parseFloat(document.getElementById('diskon-nominal').value) || 0;
    if (diskonP > 0) diskonNom = Math.round(subtotal * diskonP / 100);
    document.getElementById('bayar-input').value = Math.max(0, subtotal - diskonNom);
    updateTotal();
}

// ===================== HIDDEN INPUTS =====================
function syncHiddenInputs() {
    const container = document.getElementById('hidden-items');
    container.innerHTML = Object.values(cart).map((item, i) => `
        <input type="hidden" name="items[${i}][product_id]"   value="${item.id}">
        <input type="hidden" name="items[${i}][qty]"           value="${item.qty}">
        <input type="hidden" name="items[${i}][harga_satuan]"  value="${item.harga_satuan}">
        <input type="hidden" name="items[${i}][diskon]"        value="0">
    `).join('');
}

// ===================== REKAM MEDIS AJAX =====================
document.getElementById('patient-select').addEventListener('change', async function () {
    const patientId = this.value;
    const sel = document.getElementById('med-rec-select');
    sel.innerHTML = '<option value="">-- Pilih Rekam Medis --</option>';
    if (!patientId) return;

    try {
        const res  = await fetch(`{{ route('transactions.medical-records') }}?patient_id=${patientId}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.length === 0) {
            sel.innerHTML += '<option value="" disabled>Belum ada rekam medis</option>';
        } else {
            data.forEach(r => {
                sel.innerHTML += `<option value="${r.id}">${r.tanggal_kunjungan} — OD: ${r.od_sph} OS: ${r.os_sph}</option>`;
            });
        }
    } catch(e) {
        console.error('Error fetch rekam medis:', e);
    }
});

// ===================== SUBMIT VALIDATION =====================
document.getElementById('pos-form').addEventListener('submit', function (e) {
    if (!Object.keys(cart).length) {
        e.preventDefault();
        alert('Keranjang belanja kosong! Tambahkan produk terlebih dahulu.');
        return;
    }
    syncHiddenInputs();
    this.appendChild(document.getElementById('hidden-items'));
});

function formatNum(n) {
    return new Intl.NumberFormat('id-ID').format(Math.round(n));
}
</script>
@endpush
