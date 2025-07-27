@extends('layouts.auth')

@section('title', 'Menu Management')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"></h1>
        <a href="{{ route('menus.create') }}" class="btn btn-primary btn-icon-split">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Add New Menu</span>
        </a>
    </div>

    <!-- DataTables Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Menu List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="menuDataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%">Name</th>
                            <th width="15%">Type</th>
                            <th width="20%">URL</th>
                            <th width="15%">Parent</th>
                            <th width="10%">Order</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($menus as $menu)
                            <tr>
                                <td>{{ $menu->menu_id }}</td>
                                <td>
                                    <i class="{{ $menu->menu_icon }}"></i> {{ $menu->menu_name }}
                                </td>
                                <td>{{ ucfirst(str_replace('-', ' ', $menu->menu_type)) }}</td>
                                <td>{{ $menu->menu_url }}</td>
                                <td>
                                    @if ($menu->parent)
                                        {{ $menu->parent->menu_name }}
                                    @else
                                        <span class="badge badge-info">Root</span>
                                    @endif
                                </td>
                                <td>{{ $menu->menu_order }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('menus.edit', $menu->menu_id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal" 
                                            data-menu-id="{{ $menu->menu_id }}" data-menu-name="{{ $menu->menu_name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @if ($menu->menu_order > 1)
                                            <a href="{{ route('menus.move', ['id' => $menu->menu_id, 'direction' => 'up']) }}" class="btn btn-success btn-sm">
                                                <i class="fas fa-arrow-up"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('menus.move', ['id' => $menu->menu_id, 'direction' => 'down']) }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-arrow-down"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Menu</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Are you sure you want to delete the menu "<span id="menuName"></span>"?</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#menuDataTable').DataTable({
            "order": [[ 5, "asc" ]],
            "pageLength": 25
        });

        // Handle delete modal
        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var menuId = button.data('menu-id');
            var menuName = button.data('menu-name');
            
            var modal = $(this);
            modal.find('#menuName').text(menuName);
            $('#deleteForm').attr('action', '/menus/' + menuId);
        });
    });
</script>
@endpush