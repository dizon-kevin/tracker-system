<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Tracker System' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--sw:248px;--th:64px;--dark:#060a11;--light:#f2f5f3;--card:#fff;--border:rgba(12,26,20,.08);--tp:#0c1a14;--ts:rgba(12,26,20,.58);--tm:rgba(12,26,20,.36);--teal:#00d4aa;--blue:#2f80ed}
        html,body{min-height:100%;font-family:'Sora',sans-serif;background:var(--light);color:var(--tp)}
        a{color:inherit}
        .shell{display:flex;min-height:100vh}
        .sidebar{width:var(--sw);background:var(--dark);color:rgba(220,240,232,.7);position:fixed;inset:0 auto 0 0;padding:1.3rem .9rem 1rem;overflow-y:auto}
        .sidebar::before{content:'';position:absolute;inset:0;background-image:radial-gradient(rgba(0,212,170,.12) 1px,transparent 1px);background-size:28px 28px;pointer-events:none}
        .sidebar>*{position:relative;z-index:1}
        .brand{display:flex;align-items:center;gap:.8rem;padding:.2rem .5rem 1.2rem;border-bottom:1px solid rgba(0,212,170,.08);margin-bottom:1rem}
        .brand-mark{width:38px;height:38px;border-radius:10px;display:grid;place-items:center;color:#06110f;background:linear-gradient(135deg,var(--teal),var(--blue));box-shadow:0 0 24px rgba(0,212,170,.25)}
        .brand-wordmark{font-family:'Space Mono',monospace;font-size:1.05rem;letter-spacing:.12em;color:#e8f5f0}
        .brand-wordmark em{color:var(--teal);font-style:normal}
        .brand-sub{font-size:.7rem;color:rgba(232,245,240,.48);margin-top:.12rem}
        .sidebar-section{font-size:.66rem;text-transform:uppercase;letter-spacing:.12em;color:rgba(0,212,170,.36);padding:.9rem .55rem .45rem;font-weight:700}
        .nav-link{display:flex;align-items:center;gap:.75rem;text-decoration:none;padding:.75rem .8rem;border-radius:10px;font-size:.86rem;margin-bottom:.2rem;transition:background .18s,color .18s,transform .18s}
        .nav-link:hover,.nav-link.active{background:rgba(0,212,170,.11);color:var(--teal);transform:translateX(2px)}
        .sidebar-footer{margin-top:1.2rem;padding:.9rem .6rem 0;border-top:1px solid rgba(0,212,170,.08);font-size:.68rem;color:rgba(0,212,170,.28);font-family:'Space Mono',monospace}
        .main{margin-left:var(--sw);width:calc(100% - var(--sw));min-height:100vh;display:flex;flex-direction:column}
        .topbar{height:var(--th);background:rgba(255,255,255,.88);backdrop-filter:blur(14px);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:0 1.6rem}
        .topbar-title{font-size:1rem;font-weight:700}
        .topbar-meta{font-size:.76rem;color:var(--ts);display:flex;align-items:center;gap:.6rem}
        .meta-chip{display:inline-flex;align-items:center;gap:.35rem;border:1px solid var(--border);border-radius:999px;padding:.34rem .7rem;background:rgba(255,255,255,.75)}
        .meta-dot{width:7px;height:7px;border-radius:50%;background:var(--teal);box-shadow:0 0 10px rgba(0,212,170,.4)}
        .content{padding:1.8rem 2rem 2.4rem}
        .page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem}
        .eyebrow{display:inline-flex;align-items:center;gap:.45rem;font-size:.72rem;text-transform:uppercase;letter-spacing:.12em;font-weight:700;color:#0d8f73;margin-bottom:.5rem}
        .page-title{font-size:1.45rem;font-weight:700;letter-spacing:-.02em}
        .page-subtitle{margin-top:.3rem;font-size:.84rem;color:var(--ts)}
        .hero-card{background:linear-gradient(135deg,#091111,#0e1717 46%,#0a2430);border-radius:18px;padding:1.35rem 1.4rem;color:#e7f8f2;min-width:280px}
        .hero-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.11em;color:rgba(231,248,242,.62)}
        .hero-value{margin-top:.6rem;font-family:'Space Mono',monospace;font-size:1.7rem}
        .hero-note{margin-top:.35rem;font-size:.8rem;color:rgba(231,248,242,.72)}
        .stats-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.9rem;margin-bottom:1.4rem}
        .stat-card,.panel{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden}
        .stat-card{padding:1rem 1.05rem}
        .stat-label{font-size:.73rem;color:var(--ts);margin-bottom:.38rem}
        .stat-value{font-family:'Space Mono',monospace;font-size:1.45rem}
        .stat-accent{height:4px;border-radius:999px;margin-top:.85rem}
        .accent-total{background:linear-gradient(90deg,#00d4aa,#2f80ed)}.accent-pending{background:#f59e0b}.accent-approved{background:#2f80ed}.accent-completed{background:#16a34a}.accent-cancelled{background:#dc2626}
        .panel-head,.filters{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.2rem}
        .panel-head{border-bottom:1px solid var(--border)}
        .panel-title{font-size:.92rem;font-weight:700}
        .filters{flex-wrap:wrap;border-bottom:1px solid var(--border);background:#fbfcfb}
        .pill-group{display:flex;gap:.45rem;flex-wrap:wrap}
        .pill{display:inline-flex;align-items:center;gap:.35rem;padding:.42rem .8rem;border-radius:999px;font-size:.75rem;text-decoration:none;border:1px solid var(--border);color:var(--ts);background:#fff}
        .pill.active{border-color:transparent;background:#0d1817;color:#dff7ef}
        .search-form{margin-left:auto;display:flex;gap:.55rem;flex-wrap:wrap}
        .search-input{min-width:220px;border:1px solid var(--border);background:#fff;border-radius:10px;padding:.62rem .82rem;font:inherit;color:var(--tp)}
        .btn{border:none;border-radius:10px;padding:.62rem .9rem;font:inherit;font-size:.82rem;font-weight:600;cursor:pointer;text-decoration:none}
        .btn-primary{background:#0c1a14;color:#dff7ef}.btn-secondary{background:#eef3f0;color:var(--tp)}
        .table-wrap{overflow-x:auto}
        .table{width:100%;border-collapse:collapse;font-size:.83rem}
        .table th{padding:.75rem 1.15rem;font-size:.66rem;text-transform:uppercase;letter-spacing:.09em;color:var(--tm);background:#fafbfa;border-bottom:1px solid var(--border);text-align:left}
        .table td{padding:1rem 1.15rem;border-bottom:1px solid rgba(12,26,20,.06);color:var(--ts);vertical-align:middle}
        .table tr:hover td{background:#fcfefd}
        .mono{font-family:'Space Mono',monospace;color:var(--tp)} .primary{color:var(--tp);font-weight:600} .muted{color:var(--tm)}
        .badge{display:inline-flex;align-items:center;gap:.35rem;border-radius:999px;padding:.34rem .72rem;font-size:.7rem;font-weight:700}
        .badge::before{content:'';width:6px;height:6px;border-radius:50%}
        .badge-pending{background:rgba(245,158,11,.11);color:#b45309}.badge-pending::before{background:#f59e0b}
        .badge-approved{background:rgba(47,128,237,.11);color:#2563eb}.badge-approved::before{background:#2f80ed}
        .badge-completed{background:rgba(22,163,74,.11);color:#15803d}.badge-completed::before{background:#16a34a}
        .badge-cancelled{background:rgba(220,38,38,.1);color:#b91c1c}.badge-cancelled::before{background:#dc2626}
        .badge-processing{background:rgba(124,58,237,.1);color:#6d28d9}.badge-processing::before{background:#7c3aed}
        .action-link{display:inline-flex;align-items:center;gap:.35rem;color:#2563eb;border:1px solid rgba(47,128,237,.18);border-radius:8px;padding:.4rem .72rem;font-size:.74rem;font-weight:600;text-decoration:none}
        .empty{padding:3rem 1.4rem;text-align:center;color:var(--ts)}
        .grid-two{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(320px,.75fr);gap:1rem}
        .detail-stack,.item-list,.timeline{display:grid;gap:1rem}
        .detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.9rem}
        .detail-item{border:1px solid var(--border);border-radius:14px;padding:1rem;background:#fbfcfb}
        .detail-item-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;color:var(--tm);margin-bottom:.38rem}
        .detail-item-value{font-size:.92rem;color:var(--tp);font-weight:600}
        .timeline-item{display:grid;grid-template-columns:18px 1fr;gap:.8rem;align-items:flex-start}
        .timeline-dot{width:14px;height:14px;border-radius:50%;margin-top:.18rem;background:#d6e2db}
        .timeline-dot.done{background:#16a34a;box-shadow:0 0 0 4px rgba(22,163,74,.1)}
        .timeline-dot.danger{background:#dc2626;box-shadow:0 0 0 4px rgba(220,38,38,.09)}
        .timeline-label{font-size:.84rem;font-weight:700;color:var(--tp)}
        .timeline-time{font-size:.76rem;color:var(--ts);margin-top:.15rem}
        .item-card{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;border:1px solid var(--border);border-radius:14px;padding:.9rem 1rem;background:#fcfefd}
        .item-name{font-weight:700;color:var(--tp)}
        .item-meta,.footer-note{margin-top:.2rem;font-size:.76rem;color:var(--ts)}
        .pagination{padding:.95rem 1.2rem}
        @media (max-width:1080px){.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.grid-two{grid-template-columns:1fr}}
        @media (max-width:760px){.sidebar{position:static;width:100%}.main{margin-left:0;width:100%}.shell{display:block}.content{padding:1.2rem 1rem 1.8rem}.topbar{padding:0 1rem}.stats-grid,.detail-grid{grid-template-columns:1fr}.search-form{margin-left:0;width:100%}.search-input{min-width:0;width:100%}}
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 2h8v6h-8z"/></svg>
                </div>
                <div>
                    <div class="brand-wordmark">TRA<em>CKER</em></div>
                    <div class="brand-sub">Connected to Storix</div>
                </div>
            </div>
            <div class="sidebar-section">Monitor</div>
            <a href="{{ route('tracked-orders.index') }}" class="nav-link {{ request()->routeIs('tracked-orders.index') ? 'active' : '' }}">Tracking Dashboard</a>
            <a href="{{ route('tracked-orders.index', ['status' => 'pending']) }}" class="nav-link {{ request('status') === 'pending' ? 'active' : '' }}">Pending Queue</a>
            <a href="{{ route('tracked-orders.index', ['status' => 'completed']) }}" class="nav-link {{ request('status') === 'completed' ? 'active' : '' }}">Completed Orders</a>
            <div class="sidebar-section">Integration</div>
            <a href="{{ route('tracked-orders.index') }}" class="nav-link">API Sync Stream</a>
            <div class="sidebar-footer">TRACKER v1.0 · LIVE SYNC</div>
        </aside>
        <div class="main">
            <header class="topbar">
                <div class="topbar-title">{{ $topbarTitle ?? 'Tracker Dashboard' }}</div>
                <div class="topbar-meta">
                    <span class="meta-chip"><span class="meta-dot"></span> Connected to Storix</span>
                    <span class="meta-chip">{{ now()->format('M d, Y H:i') }}</span>
                </div>
            </header>
            <main class="content">{{ $slot }}</main>
        </div>
    </div>
</body>
</html>
