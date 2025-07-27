@extends('layouts.auth')

@section('title', 'Employee Management')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"></h1>

        <a href="{{ route('user.create') }}" class="btn btn-primary btn-icon-split">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Add New Employee</span>
        </a>
    </div>
    <!-- DataTables Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Employee List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="menuDataTable" style="width: 100%;" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="10%">ID Karyawan</th>
                            <th width="15%">Username</th>
                            <th width="15%">First Name</th>
                            <th width="15%">Last Name</th>
                            <th width="20%">NIK</th>
                            <th width="15%">Tgl Lahir</th>
                            <th width="5%">Usia</th>
                            <th width="15%">Tgl Masuk</th>
                            <th width="15%">Tgl Terakhir Kontrak</th>
                            <th style="width:20%;">Sisa Kontrak</th>
                            <th width="20%">NPWP</th>
                            <th width="15%">Jenis Kontrak</th>
                            <th width="15%">Dok. Kontrak</th>
                            <th width="15%">Email</th>
                            <th width="10%">Group</th>
                            <th width="10%">Phone</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1}}</td>
                            <td>{{ $user->id_karyawan }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->firstname }}</td>
                            <td>{{ $user->lastname }}</td>
                            <td>{{ $user->nik }}</td>
                            <td>{{ $user->tanggal_lahir != null ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->age . " Tahun" : ""}}</td>
                            <td>{{ $user->tanggal_masuk ? \Carbon\Carbon::parse($user->tanggal_masuk)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $user->tanggal_akhir_kontrak ? \Carbon\Carbon::parse($user->tanggal_akhir_kontrak)->format('d-m-Y') : '-' }}</td>
                            <td>
                                @if ($user->tanggal_akhir_kontrak)
                                @php
                                $tanggalAkhir = \Carbon\Carbon::parse($user->tanggal_akhir_kontrak);
                                $now = \Carbon\Carbon::now();
                                $diff = $now->diff($tanggalAkhir);
                                $selisihText = [];

                                if ($diff->y > 0) {
                                $selisihText[] = $diff->y . ' tahun';
                                }
                                if ($diff->m > 0) {
                                $selisihText[] = $diff->m . ' bulan';
                                }
                                if ($diff->d > 0) {
                                $selisihText[] = $diff->d . ' hari';
                                }

                                $output = count($selisihText) > 0 ? implode(' ', $selisihText) : '0 hari';

                                if ($tanggalAkhir < $now) {
                                    $class='bg-danger text-white' ;
                                    $label='KONTRAK HABIS' ;
                                    } elseif ($diff->y > 0) {
                                    $class = 'bg-success text-white';
                                    $label = 'Sisa ' . $output;
                                    } else {
                                    $class = 'bg-warning text-dark';
                                    $label = 'Sisa ' . $output;
                                    }
                                    @endphp

                                    <span class="badge {{ $class }} rounded-pill text-sm px-3 py-1" style="font-size: 0.75rem;">
                                        {{ $label }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                            </td>


                            <td>{{ $user->npwp ?? "" }}</td>
                            <td>{{ $user->jenis_kontrak }}</td>
                            <td>
                                @if($user->dokumen_kontrak)
                                <a href="{{ asset('storage/' . $user->dokumen_kontrak) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-file"></i>
                                </a>
                                @else
                                <span class="text-muted">No Document</span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->group_name }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>
                                <a href="{{ route('user.edit', $user->user_id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('user.destroy', $user->user_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <!-- Custom Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="pagination-info">
                        <span class="text-muted">
                            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} 
                            of {{ $users->total() }} results
                        </span>
                    </div>
                    <div class="pagination-wrapper">
                        <div class="custom-pagination">
                            @if ($users->onFirstPage())
                                <button class="page-btn disabled">‹</button>
                            @else
                                <a href="{{ $users->previousPageUrl() }}" class="page-btn">‹</a>
                            @endif
                            
                            <span class="page-info">{{ $users->currentPage() }}/{{ $users->lastPage() }}</span>
                            
                            @if ($users->hasMorePages())
                                <a href="{{ $users->nextPageUrl() }}" class="page-btn">›</a>
                            @else
                                <button class="page-btn disabled">›</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#menuDataTable').DataTable({
        "order": [
            [0, "asc"]
        ],
        "pageLength": 25,
        "autoWidth": false,
        "columnDefs": [{
                width: "50px",
                targets: 10
            }, // Kolom pertama (misal: No)
        ],
        // Disable DataTables pagination since we're using Laravel pagination
        "paging": false,
        "info": false
    });
</script>
@endpush

@push('styles')
<style>
    /* Table styles */
    .table-responsive {
        overflow-x: auto;
    }

    .avatar-placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background-color: #e9ecef;
        border-radius: 50%;
        font-weight: bold;
    }

    /* Card styling */
    .card {
        border: none;
        border-radius: 8px;
    }

    /* Action buttons */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    /* Custom Compact Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
    }

    .custom-pagination {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        padding: 0;
        margin: 0;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 3px;
        color: #6c757d;
        text-decoration: none;
        font-size: 0.7rem;
        line-height: 1;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .page-btn:hover:not(.disabled) {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
        text-decoration: none;
    }

    .page-btn.disabled {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #adb5bd;
        cursor: not-allowed;
    }

    .page-info {
        display: inline-flex;
        align-items: center;
        height: 24px;
        padding: 0 0.5rem;
        margin: 0 0.25rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 3px;
        font-size: 0.7rem;
        color: #495057;
        white-space: nowrap;
    }

    .pagination-info {
        font-size: 0.75rem;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .pagination-info {
            text-align: center;
            font-size: 0.7rem;
        }
        
        .page-btn {
            width: 22px;
            height: 22px;
            font-size: 0.65rem;
        }

        .page-info {
            height: 22px;
            font-size: 0.65rem;
            padding: 0 0.4rem;
        }
    }

    @media (max-width: 480px) {
        .page-btn {
            width: 20px;
            height: 20px;
            font-size: 0.6rem;
        }

        .page-info {
            height: 20px;
            font-size: 0.6rem;
            padding: 0 0.3rem;
        }

        .pagination-info {
            font-size: 0.65rem;
        }
    }
</style>
@endpush