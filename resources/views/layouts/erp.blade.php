<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - SRDigitalPro ERP</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @yield('styles')
    @stack('styles')

    <style>
        :root {
            --primary-color: #2d5ff7;
            --primary-light: #4f7bff;
            --primary-dark: #1d4ed8;
            --primary-ultra-light: #f0f5ff;
            --sidebar-bg: #ffffff;
            --sidebar-shadow: 0 0 40px rgba(0, 0, 0, 0.08);
            --header-shadow: 0 4px 20px rgba(45, 95, 247, 0.15);
            --body-bg: #f8fafc;
            --text-primary: #1a202c;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --hover-bg: #f1f5f9;
            --active-bg: #eff6ff;
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--body-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 14px;
            color: var(--text-primary);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* ==================== HEADER ==================== */
        .header-erp {
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            box-shadow: var(--header-shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .erp-title { 
            font-weight: 700; 
            font-size: 1.35rem; 
            display: flex; 
            align-items: center; 
            gap: .875rem;
            letter-spacing: -0.02em;
        }

        .erp-title i { 
            font-size: 1.75rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        /* ==================== USER BOX ==================== */
        .user-box { display: flex; align-items: center; gap: 1.25rem; }

        .avatar { 
            width: 44px; height: 44px; border-radius: 12px;
            border: 2px solid rgba(255,255,255,0.25);
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .avatar:hover { transform: scale(1.05); border-color: rgba(255,255,255,0.5); }

        .user-info { display: flex; flex-direction: column; gap: 2px; }
        .user-name { font-weight: 600; font-size: 0.95rem; color: white; line-height: 1.2; }
        .user-email { font-size: 0.8rem; color: rgba(255,255,255,.7); line-height: 1.2; }

        .logout-btn { 
            background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25); 
            color: white; padding: .625rem 1.25rem; border-radius: 10px; 
            font-size: .875rem; font-weight: 500; transition: var(--transition);
            cursor: pointer; display: flex; align-items: center; gap: 0.5rem;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,.25);
            border-color: rgba(255,255,255,.4);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* ==================== SIDEBAR ==================== */
        .sidebar-container { 
            position: fixed; left: 0; top: 70px; height: calc(100vh - 70px); width: 280px; 
            background: var(--sidebar-bg); box-shadow: var(--sidebar-shadow); 
            overflow-y: auto; overflow-x: hidden; border-right: 1px solid var(--border-color);
        }

        .sidebar-container::-webkit-scrollbar { width: 6px; }
        .sidebar-container::-webkit-scrollbar-track { background: transparent; }
        .sidebar-container::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
        .sidebar-container::-webkit-scrollbar-thumb:hover { background: var(--text-secondary); }

        .sidebar-header { 
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--primary-ultra-light);
        }

        .sidebar-title { 
            font-size: 1.125rem; font-weight: 700; color: var(--primary-color); 
            display: flex; align-items: center; gap: .625rem; letter-spacing: -0.01em;
        }

        /* ==================== NAVIGATION ==================== */
        .sidebar-nav { padding: 1.25rem 1rem; }
        .nav-item { margin-bottom: .375rem; }

        .nav-link { 
            display: flex; align-items: center; padding: .875rem 1rem; 
            color: var(--text-secondary); text-decoration: none; border-radius: 12px; 
            gap: .875rem; transition: var(--transition); font-weight: 500; font-size: 0.9rem;
            position: relative; overflow: hidden;
        }

        .nav-link i { font-size: 1.125rem; min-width: 20px; transition: var(--transition); }

        .nav-link::before {
            content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 3px;
            background: var(--primary-color); transform: scaleY(0); transition: var(--transition);
        }

        .nav-link:hover { background: var(--hover-bg); color: var(--primary-color); transform: translateX(3px); }
        .nav-link:hover i { transform: scale(1.1); }

        .nav-link.active { 
            background: var(--active-bg); color: var(--primary-color); font-weight: 600;
            box-shadow: 0 2px 8px rgba(45, 95, 247, 0.1);
        }
        .nav-link.active::before { transform: scaleY(1); }

        /* ==================== MAIN CONTENT ==================== */
        .main-content { margin-left: 280px; padding: 2rem 2.5rem; min-height: calc(100vh - 70px); }

        /* ==================== GUEST LOGIN ==================== */
        .btn-light { padding: .625rem 1.25rem; border-radius: 10px; font-weight: 500; transition: var(--transition); }
        .btn-light:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

        /* ==================== ANIMATIONS ==================== */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px);} to { opacity: 1; transform: translateY(0);} }
        .nav-item { animation: fadeIn 0.3s ease-out backwards; }
        .nav-item:nth-child(1){animation-delay:.05s}.nav-item:nth-child(2){animation-delay:.1s}.nav-item:nth-child(3){animation-delay:.15s}
        .nav-item:nth-child(4){animation-delay:.2s}.nav-item:nth-child(5){animation-delay:.25s}.nav-item:nth-child(6){animation-delay:.3s}
        .nav-item:nth-child(7){animation-delay:.35s}.nav-item:nth-child(8){animation-delay:.4s}.nav-item:nth-child(9){animation-delay:.45s}
        .nav-item:nth-child(10){animation-delay:.5s}.nav-item:nth-child(11){animation-delay:.55s}.nav-item:nth-child(12){animation-delay:.6s}
        .nav-item:nth-child(13){animation-delay:.65s}.nav-item:nth-child(14){animation-delay:.7s}.nav-item:nth-child(15){animation-delay:.75s}
        .nav-item:nth-child(16){animation-delay:.8s}.nav-item:nth-child(17){animation-delay:.85s}.nav-item:nth-child(18){animation-delay:.9s}
        .nav-item:nth-child(19){animation-delay:.95s}.nav-item:nth-child(20){animation-delay:1s}

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .sidebar-container { transform: translateX(-100%); transition: var(--transition); }
            .sidebar-container.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.5rem 1rem; }
            .user-info { display: none; }
            .header-erp { padding: 0 1rem; }
            .erp-title { font-size: 1.1rem; }
            .erp-title i { font-size: 1.4rem; }
        }

        @media (max-width: 480px) {
            .header-erp { height: 60px; }
            .sidebar-container { top: 60px; height: calc(100vh - 60px); }
            .main-content { padding: 1rem; }
        }

        /* Fix de flechas de paginación */
        a[rel="prev"], a[rel="next"] { display: none !important; }
        a[rel="prev"]::before, a[rel="prev"]::after, a[rel="next"]::before, a[rel="next"]::after { content: none !important; }
        nav[role="navigation"] a[rel="prev"], nav[role="navigation"] a[rel="next"],
        .pagination a[rel="prev"], .pagination a[rel="next"],
        a.page-link[rel="prev"], a.page-link[rel="next"] {
            display: inline-flex !important; position: static !important; width: auto !important; height: auto !important;
            background: none !important; text-indent: 0 !important; overflow: visible !important; opacity: 1 !important;
        }
    </style>
</head>
<body>
    <!-- ==================== HEADER ==================== -->
    <header class="header-erp">
        <div class="erp-title">
            <i class="bi bi-boxes"></i>
            <span>GRC ELECTRONICS</span>
        </div>

        <div class="user-box">
            @auth
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=88&background=ffffff&color=2d5ff7&rounded=true"
                     class="avatar" alt="Avatar {{ auth()->user()->name }}">
                <div class="user-info">
                    <p class="user-name">{{ auth()->user()->name }}</p>
                    <p class="user-email">{{ auth()->user()->email }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mb-0">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Salir</span>
                    </button>
                </form>
            @endauth

            @guest
                <a href="{{ route('login') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </a>
            @endguest
        </div>
    </header>

    <!-- ==================== SIDEBAR ==================== -->
    <aside class="sidebar-container">
        <div class="sidebar-header">
            <h2 class="sidebar-title">
                <i class="bi bi-grid-3x3-gap"></i> 
                Menú Principal
            </h2>
        </div>
        <nav class="sidebar-nav">
            @can('ver-dashboard')
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> 
                    <span>Dashboard</span>
                </a>
            </div>
            @endcan

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}" href="{{ route('pedidos.index') }}">
                    <i class="bi bi-cart-check"></i> 
                    <span>Pedidos</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('inventario.*') ? 'active' : '' }}" href="{{ route('inventario.index') }}">
                    <i class="bi bi-boxes"></i> 
                    <span>Inventario</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('bodegas.*') ? 'active' : '' }}" href="{{ route('bodegas.index') }}">
                    <i class="bi bi-building"></i> 
                    <span>Bodegas</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">
                    <i class="bi bi-people"></i> 
                    <span>Clientes</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('garantias.*') ? 'active' : '' }}" href="{{ route('garantias.module') }}">
                    <i class="bi bi-shield-check"></i> 
                    <span>Garantías</span>
                </a>
            </div>

            @can('ver-dashboard')
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('tracking.*') ? 'active' : '' }}" href="{{ route('tracking.map') }}">
                    <i class="bi bi-geo-alt-fill"></i> 
                    <span>Tracking GPS</span>
                </a>
            </div>
            @endcan

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}" href="{{ route('calendario.index') }}">
                    <i class="bi bi-calendar3"></i> 
                    <span>Calendario</span>
                </a>
            </div>

            @can('ver-finanzas')
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('finanzas.*') ? 'active' : '' }}" href="{{ route('finanzas.index') }}">
                    <i class="bi bi-graph-up"></i> 
                    <span>Finanzas</span>
                </a>
            </div>
            @endcan

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                    <i class="bi bi-shop"></i> 
                    <span>Punto de Venta</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.sales') }}">
                    <i class="bi bi-bar-chart-line"></i> 
                    <span>Reportes</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('rutas.*') || request()->routeIs('pedidos.ruta') ? 'active' : '' }}" href="{{ route('rutas.index') }}">
                    <i class="bi bi-map"></i> 
                    <span>Rutas y Entregas</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}" href="{{ route('empleados.index') }}">
                    <i class="bi bi-person-badge"></i> 
                    <span>Empleados</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- ==================== MAIN CONTENT ==================== -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- ==================== SCRIPTS ==================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
