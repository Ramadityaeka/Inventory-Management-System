<ul class="nav flex-column">
    @if(auth()->user()->isSuperAdmin())
        <!-- Super Admin Menu -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>

        <li class="nav-item mt-3">
            <small class="text-muted px-3 text-uppercase fw-bold">Master Data</small>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <i class="bi bi-people me-2"></i>Manajemen User
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}" href="{{ route('admin.warehouses.index') }}">
                <i class="bi bi-shop me-2"></i>Unit
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                <i class="bi bi-tag me-2"></i>Kategori
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}" href="{{ route('admin.suppliers.index') }}">
                <i class="bi bi-truck me-2"></i>Supplier
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.items.*') ? 'active' : '' }}" href="{{ route('admin.items.index') }}">
                <i class="bi bi-box-seam me-2"></i>Barang
            </a>
        </li>

        <li class="nav-item mt-3">
            <small class="text-muted px-3 text-uppercase fw-bold">Operasional</small>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#reportsMenu" role="button" aria-expanded="{{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }}">
                <i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}" id="reportsMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.reports.transactions') ? 'active' : '' }}" href="{{ route('admin.reports.transactions') }}">
                            <i class="bi bi-arrow-left-right me-2"></i>Laporan Transaksi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.reports.stock-values') ? 'active' : '' }}" href="{{ route('admin.reports.stock-values') }}">
                            <i class="bi bi-currency-dollar me-2"></i>Laporan Stok & Nilai
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @elseif(auth()->user()->isAdminGudang())
        <!-- Admin Gudang Menu -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>

        <li class="nav-item mt-3">
            <small class="text-muted px-3 text-uppercase fw-bold">Operasional</small>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('gudang.submissions.*') ? 'active' : '' }}" href="{{ route('gudang.submissions.index') }}">
                <i class="bi bi-clipboard-check me-2"></i>Verifikasi Barang Masuk
                @if(isset($pendingSubmissions) && $pendingSubmissions > 0)
                    <span class="badge bg-danger rounded-pill float-end">{{ $pendingSubmissions }}</span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('gudang.stock-requests.*') ? 'active' : '' }}" href="{{ route('gudang.stock-requests.index') }}">
                <i class="bi bi-box-arrow-right me-2"></i>Verifikasi Barang Keluar
                @if(isset($pendingStockRequests) && $pendingStockRequests > 0)
                    <span class="badge bg-warning rounded-pill float-end">{{ $pendingStockRequests }}</span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('gudang.stocks.*') ? 'active' : '' }}" href="{{ route('gudang.stocks.index') }}">
                <i class="bi bi-boxes me-2"></i>Stok Unit
            </a>
        </li>

        <li class="nav-item mt-3">
            <small class="text-muted px-3 text-uppercase fw-bold">Laporan</small>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('gudang.reports.movements') ? 'active' : '' }}" href="{{ route('gudang.reports.movements') }}">
                <i class="bi bi-arrow-left-right me-2"></i>Perpindahan Barang
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('gudang.reports.in-out') ? 'active' : '' }}" href="{{ route('gudang.reports.in-out') }}">
                <i class="bi bi-arrow-down-up me-2"></i>Barang Masuk & Keluar
            </a>
        </li>
        
    @elseif(auth()->user()->isStaffGudang())
        <!-- Staff Gudang Menu -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>

        <li class="nav-item mt-3">
            <small class="text-muted px-3 text-uppercase fw-bold">Barang Masuk</small>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.receive-items.create') ? 'active' : '' }}" href="{{ route('staff.receive-items.create') }}">
                <i class="bi bi-plus-circle me-2"></i>Input Barang Masuk
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.drafts.*') ? 'active' : '' }}" href="{{ route('staff.drafts') }}">
                <i class="bi bi-file-earmark-text me-2"></i>Draft Tersimpan
                @if(isset($draftCount) && $draftCount > 0)
                    <span class="badge bg-info rounded-pill float-end">{{ $draftCount }}</span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.receive-items.index') ? 'active' : '' }}" href="{{ route('staff.receive-items.index') }}">
                <i class="bi bi-list-check me-2"></i>Riwayat Submission
            </a>
        </li>

        <li class="nav-item mt-3">
            <small class="text-muted px-3 text-uppercase fw-bold">Pengeluaran Barang</small>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.stock-requests.index') ? 'active' : '' }}" href="{{ route('staff.stock-requests.index') }}">
                <i class="bi bi-box-seam me-2"></i>Stok Tersedia
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.stock-requests.create') ? 'active' : '' }}" href="{{ route('staff.stock-requests.create') }}">
                <i class="bi bi-plus-circle-fill me-2"></i>Request Barang Keluar
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.stock-requests.my-requests') ? 'active' : '' }}" href="{{ route('staff.stock-requests.my-requests') }}">
                <i class="bi bi-clock-history me-2"></i>Riwayat Request
            </a>
        </li>

    @endif
</ul>