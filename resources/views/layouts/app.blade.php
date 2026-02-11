<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        運行管理システム
        @if(View::hasSection('title'))
            - @yield('title')
        @endif
    </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
    <style>
        .navbar {
            background-color: #212529;
        }
        
        .navbar-collapse {
            justify-content: center;
        }

        .nav-item {
            margin: 0 10px;
        }

        .nav-link {
            font-weight: 500;
            white-space: nowrap;
        }

        .mega-menu {
            min-width: 450px;
            padding: 1.5rem;
            left: 50% !important;
            transform: translateX(-50%) !important;
        }
        
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            --bs-table-bg-type: transparent;
        }
        .table-striped > tbody > tr:nth-of-type(even) > * {
            --bs-table-bg-type: #f1f1f1;
        }
        .table-hover-f1 > tbody > tr:hover > * {
            background-color: #f1f1f1 !important;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-xl navbar-dark shadow">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-xl-none" href="{{ route('home') }}">運行管理</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown text-center">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-truck me-1"></i>運行管理
                        </a>
                        <ul class="dropdown-menu shadow">
                            <li><a class="dropdown-item" href="#">運行台帳</a></li>
                            <li><a class="dropdown-item" href="#">運転手台帳</a></li>
                            <li><a class="dropdown-item" href="#">運行一覧</a></li>
                            <li><a class="dropdown-item" href="#">日次一覧</a></li>
                            <li><a class="dropdown-item" href="#">乗務指示一覧</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">デジタコデータアップロード</a></li>
                            <li><a class="dropdown-item" href="#">デジタコデータアップロード履歴</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown text-center">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-cash-stack me-1"></i>売上管理
                        </a>
                        <ul class="dropdown-menu shadow">
                            <li><a class="dropdown-item" href="#">請求管理</a></li>
                            <li><a class="dropdown-item" href="#">請求詳細</a></li>
                            <li><a class="dropdown-item" href="#">入金管理</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown text-center">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-graph-up-arrow me-1"></i>実績集計
                        </a>
                        <ul class="dropdown-menu shadow">
                            <li><a class="dropdown-item" href="#">輸送実績一覧</a></li>
                            <li><a class="dropdown-item" href="#">乗務実績一覧</a></li>
                            <li><a class="dropdown-item" href="#">売上集計</a></li>
                        </ul>
                    </li>



                    <li class="nav-item dropdown text-center">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-database-gear me-1"></i>マスター管理
                        </a>
                        <ul class="dropdown-menu shadow">
                            <li><a class="dropdown-item py-1" href="#">基本情報</a></li>
                            <li><a class="dropdown-item py-1" href="{{ route('masters.branches.index') }}">営業所</a></li>
                            <li><a class="dropdown-item py-1" href="{{ route('masters.staffs.index') }}">スタッフ</a></li>
                            <li><a class="dropdown-item py-1" href="{{ route('masters.vehicles.index') }}">車両</a></li>
                            <li><a class="dropdown-item py-1" href="{{ route('masters.drivers.index') }}">運転手</a></li>
                            <li><a class="dropdown-item py-1" href="{{ route('masters.guides.index') }}">ガイド</a></li>
                            <li><a class="dropdown-item py-1" href="{{ route('masters.agencies.index') }}">代理店</a></li>
                            <li><a class="dropdown-item py-1" href="{{ route('masters.partners.index') }}">取引先(傭車先)</a></li>
                            <li><a class="dropdown-item py-1" href="{{ route('masters.customers.index') }}">顧客</a></li>
                            <li><a class="dropdown-item py-1" href="{{ route('masters.itineraries.index') }}">行程</a></li>
                            <li><a class="dropdown-item py-1" href="{{ route('masters.purposes.index') }}">目的</a></li>
                            <li><a class="dropdown-item py-1" href="#">施設</a></li>
                            <li><a class="dropdown-item py-1" href="#">地名</a></li>
                            <li><a class="dropdown-item py-1" href="#">料金</a></li>
                            <li><a class="dropdown-item py-1" href="#">予約分類</a></li>
                            <li><a class="dropdown-item py-1" href="#">勤怠分類</a></li>
                            <li><a class="dropdown-item py-1" href="#">備考</a></li>
                            <hr class="dropdown-divider">
                            <li><a class="dropdown-item py-1 small" href="#">ログイン履歴</a></li>
                        </ul>
                    </li>
                </ul>

                <div class="d-xl-flex align-items-center ms-auto">
                    <a href="/" class="btn btn-sm btn-outline-light">ログアウト</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 mb-4 px-4">
        @yield('content')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>