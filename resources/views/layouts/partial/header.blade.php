<!--begin::Header-->
<nav class="app-header navbar navbar-expand bg-body" id="display_header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Home</a></li>
            <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Contact</a></li>
        </ul>
        <!--end::Start Navbar Links-->
        <!--begin::End Navbar Links-->
        <ul class="navbar-nav ms-auto">

            {{-- Auth Menu --}}
            @guest

                <li class="nav-item">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm rounded-pill px-3 me-2">

                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Login
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">

                        <i class="bi bi-person-plus me-1"></i>
                        Register
                    </a>
                </li>

            @endguest


            @auth

                {{-- User Dropdown --}}
                <li class="nav-item dropdown user-menu">

                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">

                        <img src="{{ asset('/assets/img/' . Auth::user()->name . '.png') }}"
                            onerror="this.onerror=null;this.src='{{ asset('/assets/img/user2-160x160.jpg') }}';"
                            class="user-image rounded-circle shadow me-2" alt="User Image"
                            style="width:32px;height:32px;object-fit:cover;">

                        <span class="d-none d-md-inline">
                            {{ Auth::user()->name }}
                        </span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">

                        {{-- Header --}}
                        <li class="user-header text-bg-primary">

                            <img src="{{ asset('/assets/img/' . Auth::user()->name . '.png') }}"onerror="this.onerror=null;this.src='{{ asset('/assets/img/user2-160x160.jpg') }}';"
                                class="rounded-circle shadow" alt="User Image" />

                            <p>
                                {{ Auth::user()->name }}

                                <small>
                                    {{ Auth::user()->email }}
                                </small>
                            </p>
                        </li>

                        {{-- Footer --}}
                        <li class="user-footer">

                            <a href="{{ route('profile.edit') }}" class="btn btn-default btn-flat">

                                Profile
                            </a>

                            <form action="{{ route('logout') }}" method="POST" class="d-inline float-end">

                                @csrf

                                <button type="submit" class="btn btn-danger btn-flat">

                                    Logout
                                </button>

                            </form>

                        </li>

                    </ul>
                </li>

            @endauth


            {{-- Fullscreen --}}
            <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                </a>
            </li>
            <button id="theme-toggle" class="btn btn-outline-secondary">
                <i id="theme-icon" class="bi bi-sun"></i>
            </button>

        </ul>
        <!--end::End Navbar Links-->
    </div>
    <!--end::Container-->
</nav>
<!--end::Header-->
