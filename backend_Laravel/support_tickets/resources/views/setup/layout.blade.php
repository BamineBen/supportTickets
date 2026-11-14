<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Création de l'administrateur</title>
    <style>
        :root { --bg:#f3f4f6; --card:#fff; --text:#111827; --muted:#6b7280; --border:#d1d5db; --accent:#2563eb; --err:#b91c1c; --ok:#047857; }
        @media (prefers-color-scheme: dark) { :root { --bg:#111827; --card:#1f2937; --text:#f9fafb; --muted:#9ca3af; --border:#374151; --accent:#60a5fa; --err:#f87171; --ok:#34d399; } }
        * { box-sizing: border-box; }
        body { margin:0; background:var(--bg); color:var(--text); font:16px/1.5 system-ui, sans-serif; display:flex; justify-content:center; padding:40px 16px; }
        .card { background:var(--card); width:100%; max-width:440px; padding:28px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,.08); }
        h1 { font-size:1.3rem; margin:0 0 6px; }
        p.sub { color:var(--muted); margin:0 0 22px; font-size:.92rem; }
        label { display:block; font-weight:600; font-size:.9rem; margin:14px 0 6px; }
        input[type=text], input[type=email], input[type=password] { width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; background:transparent; color:var(--text); font-size:1rem; }
        input:focus { outline:2px solid var(--accent); outline-offset:1px; }
        .hint { color:var(--muted); font-size:.8rem; margin-top:4px; }
        .err { color:var(--err); font-size:.85rem; margin-top:4px; }
        .check { display:flex; gap:8px; align-items:flex-start; margin-top:18px; font-size:.9rem; }
        button { margin-top:22px; width:100%; padding:12px; border:0; border-radius:8px; background:var(--accent); color:#fff; font-size:1rem; font-weight:600; cursor:pointer; }
        .ok { color:var(--ok); font-weight:600; }
        ul { padding-left:20px; }
        a { color:var(--accent); }
        hr { border:0; border-top:1px solid var(--border); margin:20px 0; }
    </style>
</head>
<body><main class="card">@yield('content')</main></body>
</html>
