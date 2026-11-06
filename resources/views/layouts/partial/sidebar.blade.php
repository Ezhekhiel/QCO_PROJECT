<!--begin::Sidebar-->
<aside class="app-sidebar bg-body-secondary shadow" id="display_sidebar" data-bs-theme="dark"
    style="background-color: #343A40;">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
        <!--begin::Brand Link-->
        <a href="./index.html" class="brand-link">
            <!--begin::Brand Image-->
            <img src="{{ asset('assets/img/AdminLTELogo v1.png') }}" alt="AdminLTE Logo"
                class="brand-image opacity-75 shadow" />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">PT. PWI 2</span>
            <!--end::Brand Text-->
        </a>
        <!--end::Brand Link-->
    </div>
    <!--end::Sidebar Brand-->
    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation"
                aria-label="Main navigation" data-accordion="false" id="navigation">
                @auth
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-hdd-network"></i>
                            <p>
                                <i>QCO (Quick Change Over)</i>
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('QCO') }}" class="nav-link">
                                    <i class="nav-icon bi bi-circle"></i>
                                    <p>Dashboard QCO</p>
                                </a>
                            </li>
                            @if (Auth::user()->department && Auth::user()->department->nama != 'PRODUCTION')
                                <li class="nav-item">
                                    <a href="{{ url('QCO/Form') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Form QCO</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                    {{-- Pastikan relasi department ada dan namanya PLANNER -- --}}
                    @if (Auth::user()->department && Auth::user()->department->nama == 'LEAN')
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-person-fill"></i>
                                <p>
                                    <i>User</i>
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('/register') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Register</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('departments') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Department</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('roles') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Roles</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-gear"></i>
                                <p>
                                    <i>Manage</i>
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('/cell-info') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Cell Info</p>
                                    </a>
                                </li>
                            </ul>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('/QCO/List') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>QCO Preparation List</p>
                                    </a>
                                </li>
                            </ul>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('/plan-master') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Upload Plan Master</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endauth

                <!--end::Sidebar Menu-->
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->
