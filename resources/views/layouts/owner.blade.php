<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'CashTrack Secure')</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f5f7fa;
        }

        .navbar-brand {
            letter-spacing: .3px;
        }

        .main-container {
            max-width: 1400px;
        }

        .stat-card {
            border: 0;
            border-radius: 14px;
        }

        .content-card {
            border: 0;
            border-radius: 14px;
        }

        .nav-link.active {
            font-weight: 600;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid px-4">

        <a class="navbar-brand fw-bold"
           href="{{ url('/owner/dashboard') }}">
            CashTrack Secure
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div
            class="collapse navbar-collapse"
            id="mainNavbar"
        >

            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a
                        href="{{ url('/owner/dashboard') }}"
                        class="nav-link
                        {{ request()->is('owner/dashboard') ? 'active' : '' }}"
                    >
                        Dashboard
                    </a>
                </li>

                @if(Route::has('owner.products.index'))
                    <li class="nav-item">
                        <a
                            href="{{ route('owner.products.index') }}"
                            class="nav-link
                            {{ request()->routeIs('owner.products.*') ? 'active' : '' }}"
                        >
                            Products
                        </a>
                    </li>
                @endif

                <li class="nav-item">
                    <a
                        href="{{ route('owner.stock-in.create') }}"
                        class="nav-link
                        {{ request()->routeIs('owner.stock-in.*') ? 'active' : '' }}"
                    >
                        Stock In
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="{{ route('owner.stock-counts.index') }}"
                        class="nav-link
                        {{ request()->routeIs('owner.stock-counts.*') ? 'active' : '' }}"
                    >
                        Stock Count
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="{{ route('owner.alerts.index') }}"
                        class="nav-link
                        {{ request()->routeIs('owner.alerts.*') ? 'active' : '' }}"
                    >
                        Alerts
                    </a>
                </li>

                @if(Route::has('owner.audit.index'))
                    <li class="nav-item">
                        <a
                            href="{{ route('owner.audit.index') }}"
                            class="nav-link
                            {{ request()->routeIs('owner.audit.*') ? 'active' : '' }}"
                        >
                            Audit Trail
                        </a>
                    </li>
                @endif

            </ul>

            <div class="d-flex align-items-center gap-3">

                <span class="text-light">
                    {{ auth()->user()->name ?? 'Owner' }}
                </span>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-outline-light btn-sm"
                    >
                        Logout
                    </button>
                </form>

            </div>

        </div>
    </div>
</nav>

<main class="container-fluid main-container py-4 px-4">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the following:</strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')

</main>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>