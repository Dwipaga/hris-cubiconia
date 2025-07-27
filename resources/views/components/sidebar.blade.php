@php
    $generalService = app(\App\Services\GeneralService::class);
    $sidemenu = $generalService->getMenu('side-menu');
    $segment1 = request()->segment(1);
    $segment2 = request()->segment(1)."/".request()->segment(2);
@endphp

<style>
    .brand-link {
        padding: 5px;
        height: 57px;
    }
</style>

<aside class="main-sidebar elevation-4 sidebar-dark-white" style="background-color:#0467be;">
    <!-- Brand Logo -->
    <a href="{{ url('/') }}" class="brand-link bg-cubiconia">

        <img src="{{ asset('admin/form/logo/logoFull.png') }}" alt="" class="brand-image img-thumbnail ml-3 mr-3 mt-2 p-1">
        <div class="brand-text mt-1 text-light">
            <big>HRIS</big>
        </div>
    </a>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent text-sm nav-flat" data-widget="treeview" role="menu" data-accordion="false">
                @foreach ($sidemenu as $menu)
                @php
                    
                @endphp
                    @if ($menu['root']->menu_description)
                        <li class="nav-header pt-2 lead text-light">
                            <h6>{{ $menu['root']->menu_description }}</h6>
                            <hr class="mt-2 mb-0">
                        </li>
                    @endif
                    
                    <li class="nav-item {{ !empty($menu['child']) ? 'has-treeview ' : '' }} {{ $segment1 == $menu['root']->menu_url ? 'menu-open' : '' }}">
                        <a href="{{ $menu['root']->menu_url }}" class="nav-link {{ $segment1 == $menu['root']->menu_url ? 'active' : '' }}">
                            <i class="{{ $menu['root']->menu_icon }} nav-icon"></i>
                            <p style="font-size:12px">
                                {{ $menu['root']->menu_name }}
                                @if (count($menu["child"]) != 0)
                                    <i class="right fas fa-angle-left"></i>
                                @endif
                            </p>
                        </a>
                        
                        @if (count($menu["child"]) != 0)
                            <ul class="nav nav-treeview">
                                @foreach ($menu['child'] as $value)
                                    @php
                                        $length = strlen($value->menu_name);
                                        $name_menu = $length > 27 ? substr($value->menu_name, 0, 27).'...' : $value->menu_name;
                                    @endphp
                                    
                                    <li class="nav-item">
                                        <a title="{{ $value->menu_name }}" href="{{ url($value->menu_url) }}" class="nav-link {{ $segment2 == $value->menu_url ? 'active' : '' }}">
                                            <i class="{{ $value->menu_icon }} nav-icon"></i>
                                            <p style="font-size:12px">{{ $name_menu }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>