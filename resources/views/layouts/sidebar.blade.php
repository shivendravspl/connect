<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box" style="margin-top:10px;margin-bottom:6px;">
        <!-- Dark Logo-->
        <a href="index.html" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/connect-icon.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/ojas_dark.png') }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/home" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/connect-icon.png') }}" alt="" style="height:22px !important;">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/connect-logo.png') }}" alt="" style="height:24px !important;">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>


    <!-- Add this just below the navbar-brand-box -->
    {{--<div style="padding: 10px; background: #f8f9fa;">
    @auth
        <p>User: {{ Auth::user()->name }} ({{ Auth::user()->phone }})</p>
    <p>Roles: {{ Auth::user()->roles->pluck('name')->implode(', ') }}</p>
    <p>Permissions: {{ Auth::user()->getAllPermissions()->pluck('name')->implode(', ') }}</p>
    @endauth
</div>--}}


<div id="scrollbar">
    <div class="container-fluid">
        <div id="two-column-menu"></div>
        <ul class="navbar-nav" id="navbar-nav">
            <!--<li class="menu-title"><span data-key="t-menu">Menu</span></li>-->
            <li class="nav-item">
                <a class="nav-link menu-link" href="#sidebarDashboards" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarDashboards">
                    <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboards</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarDashboards">
                    <ul class="nav nav-sm flex-column"></ul>
                </div>
            </li>

            @role('Super Admin')
                   <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('page-builder.*') ? 'active' : '' }}" href="#sidebarFormBuilder" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('page-builder.*') ? 'true' : 'false' }}" aria-controls="sidebarFormBuilder">
                            <i class="ri-survey-line"></i> <span>Form Builder</span>
                        </a>
                        <div class="collapse menu-dropdown {{ request()->routeIs('page-builder.*') ? 'show' : '' }}" id="sidebarFormBuilder">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('page-builder.index') }}" class="nav-link">All Templates</a>
                                </li>
                                 {{--<li class="nav-item">
                                    <a href="{{ route('forms.index') }}#createForm" class="nav-link">Create New</a>
                                </li>--}}
                            </ul>
                        </div>
                    </li>
            <li class="nav-item">
                <a class="nav-link menu-link {{ request()->is('distributor*', 'users*', 'roles*', 'zones*', 'regions*', 'territories*', 'categories*', 'crops*', 'varieties*', 'verticals*', 'business-units*', 'org-functions*', 'companies*', 'core_api*') ? 'active' : '' }}" href="#sidebarMaster" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMaster">
                    <i class="ri-apps-2-line"></i> <span data-key="t-dashboards">Masters</span>
                </a>
                <div class="collapse menu-dropdown {{ request()->is('distributor*', 'users*', 'roles*', 'zones*', 'regions*', 'territories*', 'categories*', 'crops*', 'varieties*', 'verticals*', 'business-units*', 'org-functions*', 'companies*', 'core_api*') ? 'show' : '' }}" id="sidebarMaster">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a href="{{ route('distributor.index') }}" class="nav-link {{ request()->is('distributor*') ? 'active' : '' }}" data-key="t-analytics">Distributor</a>
                        </li>
                        <li class="nav-item">
                            <a href="#sidebarUserAccess" class="nav-link collapsed {{ request()->is('users*', 'roles*') ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('users*', 'roles*') ? 'true' : 'false' }}" aria-controls="sidebarUserAccess" data-key="t-calender">User Access</a>
                            <div class="menu-dropdown collapse {{ request()->is('users*', 'roles*') ? 'show' : '' }}" id="sidebarUserAccess">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->is('users*') ? 'active' : '' }}" data-key="t-main-calender">User</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('roles.index') }}" class="nav-link {{ request()->is('roles*') ? 'active' : '' }}" data-key="t-month-grid">Roles & Permission</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a href="#sidebarLocationMaster" class="nav-link collapsed {{ request()->is('zones*', 'regions*', 'territories*') ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('zones*', 'regions*', 'territories*') ? 'true' : 'false' }}" aria-controls="sidebarLocationMaster" data-key="t-calender">Location Master</a>
                            <div class="menu-dropdown collapse {{ request()->is('zones*', 'regions*', 'territories*') ? 'show' : '' }}" id="sidebarLocationMaster">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('zones.index') }}" class="nav-link {{ request()->is('zones*') ? 'active' : '' }}" data-key="t-analytics">Zones</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('regions.index') }}" class="nav-link {{ request()->is('regions*') ? 'active' : '' }}" data-key="t-analytics">Regions</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('territories.index') }}" class="nav-link {{ request()->is('territories*') ? 'active' : '' }}" data-key="t-analytics">Territories</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a href="#sidebarProductMaster" class="nav-link collapsed {{ request()->is('categories*', 'crops*', 'varieties*', 'verticals*') ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('categories*', 'crops*', 'varieties*', 'verticals*') ? 'true' : 'false' }}" aria-controls="sidebarProductMaster" data-key="t-calender">Product Master</a>
                            <div class="menu-dropdown collapse {{ request()->is('categories*', 'crops*', 'varieties*', 'verticals*') ? 'show' : '' }}" id="sidebarProductMaster">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->is('categories*') ? 'active' : '' }}" data-key="t-analytics">Category</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('crops.index') }}" class="nav-link {{ request()->is('crops*') ? 'active' : '' }}" data-key="t-analytics">Crops</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('varieties.index') }}" class="nav-link {{ request()->is('varieties*') ? 'active' : '' }}" data-key="t-analytics">Varieties</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('verticals.index') }}" class="nav-link {{ request()->is('verticals*') ? 'active' : '' }}" data-key="t-analytics">Verticals</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('business-units.index') }}" class="nav-link {{ request()->is('business-units*') ? 'active' : '' }}" data-key="t-analytics">Business Unit</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('org-functions.index') }}" class="nav-link {{ request()->is('org-functions*') ? 'active' : '' }}" data-key="t-analytics">Organization Function</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('companies.index') }}" class="nav-link {{ request()->is('companies*') ? 'active' : '' }}" data-key="t-analytics">Companies</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('core_api.index') }}" class="nav-link {{ request()->routeIs('core_api*') ? 'active' : '' }}" data-key="t-analytics">Core API</a>
                        </li>
                    </ul>
                </div>
            </li>
            @else
            @canany(['list-distributor', 'list-user', 'list-role', 'list-zone', 'list-region', 'list-territory', 'list-category', 'list-crop', 'list-variety', 'list-vertical', 'list-business-unit', 'list-org-function', 'list-company', 'list-core-api'])
            <li class="nav-item">
                <a class="nav-link menu-link {{ request()->is('distributor*', 'users*', 'roles*', 'zones*', 'regions*', 'territories*', 'categories*', 'crops*', 'varieties*', 'verticals*', 'business-units*', 'org-functions*', 'companies*', 'core_api*') ? 'active' : '' }}" href="#sidebarMaster" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMaster">
                    <i class="ri-apps-2-line"></i> <span data-key="t-dashboards">Masters</span>
                </a>
                <div class="collapse menu-dropdown {{ request()->is('distributor*', 'users*', 'roles*', 'zones*', 'regions*', 'territories*', 'categories*', 'crops*', 'varieties*', 'verticals*', 'business-units*', 'org-functions*', 'companies*', 'core_api*') ? 'show' : '' }}" id="sidebarMaster">
                    <ul class="nav nav-sm flex-column">
                        @can('list-distributor')
                        <li class="nav-item">
                            <a href="{{ route('distributor.index') }}" class="nav-link {{ request()->is('distributor*') ? 'active' : '' }}" data-key="t-analytics">Distributor</a>
                        </li>
                        @endcan
                        @canany(['list-user', 'list-role'])
                        <li class="nav-item">
                            <a href="#sidebarUserAccess" class="nav-link collapsed {{ request()->is('users*', 'roles*') ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('users*', 'roles*') ? 'true' : 'false' }}" aria-controls="sidebarUserAccess" data-key="t-calender">User Access</a>
                            <div class="menu-dropdown collapse {{ request()->is('users*', 'roles*') ? 'show' : '' }}" id="sidebarUserAccess">
                                <ul class="nav nav-sm flex-column">
                                    @can('list-user')
                                    <li class="nav-item">
                                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->is('users*') ? 'active' : '' }}" data-key="t-main-calender">User</a>
                                    </li>
                                    @endcan
                                    @can('list-role')
                                    <li class="nav-item">
                                        <a href="{{ route('roles.index') }}" class="nav-link {{ request()->is('roles*') ? 'active' : '' }}" data-key="t-month-grid">Roles & Permission</a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcan
                        @canany(['list-zone', 'list-region', 'list-territory'])
                        <li class="nav-item">
                            <a href="#sidebarLocationMaster" class="nav-link collapsed {{ request()->is('zones*', 'regions*', 'territories*') ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('zones*', 'regions*', 'territories*') ? 'true' : 'false' }}" aria-controls="sidebarLocationMaster" data-key="t-calender">Location Master</a>
                            <div class="menu-dropdown collapse {{ request()->is('zones*', 'regions*', 'territories*') ? 'show' : '' }}" id="sidebarLocationMaster">
                                <ul class="nav nav-sm flex-column">
                                    @can('list-zone')
                                    <li class="nav-item">
                                        <a href="{{ route('zones.index') }}" class="nav-link {{ request()->is('zones*') ? 'active' : '' }}" data-key="t-analytics">Zones</a>
                                    </li>
                                    @endcan
                                    @can('list-region')
                                    <li class="nav-item">
                                        <a href="{{ route('regions.index') }}" class="nav-link {{ request()->is('regions*') ? 'active' : '' }}" data-key="t-analytics">Regions</a>
                                    </li>
                                    @endcan
                                    @can('list-territory')
                                    <li class="nav-item">
                                        <a href="{{ route('territories.index') }}" class="nav-link {{ request()->is('territories*') ? 'active' : '' }}" data-key="t-analytics">Territories</a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcan
                        @canany(['list-category', 'list-crop', 'list-variety', 'list-vertical'])
                        <li class="nav-item">
                            <a href="#sidebarProductMaster" class="nav-link collapsed {{ request()->is('categories*', 'crops*', 'varieties*', 'verticals*') ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('categories*', 'crops*', 'varieties*', 'verticals*') ? 'true' : 'false' }}" aria-controls="sidebarProductMaster" data-key="t-calender">Product Master</a>
                            <div class="menu-dropdown collapse {{ request()->is('categories*', 'crops*', 'varieties*', 'verticals*') ? 'show' : '' }}" id="sidebarProductMaster">
                                <ul class="nav nav-sm flex-column">
                                    @can('list-category')
                                    <li class="nav-item">
                                        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->is('categories*') ? 'active' : '' }}" data-key="t-analytics">Category</a>
                                    </li>
                                    @endcan
                                    @can('list-crop')
                                    <li class="nav-item">
                                        <a href="{{ route('crops.index') }}" class="nav-link {{ request()->is('crops*') ? 'active' : '' }}" data-key="t-analytics">Crops</a>
                                    </li>
                                    @endcan
                                    @can('list-variety')
                                    <li class="nav-item">
                                        <a href="{{ route('varieties.index') }}" class="nav-link {{ request()->is('varieties*') ? 'active' : '' }}" data-key="t-analytics">Varieties</a>
                                    </li>
                                    @endcan
                                    @can('list-vertical')
                                    <li class="nav-item">
                                        <a href="{{ route('verticals.index') }}" class="nav-link {{ request()->is('verticals*') ? 'active' : '' }}" data-key="t-analytics">Verticals</a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcan
                        @can('list-business-unit')
                        <li class="nav-item">
                            <a href="{{ route('business-units.index') }}" class="nav-link {{ request()->is('business-units*') ? 'active' : '' }}" data-key="t-analytics">Business Unit</a>
                        </li>
                        @endcan
                        @can('list-org-function')
                        <li class="nav-item">
                            <a href="{{ route('org-functions.index') }}" class="nav-link {{ request()->is('org-functions*') ? 'active' : '' }}" data-key="t-analytics">Organization Function</a>
                        </li>
                        @endcan
                        @can('list-company')
                        <li class="nav-item">
                            <a href="{{ route('companies.index') }}" class="nav-link {{ request()->is('companies*') ? 'active' : '' }}" data-key="t-analytics">Companies</a>
                        </li>
                        @endcan
                        @can('list-core-api')
                        <li class="nav-item">
                            <a href="{{ route('core_api.index') }}" class="nav-link {{ request()->routeIs('core_api*') ? 'active' : '' }}" data-key="t-analytics">Core API</a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany
            @endrole

      
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('applications.*') ? 'active' : '' }}" 
                    href="{{ route('applications.index') }}">
                        <i class="ri-apps-line"></i> <span>Applications</span>
                    </a>
                </li>
       @can('menu-builder')
            <li class="nav-item">
                <a class="nav-link menu-link {{ request()->routeIs('menu-builder.*') ? 'active' : '' }}" 
                href="{{ route('menu-builder.index') }}">
                    <i class="ri-menu-2-line"></i> <span>Menu Builder</span>
                </a>
            </li>
        @endcan
            
            

               <!-- Dynamic Menus from Database -->
                @php
                 $menus = getMenuList();
                @endphp

                @if(isset($menus))
                    @foreach($menus as $menu)
                        @can($menu->permissions)
                            @if(empty($menu->children))
                                <li class="nav-item">
                                    <a class="nav-link menu-link {{ request()->routeIs($menu->menu_url.'.*') ? 'active' : '' }}" 
                                       href="{{ $menu->menu_url == '#' ? 'javascript:void(0);' : route($menu->menu_url.'.index') }}">
                                        <i class="ri-file-list-line"></i> <span>{{ $menu->menu_name }}</span>
                                    </a>
                                </li>
                            @else
                                @php
                                    $childPermissions = collect($menu->children)->pluck('permissions')->toArray();
                                    $activeChild = collect($menu->children)->contains(function ($child) {
                                        return request()->routeIs($child['menu_url'].'.*');
                                    });
                                @endphp
                                @canany($childPermissions)
                                <li class="nav-item">
                                    <a class="nav-link menu-link {{ $activeChild ? 'active' : '' }}" 
                                       href="#sidebar{{ Str::studly($menu->menu_name) }}" data-bs-toggle="collapse" role="button" 
                                       aria-expanded="{{ $activeChild ? 'true' : 'false' }}" 
                                       aria-controls="sidebar{{ Str::studly($menu->menu_name) }}">
                                        <i class="ri-folder-line"></i> <span>{{ $menu->menu_name }}</span>
                                    </a>
                                    <div class="collapse menu-dropdown {{ $activeChild ? 'show' : '' }}" id="sidebar{{ Str::studly($menu->menu_name) }}">
                                        <ul class="nav nav-sm flex-column">
                                            @foreach($menu->children as $child)
                                                @can([$child->permissions])
                                                <li class="nav-item">
                                                    <a href="{{ route($child->menu_url.'.index') }}" 
                                                       class="nav-link {{ request()->routeIs($child['menu_url'].'.*') ? 'active' : '' }}">
                                                        {{ $child->menu_name }}
                                                    </a>
                                                </li>
                                                @endcan
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                                @endcanany
                            @endif
                        @endcan
                    @endforeach
                @endif


          
        </ul>
    </div>
</div>

<div class="sidebar-background"></div>
</div>
<!-- Left Sidebar -->