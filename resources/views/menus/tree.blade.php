@extends('layouts.app')

@section('title', 'Menu Structure')

@section('styles')
<style>
    .menu-tree .card-header {
        padding: 0.75rem 1.25rem;
    }
    .menu-tree .list-group-item {
        border-left: none;
        border-right: none;
        padding: 0.75rem 1.25rem;
    }
    .menu-tree .list-group-item:first-child {
        border-top: none;
    }
    .child-menu {
        margin-left: 2rem;
        border-left: 1px dashed #ccc;
        padding-left: 1rem;
    }
    .menu-actions {
        display: none;
    }
    .list-group-item:hover .menu-actions {
        display: block;
    }
    .menu-type-label {
        font-weight: bold;
        margin-bottom: 0.5rem;
        padding: 0.25rem 0.5rem;
        background-color: #f8f9fc;
        border-radius: 0.25rem;
        border-left: 3px solid #4e73df;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Menu Structure</h1>
        <div>
            <a href="{{ route('menus.index') }}" class="btn btn-primary btn-icon-split mr-2">
                <span class="icon text-white-50">
                    <i class="fas fa-list"></i>
                </span>
                <span class="text">List View</span>
            </a>
            <a href="{{ route('menus.create') }}" class="btn btn-success btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-plus"></i>
                </span>
                <span class="text">Add New Menu</span>
            </a>
        </div>
    </div>

    <div class="row">
        @foreach($menuTypes as $type => $menus)
            <div class="col-lg-6 mb-4">
                <div class="card shadow menu-tree">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">{{ ucfirst(str_replace('-', ' ', $type)) }}</h6>
                    </div>
                    <div class="card-body">
                        @if(count($menus) > 0)
                            <ul class="list-group">
                                @foreach($menus as $menu)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="menu-item">
                                            <i class="{{ $menu->menu_icon ?? 'fas fa-circle' }} mr-2"></i>
                                            <span>{{ $menu->menu_name }}</span>
                                            
                                            @if($menu->menu_url)
                                                <small class="text-muted ml-2">({{ $menu->menu_url }})</small>
                                            @endif
                                            
                                            @if($menu->children && count($menu->children) > 0)
                                                <div class="child-menu mt-3">
                                                    <ul class="list-group">
                                                        @foreach($menu->children as $child)
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <i class="{{ $child->menu_icon ?? 'fas fa-circle' }} mr-2"></i>
                                                                    <span>{{ $child->menu_name }}</span>
                                                                    
                                                                    @if($child->menu_url)
                                                                        <small class="text-muted ml-2">({{ $child->menu_url }})</small>
                                                                    @endif
                                                                </div>
                                                                
                                                                <div class="menu-actions">
                                                                    <div class="btn-group">
                                                                        <a href="{{ route('menus.edit', $child->menu_id) }}" class="btn btn-sm btn-primary">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                        
                                                                        @if($child->menu_order > 1)
                                                                            <a href="{{ route('menus.move', ['id' => $child->menu_id, 'direction' => 'up']) }}" 
                                                                               class="btn btn-sm btn-success" title="Move Up">
                                                                                <i class="fas fa-arrow-up"></i>
                                                                            </a>
                                                                        @else
                                                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                                                <i class="fas fa-arrow-up"></i>
                                                                            </button>
                                                                        @endif
                                                                        
                                                                        <a href="{{ route('menus.move', ['id' => $child->menu_id, 'direction' => 'down']) }}"
                                                                           class="btn btn-sm btn-success" title="Move Down">
                                                                            <i class="fas fa-arrow-down"></i>
                                                                        </a>
                                                                        
                                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                                data-toggle="modal" data-target="#deleteModal"
                                                                                data-menu-id="{{ $child->menu_id }}"
                                                                                data-menu-name="{{ $child->menu_name }}">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="menu-actions">
                                            <div class="btn-group">
                                                <a href="{{ route('menus.edit', $menu->menu_id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <a href="{{ route('menus.create') }}?parent={{ $menu->menu_id }}" class="btn btn-sm btn-info" title="Add Child">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                                
                                                @if($menu->menu_order > 1)
                                                    <a href="{{ route('menus.move', ['id' => $menu->menu_id, 'direction' => 'up']) }}" 
                                                       class="btn btn-sm btn-success" title="Move Up">
                                                        <i class="fas fa-arrow-up"></i>
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="fas fa-arrow-up"></i>
                                                    </button>
                                                @endif
                                                
                                                <a href="{{ route('menus.move', ['id' => $menu->menu_id, 'direction' => 'down']) }}"
                                                   class="btn btn-sm btn-success" title="Move Down">
                                                    <i class="fas fa-arrow-down"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        data-toggle="modal" data-target="#deleteModal"
                                                        data-menu-id="{{ $menu->menu_id }}"
                                                        data-menu-name="{{ $menu->menu_name }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-exclamation-circle text-warning mb-3" style="font-size: 2rem;"></i>
                                <p class="mb-0">No menus found for this type</p>
                                <a href="{{ route('menus.create') }}?type={{ $type }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus"></i> Add Menu
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
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
            <div class="modal-body">
                <p>Are you sure you want to delete the menu "<span id="menuName"></span>"?</p>
                <p class="text-danger"><strong>Warning:</strong> This will also delete all child menus!</p>
            </div>
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