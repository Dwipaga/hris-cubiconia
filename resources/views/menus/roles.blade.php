@extends('layouts.app')

@section('title', 'Menu Access Management')

@section('styles')
<style>
    .role-card {
        transition: all 0.3s;
    }
    .role-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .menu-access-table th, .menu-access-table td {
        vertical-align: middle !important;
    }
    .child-menu {
        margin-left: 2rem;
        border-left: 2px solid #eaecf4;
        padding-left: 1rem;
    }
    .check-all-container {
        padding: 0.5rem;
        background-color: #f8f9fc;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Menu Access Management</h1>
        <a href="{{ route('menus.index') }}" class="btn btn-primary btn-icon-split">
            <span class="icon text-white-50">
                <i class="fas fa-arrow-left"></i>
            </span>
            <span class="text">Back to Menus</span>
        </a>
    </div>

    <!-- User Role Cards -->
    <div class="row mb-4">
        @foreach($groups as $group)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2 role-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">User Group</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $group->group_name }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <a href="#" class="btn btn-sm btn-primary btn-block" data-toggle="modal" data-target="#roleAccessModal" 
                           data-group-id="{{ $group->group_id }}" data-group-name="{{ $group->group_name }}">
                            Manage Access <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- DataTables Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Menu Access Overview</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Actions:</div>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#bulkAssignModal">Bulk Assign Menus</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('menus.export-roles') }}">Export Access Data</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered menu-access-table" id="accessDataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            @foreach($groups as $group)
                                <th class="text-center">{{ $group->group_name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menusByType as $type => $menus)
                            <tr class="bg-light">
                                <td colspan="{{ count($groups) + 1 }}" class="font-weight-bold">
                                    {{ ucfirst(str_replace('-', ' ', $type)) }} Menus
                                </td>
                            </tr>
                            @foreach($menus as $menu)
                                <tr>
                                    <td>
                                        <i class="{{ $menu->menu_icon ?? 'fas fa-circle' }} mr-2"></i>
                                        {{ $menu->menu_name }}
                                    </td>
                                    @foreach($groups as $group)
                                        <td class="text-center">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input access-toggle" 
                                                    id="access-{{ $menu->menu_id }}-{{ $group->group_id }}"
                                                    data-menu-id="{{ $menu->menu_id }}" 
                                                    data-group-id="{{ $group->group_id }}"
                                                    {{ in_array($group->group_id, $menuAccess[$menu->menu_id] ?? []) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="access-{{ $menu->menu_id }}-{{ $group->group_id }}"></label>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                                @if(isset($childMenus[$menu->menu_id]) && count($childMenus[$menu->menu_id]) > 0)
                                    @foreach($childMenus[$menu->menu_id] as $child)
                                        <tr class="table-light">
                                            <td class="pl-5">
                                                <i class="fas fa-long-arrow-alt-right mr-2 text-muted"></i>
                                                <i class="{{ $child->menu_icon ?? 'fas fa-circle' }} mr-2"></i>
                                                {{ $child->menu_name }}
                                            </td>
                                            @foreach($groups as $group)
                                                <td class="text-center">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input access-toggle" 
                                                            id="access-{{ $child->menu_id }}-{{ $group->group_id }}"
                                                            data-menu-id="{{ $child->menu_id }}" 
                                                            data-group-id="{{ $group->group_id }}"
                                                            {{ in_array($group->group_id, $menuAccess[$child->menu_id] ?? []) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="access-{{ $child->menu_id }}-{{ $group->group_id }}"></label>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Role Access Modal -->
<div class="modal fade" id="roleAccessModal" tabindex="-1" role="dialog" aria-labelledby="roleAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleAccessModalLabel">Manage Access for <span id="groupNameSpan"></span></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="check-all-container mb-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="checkAllMenus">
                        <label class="custom-control-label font-weight-bold" for="checkAllMenus">Select/Deselect All Menus</label>
                    </div>
                </div>
                
                <form id="roleAccessForm">
                    <input type="hidden" id="groupIdInput" name="group_id" value="">
                    
                    @foreach($menusByType as $type => $menus)
                        <h6 class="font-weight-bold border-bottom pb-2 mt-4">{{ ucfirst(str_replace('-', ' ', $type)) }} Menus</h6>
                        
                        @foreach($menus as $menu)
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input menu-checkbox parent-menu"
                                        id="menu-{{ $menu->menu_id }}" name="menu_ids[]" value="{{ $menu->menu_id }}"
                                        data-menu-id="{{ $menu->menu_id }}">
                                    <label class="custom-control-label" for="menu-{{ $menu->menu_id }}">
                                        <i class="{{ $menu->menu_icon ?? 'fas fa-circle' }} mr-2"></i>
                                        {{ $menu->menu_name }}
                                    </label>
                                </div>
                                
                                @if(isset($childMenus[$menu->menu_id]) && count($childMenus[$menu->menu_id]) > 0)
                                    <div class="child-menu mt-2">
                                        @foreach($childMenus[$menu->menu_id] as $child)
                                            <div class="custom-control custom-checkbox mt-2">
                                                <input type="checkbox" class="custom-control-input menu-checkbox child-of-{{ $menu->menu_id }}"
                                                    id="menu-{{ $child->menu_id }}" name="menu_ids[]" value="{{ $child->menu_id }}"
                                                    data-parent-id="{{ $menu->menu_id }}">
                                                <label class="custom-control-label" for="menu-{{ $child->menu_id }}">
                                                    <i class="{{ $child->menu_icon ?? 'fas fa-circle' }} mr-2"></i>
                                                    {{ $child->menu_name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <button id="saveRoleAccess" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1" role="dialog" aria-labelledby="bulkAssignModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkAssignModalLabel">Bulk Assign Menu Access</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="bulkAssignForm">
                    <div class="form-group">
                        <label for="sourceGroupSelect">Copy Access Settings From:</label>
                        <select class="form-control" id="sourceGroupSelect" name="source_group_id" required>
                            <option value="">Select User Group</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->group_id }}">{{ $group->group_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Apply To:</label>
                        <div class="card">
                            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                @foreach($groups as $group)
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input target-group-checkbox"
                                            id="target-group-{{ $group->group_id }}" name="target_group_ids[]" value="{{ $group->group_id }}">
                                        <label class="custom-control-label" for="target-group-{{ $group->group_id }}">
                                            {{ $group->group_name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="overwriteExistingCheckbox" name="overwrite_existing" value="1">
                            <label class="custom-control-label" for="overwriteExistingCheckbox">
                                Overwrite existing access settings
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <button id="saveBulkAssign" class="btn btn-primary">Apply Changes</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#accessDataTable').DataTable({
            "paging": false,
            "ordering": false,
            "info": false,
            "autoWidth": false
        });
        
        // Role Access Modal
        $('#roleAccessModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var groupId = button.data('group-id');
            var groupName = button.data('group-name');
            
            var modal = $(this);
            modal.find('#groupNameSpan').text(groupName);
            modal.find('#groupIdInput').val(groupId);
            
            // Reset form checkboxes
            $('.menu-checkbox').prop('checked', false);
            
            // Load current access settings
            $.ajax({
                url: '/menus/get-group-access/' + groupId,
                type: 'GET',
                success: function(data) {
                    $.each(data, function(index, menuId) {
                        $('#menu-' + menuId).prop('checked', true);
                    });
                },
                error: function(xhr) {
                    console.error('Error loading group access data');
                }
            });
        });
        
        // Check/uncheck child menus when parent is checked/unchecked
        $(document).on('change', '.parent-menu', function() {
            var menuId = $(this).data('menu-id');
            var isChecked = $(this).prop('checked');
            
            $('.child-of-' + menuId).prop('checked', isChecked);
        });
        
        // Check all menus
        $('#checkAllMenus').on('change', function() {
            var isChecked = $(this).prop('checked');
            $('.menu-checkbox').prop('checked', isChecked);
        });
        
        // Save role access
        $('#saveRoleAccess').on('click', function() {
            var formData = $('#roleAccessForm').serialize();
            
            $.ajax({
                url: '/menus/save-group-access',
                type: 'POST',
                data: formData + '&_token={{ csrf_token() }}',
                success: function(response) {
                    $('#roleAccessModal').modal('hide');
                    
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'Menu access has been updated.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    console.error('Error saving group access data');
                    
                    // Show error message
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to update menu access.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
        
        // Toggle access directly from the table
        $('.access-toggle').on('change', function() {
            var menuId = $(this).data('menu-id');
            var groupId = $(this).data('group-id');
            var isGranted = $(this).prop('checked');
            
            $.ajax({
                url: '/menus/toggle-access',
                type: 'POST',
                data: {
                    menu_id: menuId,
                    group_id: groupId,
                    is_granted: isGranted ? 1 : 0,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Optional: show a small toast notification
                    toastr.success('Access updated');
                },
                error: function(xhr) {
                    console.error('Error updating access');
                    toastr.error('Failed to update access');
                    
                    // Revert the checkbox state
                    $(this).prop('checked', !isGranted);
                }
            });
        });
        
        // Bulk assign
        $('#saveBulkAssign').on('click', function() {
            var formData = $('#bulkAssignForm').serialize();
            
            // Validate form
            var sourceGroupId = $('#sourceGroupSelect').val();
            var targetGroups = $('input[name="target_group_ids[]"]:checked').length;
            
            if (!sourceGroupId) {
                alert('Please select a source user group.');
                return;
            }
            
            if (targetGroups === 0) {
                alert('Please select at least one target user group.');
                return;
            }
            
            $.ajax({
                url: '/menus/bulk-assign-access',
                type: 'POST',
                data: formData + '&_token={{ csrf_token() }}',
                success: function(response) {
                    $('#bulkAssignModal').modal('hide');
                    
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'Menu access has been bulk assigned.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    console.error('Error bulk assigning access');
                    
                    // Show error message
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to bulk assign menu access.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    });
</script>
@endpush