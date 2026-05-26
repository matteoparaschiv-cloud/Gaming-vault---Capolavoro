<?php 
    require 'db.php';

    // LOGICA ELIMINA (prima di tutto, per evitare di caricare dati inutili)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['elimina'])) {
        $pdo->query("TRUNCATE TABLE giochi");
        header("Location: index_gaming.php"); 
        exit();
    }

    // LOGICA AGGIUNGI
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aggiungi'])) {
        $titolo = trim($_POST['titolo']);
        $genere = (int)$_POST['genere'];
        if ($titolo !== '') {
            $sql = "INSERT INTO giochi (titolo, id_genere) VALUES (:titolo, :genere)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['titolo' => $titolo, 'genere' => $genere]);
        }
        header("Location: index_gaming.php");
        exit();
    }

    // LOGICA CAMBIO STATO
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['stato_id'])) {
        $id    = (int)$_POST['stato_id'];
        $stato = $_POST['stato_valore'];
        $validi = ['Da iniziare', 'In corso', 'Completato'];
        if (in_array($stato, $validi)) {
            $stmt = $pdo->prepare("UPDATE giochi SET stato = :stato WHERE id = :id");
            $stmt->execute(['stato' => $stato, 'id' => $id]);
        }
        header("Location: index_gaming.php");
        exit();
    }

    // RECUPERO DATI
    $query = $pdo->query("SELECT giochi.*, generi.nome AS genere_nome 
                          FROM giochi 
                          LEFT JOIN generi ON giochi.id_genere = generi.id
                          ORDER BY giochi.data_aggiunta DESC");
    $giochi = $query->fetchAll();
    $generi = $pdo->query("SELECT * FROM generi")->fetchAll();

    $totale      = count($giochi);
    $completati  = count(array_filter($giochi, fn($g) => $g['stato'] === 'Completato'));
    $in_corso    = count(array_filter($giochi, fn($g) => $g['stato'] === 'In corso'));
    $da_iniziare = count(array_filter($giochi, fn($g) => $g['stato'] === 'Da iniziare'));
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaming Vault</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0b0d11;
            --surface:   #13161c;
            --surface2:  #1a1e28;
            --border:    rgba(255,255,255,0.07);
            --accent:    #00e5ff;
            --accent2:   #7b5ea7;
            --text:      #e8eaf0;
            --muted:     #6b7280;
            --danger:    #ef4444;
            --success:   #22c55e;
            --warning:   #f59e0b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 0;
        }

        /* ── HEADER AGGIORNATO ── */
        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 20px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between; /* Spinge logo a sx e tasto a dx */
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        header h1 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #fff;
        }
        header h1 span { color: var(--accent); }

        /* NUOVO TASTO CAPOLAVORO */
        .btn-capolavoro {
            background: linear-gradient(135deg, var(--accent), #008c9e);
            color: #0b0d11;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 30px;
            font-family: 'Rajdhani', sans-serif;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0, 229, 255, 0.3);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-capolavoro:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 229, 255, 0.6);
            color: #fff;
        }

        /* ── MAIN LAYOUT ── */
        main { max-width: 1100px; margin: 0 auto; padding: 40px 24px; }

        /* ── STAT CARDS ── */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 36px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .stat-card .number {
            font-family: 'Rajdhani', sans-serif;
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 6px;
        }
        .stat-card .label {
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-card.total   .number { color: var(--accent); }
        .stat-card.done    .number { color: var(--success); }
        .stat-card.playing .number { color: var(--warning); }
        .stat-card.todo    .number { color: var(--muted); }

        /* ── FORM AGGIUNGI ── */
        .add-form {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 28px 32px;
            margin-bottom: 36px;
        }
        .add-form h2 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--accent);
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .form-row {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 12px; color: var(--muted); letter-spacing: 0.5px; }

        .form-row input[type="text"] {
            flex: 1;
            min-width: 200px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 11px 16px;
            color: var(--text);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-row input[type="text"]:focus { border-color: var(--accent); }
        .form-row input[type="text"]::placeholder { color: var(--muted); }

        .form-row select {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 11px 16px;
            color: var(--text);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .form-row select:focus { border-color: var(--accent); }
        .form-row select option { background: var(--surface2); }

        .btn-add {
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 8px;
            padding: 11px 24px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
            white-space: nowrap;
        }
        .btn-add:hover { opacity: 0.88; transform: translateY(-1px); }

        /* ── TABLE AREA ── */
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .table-header h2 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text);
        }

        .btn-danger {
            background: transparent;
            border: 1px solid var(--danger);
            color: var(--danger);
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .btn-danger:hover { background: var(--danger); color: #fff; }

        /* ── TABELLA ── */
        .table-wrapper {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: var(--surface2);
            padding: 14px 20px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--border);
        }
        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--surface2); }
        tbody td { padding: 14px 20px; font-size: 14px; vertical-align: middle; }

        /* COVER INGRANDITE E ANIMATE */
        td.cover-cell { width: 110px; padding: 12px; text-align: center; }
        
        .cover-img {
            width: 90px; 
            height: 126px; /* Proporzione locandina perfetta */
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid transparent;
            display: block;
            margin: 0 auto;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
        }
        
        .cover-img:hover {
            transform: scale(1.15);
            border-color: var(--accent);
            box-shadow: 0 10px 25px rgba(0,229,255,0.4);
            position: relative;
            z-index: 10;
        }
        
        .cover-placeholder {
            width: 90px; 
            height: 126px;
            background: var(--surface2);
            border-radius: 8px;
            border: 1px dashed rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px;
            color: var(--muted);
            margin: 0 auto;
        }

        /* titolo */
        .game-title { font-weight: 600; font-size: 16px; color: var(--text); }

        /* badge genere */
        .badge-genre {
            display: inline-block;
            background: rgba(123,94,167,0.18);
            color: #b39ddb;
            border: 1px solid rgba(123,94,167,0.3);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 12px;
            font-weight: 500;
        }

        /* stato select inline */
        .stato-select {
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            outline: none;
            transition: border-color 0.2s;
        }
        .stato-select:focus { border-color: var(--accent); }
        .stato-select.da-iniziare { color: var(--muted);   background: rgba(107,114,128,0.08); }
        .stato-select.in-corso    { color: var(--warning); background: rgba(245,158,11,0.08); }
        .stato-select.completato  { color: var(--success); background: rgba(34,197,94,0.08); }
        .stato-select option { background: var(--surface2); color: var(--text); }

        /* data */
        .date-cell { color: var(--muted); font-size: 13px; }

        /* empty state */
        .empty-state {
            text-align: center;
            padding: 64px 24px;
            color: var(--muted);
        }
        .empty-state .icon { font-size: 48px; margin-bottom: 16px; }
        .empty-state p { font-size: 15px; }

        /* ── FOOTER ── */
        footer {
            text-align: center;
            padding: 32px;
            font-size: 13px;
            color: var(--muted);
            border-top: 1px solid var(--border);
            margin-top: 60px;
        }

        /* ── CONFIRM MODAL ── */
        .modal-backdrop {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 100;
            align-items: center; justify-content: center;
        }
        .modal-backdrop.open { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 36px;
            max-width: 380px;
            width: 90%;
            text-align: center;
        }
        .modal h3 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 22px; font-weight: 700;
            color: var(--danger);
            margin-bottom: 12px;
        }
        .modal p { color: var(--muted); font-size: 14px; line-height: 1.6; margin-bottom: 28px; }
        .modal-actions { display: flex; gap: 12px; justify-content: center; }
        .btn-cancel {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 24px;
            color: var(--text);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
        }
        .btn-confirm-del {
            background: var(--danger);
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
        }

        @media (max-width: 700px) {
            header { flex-direction: column; gap: 15px; padding: 20px; text-align: center; }
            main   { padding: 24px 16px; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .form-row { flex-direction: column; }
            .form-row input[type="text"], .form-row select { width: 100%; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo-container">
        <div class="logo-icon">🎮</div>
        <h1>GAMING<span>VAULT</span></h1>
    </div>
    
    <a href="capolavoro.html" class="btn-capolavoro">🚀 Esplora il Capolavoro</a>
</header>

<main>

    <div class="stats">
        <div class="stat-card total">
            <div class="number"><?= $totale ?></div>
            <div class="label">Giochi totali</div>
        </div>
        <div class="stat-card done">
            <div class="number"><?= $completati ?></div>
            <div class="label">Completati</div>
        </div>
        <div class="stat-card playing">
            <div class="number"><?= $in_corso ?></div>
            <div class="label">In corso</div>
        </div>
        <div class="stat-card todo">
            <div class="number"><?= $da_iniziare ?></div>
            <div class="label">Da iniziare</div>
        </div>
    </div>

    <div class="add-form">
        <h2>➕ Aggiungi un gioco</h2>
        <form method="POST">
            <div class="form-row">
                <div class="form-group" style="flex:1; min-width:200px;">
                    <label>Titolo del gioco</label>
                    <input type="text" name="titolo" placeholder="Es. Elden Ring, God of War…" required>
                </div>
                <div class="form-group">
                    <label>Genere</label>
                    <select name="genere">
                        <?php foreach($generi as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" name="aggiungi" class="btn-add">Aggiungi</button>
                </div>
            </div>
        </form>
    </div>

    <div class="table-header">
        <h2>📦 La tua collezione</h2>
        <?php if ($totale > 0): ?>
        <button class="btn-danger" onclick="document.getElementById('modal').classList.add('open')">
            🗑 Elimina tutto
        </button>
        <?php endif; ?>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Titolo</th>
                    <th>Genere</th>
                    <th>Stato</th>
                    <th>Aggiunto il</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($totale === 0): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="icon">🕹️</div>
                            <p>La tua collezione è vuota. Aggiungi il primo gioco!</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach($giochi as $gioco): 
                    $stato_class = match($gioco['stato']) {
                        'In corso'    => 'in-corso',
                        'Completato'  => 'completato',
                        default       => 'da-iniziare'
                    };
                ?>
                <tr class="game-row" data-title="<?= htmlspecialchars($gioco['titolo']) ?>">
                    <td class="cover-cell">
                        <div class="cover-placeholder">...</div>
                    </td>
                    <td class="game-title"><?= htmlspecialchars($gioco['titolo']) ?></td>
                    <td><span class="badge-genre"><?= htmlspecialchars($gioco['genere_nome'] ?? '—') ?></span></td>
                    <td>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="stato_id" value="<?= $gioco['id'] ?>">
                            <select name="stato_valore"
                                    class="stato-select <?= $stato_class ?>"
                                    onchange="this.form.submit()">
                                <option <?= $gioco['stato']==='Da iniziare' ? 'selected' : '' ?>>Da iniziare</option>
                                <option <?= $gioco['stato']==='In corso'    ? 'selected' : '' ?>>In corso</option>
                                <option <?= $gioco['stato']==='Completato'  ? 'selected' : '' ?>>Completato</option>
                            </select>
                        </form>
                    </td>
                    <td class="date-cell"><?= date('d/m/Y', strtotime($gioco['data_aggiunta'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<div class="modal-backdrop" id="modal">
    <div class="modal">
        <h3>⚠️ Sicuro?</h3>
        <p>Stai per eliminare <strong style="color:var(--text);"><?= $totale ?> giochi</strong> dalla collezione.<br>L'operazione è irreversibile.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="document.getElementById('modal').classList.remove('open')">Annulla</button>
            <form method="POST" style="margin:0;">
                <button type="submit" name="elimina" class="btn-confirm-del">Sì, elimina tutto</button>
            </form>
        </div>
    </div>
</div>

<footer>
    Gaming Vault &copy; <?= date('Y') ?> | Progetto Esame
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.game-row').forEach(row => {
        const title     = row.getAttribute('data-title');
        const coverCell = row.querySelector('.cover-cell');

        fetch(`https://www.cheapshark.com/api/1.0/games?title=${encodeURIComponent(title)}&limit=1`)
            .then(r => r.json())
            .then(data => {
                if (data && data.length > 0) {
                    const url = data[0].thumb;
                    coverCell.innerHTML = `<img src="${url}" alt="Cover ${title}" class="cover-img">`;
                } else {
                    coverCell.innerHTML = '<div class="cover-placeholder">N/A</div>';
                }
            })
            .catch(() => {
                coverCell.innerHTML = '<div class="cover-placeholder">!</div>';
            });
    });

    // Aggiorna colore select stato al cambio
    document.querySelectorAll('.stato-select').forEach(sel => {
        sel.addEventListener('change', function() {
            this.className = 'stato-select';
            const map = { 'Da iniziare': 'da-iniziare', 'In corso': 'in-corso', 'Completato': 'completato' };
            this.classList.add(map[this.value] || 'da-iniziare');
        });
    });

    // Chiudi modal cliccando fuori
    document.getElementById('modal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});
</script>
</body>
</html>