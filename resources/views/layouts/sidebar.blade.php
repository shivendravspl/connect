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
                 <a class="nav-link menu-link" href="{{ route('dashboard') }}">
                    <i class="ri-dashboard-2-line"></i>
                    <span data-key="t-dashboards">Dashboards</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarDashboards">
                    <ul class="nav nav-sm flex-column"></ul>
                </div>
            </li>

    @hasanyrole('Admin|Super Admin')          
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->is('distributor', 'distributor/', 'users*', 'roles*', 'zones*', 'regions*', 'territories*', 'categories*', 'crops*', 'varieties*', 'verticals*', 'business-units*', 'org-functions*', 'companies*', 'core_api*','item-groups*','items*','indents*','communication*') ? 'active' : '' }}" href="#sidebarMaster" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMaster">
            <i class="ri-apps-2-line"></i> <span data-key="t-dashboards">Masters</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->is('distributor', 'distributor/', 'users*', 'roles*', 'zones*', 'regions*', 'territories*', 'categories*', 'crops*', 'varieties*', 'verticals*', 'business-units*', 'org-functions*', 'companies*', 'core_api*','item-groups*','items*','indents*','communication*') ? 'show' : '' }}" id="sidebarMaster">
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
                                <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" data-key="t-month-grid">Roles & Permission</a>
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
                <li class="nav-item">
                    <a href="{{ route('item-groups.index') }}" class="nav-link {{ request()->routeIs('item-groups*') ? 'active' : '' }}" data-key="t-analytics">Item Groups</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items*') ? 'active' : '' }}" data-key="t-analytics">Items Master</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('indents.index') }}" class="nav-link {{ request()->routeIs('indents.index') ? 'active' : '' }}" data-key="t-analytics">Indent Management</a>
                </li>               
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('indents.approval.*') ? 'active' : '' }}" href="{{ route('indents.approval.index') }}">
                             Approve Indents
                           {{-- @if($pendingApprovalCount > 0)
                            <span class="badge bg-danger">{{ $pendingApprovalCount }}</span>
                            @endif --}}
                        </a>
                    </li>

                    <li class="nav-item">
                          <a href="{{ route('communication.index') }}" class="nav-link {{ request()->routeIs('communication.index') ? 'active' : '' }}" data-key="t-analytics">Communication Controls</a>
                    </li>
            </ul>
        </div>
    </li>
    @else
    @canany(['list-distributor', 'list-user', 'list-role', 'list-zone', 'list-region', 'list-territory', 'list-category', 'list-crop', 'list-variety', 'list-vertical', 'list-business-unit', 'list-org-function', 'list-company', 'list-core-api'])
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->is('distributor*', 'users*', 'roles*', 'zones*', 'regions*', 'territories*', 'categories*', 'crops*', 'varieties*', 'verticals*', 'business-units*', 'org-functions*', 'companies*', 'core_api*','item-groups*','items*','indents*','communication*') ? 'active' : '' }}" href="#sidebarMaster" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMaster">
            <i class="ri-apps-2-line"></i> <span data-key="t-dashboards">Masters</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->is('distributor*', 'users*', 'roles*', 'zones*', 'regions*', 'territories*', 'categories*', 'crops*', 'varieties*', 'verticals*', 'business-units*', 'org-functions*', 'companies*', 'core_api*','item-groups*','items*','indents*','communication*') ? 'show' : '' }}" id="sidebarMaster">
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

@can('list-distributor')
<li class="nav-item">
    @php
        $isDistributorRoute = request()->routeIs('applications.*') || request()->is('applications*');
        $isReportRoute = request()->routeIs('applications.*-status') || 
                        request()->routeIs('applications.distributor-summary') || 
                        request()->routeIs('applications.lifecycle') || 
                        request()->routeIs('applications.pending') || 
                        request()->routeIs('applications.rejected');
        $isNonReportDistributor = $isDistributorRoute && !$isReportRoute;
    @endphp
    
    <a class="nav-link menu-link {{ $isDistributorRoute ? 'active' : '' }}" 
       href="#sidebarDistributor" 
       data-bs-toggle="collapse" 
       role="button" 
       aria-expanded="{{ $isDistributorRoute ? 'true' : 'false' }}" 
       aria-controls="sidebarDistributor">
        <i class="ri-apps-line"></i> <span data-key="t-dashboards">Distributor</span>
    </a>
    
    <div class="collapse menu-dropdown {{ $isDistributorRoute ? 'show' : '' }}" 
         id="sidebarDistributor">
        <ul class="nav nav-sm flex-column">
            <li class="nav-item">
                <a href="{{ route('applications.index') }}" 
                   class="nav-link {{ request()->routeIs('applications.index') ? 'active' : '' }}" 
                   data-key="t-analytics">Onboarding</a>
            </li>
            
            <!-- Reports Section -->
             @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Mis Admin', 'Mis User', 'Management']))
                <li class="nav-item">
                    <a href="#sidebarReports" 
                    class="nav-link collapsed {{ $isReportRoute ? 'active' : '' }}" 
                    data-bs-toggle="collapse" 
                    role="button" 
                    aria-expanded="{{ $isReportRoute ? 'true' : 'false' }}" 
                    aria-controls="sidebarReports" 
                    data-key="t-calender">
                        Reports
                    </a>
                
                    <div class="menu-dropdown collapse {{ $isReportRoute ? 'show' : '' }}" 
                        id="sidebarReports">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('applications.distributor-summary') }}" 
                                class="nav-link {{ request()->routeIs('applications.distributor-summary') ? 'active' : '' }}" 
                                data-key="t-analytics">
                                    Distributor Summary
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('applications.approval-status') }}" 
                                class="nav-link {{ request()->routeIs('applications.approval-status') ? 'active' : '' }}" 
                                data-key="t-analytics">
                                    Approval Status
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('applications.verification-status') }}" 
                                class="nav-link {{ request()->routeIs('applications.verification-status') ? 'active' : '' }}" 
                                data-key="t-analytics">
                                    Verification Status
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('applications.dispatch-status') }}" 
                                class="nav-link {{ request()->routeIs('applications.dispatch-status') ? 'active' : '' }}" 
                                data-key="t-analytics">
                                    Dispatch / Physical Verification
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('applications.lifecycle') }}" 
                                class="nav-link {{ request()->routeIs('applications.lifecycle') ? 'active' : '' }}" 
                                data-key="t-analytics">
                                    Lifecycle Report
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('applications.pending') }}" 
                                class="nav-link {{ request()->routeIs('applications.pending') ? 'active' : '' }}" 
                                data-key="t-analytics">
                                    Pending Work
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('applications.rejected') }}" 
                                class="nav-link {{ request()->routeIs('applications.rejected') ? 'active' : '' }}" 
                                data-key="t-analytics">
                                    Rejected / Rework
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('applications.reports.tat') }}" 
                                class="nav-link {{ request()->routeIs('applications.reports.tat') ? 'active' : '' }}" 
                                data-key="t-analytics">
                                    TAT
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            <!-- End Reports Section -->
            
            @php
                $pendingCount = \App\Models\Onboarding::where('status', 'documents_pending')
                    ->where('created_by', auth()->user()->emp_id)
                    ->count();
            @endphp
            @if($pendingCount > 0)
            <li class="nav-item">
                <a href="{{ route('applications.pending-documents') }}" 
                   class="nav-link {{ request()->routeIs('applications.pending-documents') ? 'active' : '' }}">
                    <i class="nav-icon ri-file-upload-line"></i>
                    <p>
                        Pending Documents
                        <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                    </p>
                </a>
            </li>
            @endif
        </ul>                   
    </div>      
</li>
@endcan


    {{--@if(Auth::user()->employee && Auth::user()->employee->isMisTeam())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('mis.verification-list') ? 'active' : '' }}" 
                       href="{{ route('mis.verification-list') }}">
                         <i class="ri-apps-line"></i>
                        <span class="nav-text">Onboarding Request Received</span>
                        @php
                            $pendingCount = \App\Models\Onboarding::whereIn('status', ['mis_processing', 'documents_pending','documents_resubmitted','physical_docs_verified'])->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="badge bg-danger ms-auto">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </li>
    @endif --}}

    @hasanyrole('Admin|Super Admin')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->is('vendors*') ? 'active' : '' }}" href="#vendorMaster" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="vendorMaster">
            <i class="ri-user-add-line"></i> <span data-key="t-dashboards">Vendors</span>
        </a>
         <div class="collapse menu-dropdown {{ request()->is('vendors*','temp-edits*',) ? 'show' : '' }}" id="vendorMaster">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('vendors.index') }}" class="nav-link {{ request()->is('vendors*') ? 'active' : '' }}" data-key="t-analytics">Onboarding</a>
                </li>
                 <li class="nav-item">
                    <a href="{{ route('temp-edits') }}"
                        class="nav-link {{ request()->routeIs('temp-edits') ? 'active' : '' }}" data-key="t-analytics">
                       Change Request
                    </a>
                </li>
            </ul>
         </div>
    </li>
    @endhasanyrole
    @hasanyrole('Admin|Super Admin')
   
    @endhasanyrole
    @if(Auth::user()->type === 'vendor')
    @php
    $vendor = Auth::user()->vendor; // This will be null if no vendor record exists
    @endphp

    @if($vendor && $vendor->approval_status === 'approved')
    <li class="nav-item">
        <a href="{{ route('vendors.profile') }}"
            class="nav-link {{ request()->routeIs('vendors.profile') ? 'active' : '' }}">
            <i class="ri-user-line"></i> <span>My Profile</span>
        </a>
    </li>
    @else
    <li class="nav-item">
        <a href="{{ route('vendors.create') }}"
            class="nav-link {{ request()->routeIs('vendors.create') ? 'active' : '' }}">
            <i class="ri-user-add-line"></i> <span>Registration</span>
        </a>
    </li>
    @endif
    @endif   



    </ul>
</div>
</div>

<div class="sidebar-background"></div>
</div>
<!-- Left Sidebar -->