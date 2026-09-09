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

        .main-container {
            max-width: 1400px;
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

        <a
            class="navbar-brand fw-bold"
            href="{{ route('cashier.dashboard') }}"
        >
            CashTrack Secure
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#cashierNavbar"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div
            class="collapse navbar-collapse"
            id="cashierNavbar"
        >

            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a
                        href="{{ route('cashier.dashboard') }}"
                        class="nav-link {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}"
                    >
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="{{ route('cashier.sales.create') }}"
                        class="nav-link {{ request()->routeIs('cashier.sales.*') ? 'active' : '' }}"
                    >
                        New Sale
                    </a>
                </li>

            </ul>

            <div class="d-flex align-items-center gap-3">

                <span class="text-light">
                    {{ auth()->user()->name ?? 'Cashier' }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
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
            <ul class="mb-0">
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