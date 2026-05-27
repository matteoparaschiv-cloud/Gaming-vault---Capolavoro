<?php 
    require 'db.php';

    // LOGICA ELIMINA
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

        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 20px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo-container { display: flex; align-items: center; gap: 16px; }
        .logo-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        header h1 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 28px; font-weight: 700;
            letter-spacing: 2px; color: #fff;
        }
        header h1 span { color: var(--accent); }

        .btn-capolavoro {
            background: linear-gradient(135deg, var(--accent), #008c9e);
            color: #0b0d11;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 30px;
            font-family: 'Rajdhani', sans-serif;
            font-size: 18px; font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0, 229, 255, 0.3);
            transition: all 0.3s;
        }
        .btn-capolavoro:hover { transform: scale(1.05); color: #fff; }

        main { max-width: 1100px; margin: 0 auto; padding: 40px 24px; }

        /* STAT CARDS */
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 36px; }
        .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; text-align: center; }
        .stat-card .number { font-family: 'Rajdhani', sans-serif; font-size: 36px; font-weight: 700; margin-bottom: 6px; }
        .stat-card .label { font-size: 12px; color: var(--muted); text-transform: uppercase; }
        .stat-card.total .number { color: var(--accent); }
        .stat-card.done .number { color: var(--success); }
        .stat-card.playing .number { color: var(--warning); }

        /* FORM */
        .add-form { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 28px 32px; margin-bottom: 36px; }
        .form-row { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 12px; color: var(--muted); }
        .form-row input, .form-row select { background: var(--surface2); border: 1px solid var(--border); border-radius: 8px; padding: 11px; color: #fff; outline: none; }
        .btn-add { background: var(--accent); color: #000; border: none; border-radius: 8px; padding: 11px 24px; font-weight: 600; cursor: pointer; }

        /* TABELLA */
        .table-wrapper { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: var(--surface2); padding: 14px 20px; text-align: left; font-size: 11px; color: var(--muted); text-transform: uppercase; }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.2s; }
        tbody tr:hover { background: rgba(255,255,255,0.02); }
        tbody td { padding: 14px 20px; font-size: 14px; }

        .cover-img { width: 80px; height: 110px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); transition: transform 0.3s; }
        .cover-img:hover { transform: scale(1.1); border-color: var(--accent); }

        /* TITOLO CLICCABILE */
        .game-title { 
            font-weight: 600; font-size: 16px; color: var(--accent); 
            cursor: pointer; transition: color 0.2s;
        }
        .game-title:hover { color: #fff; text-decoration: underline; }

        .badge-genre { background: rgba(123,94,167,0.18); color: #b39ddb; padding: 4px 12px; border-radius: 20px; font-size: 12px; }

        .stato-select { background: transparent; border: 1px solid var(--border); border-radius: 6px; padding: 6px; color: inherit; cursor: pointer; }
        .stato-select.da-iniziare { color: var(--muted); }
        .stato-select.in-corso { color: var(--warning); }
        .stato-select.completato { color: var(--success); }

        /* ── MODALE RAWG ── */
        #gameModal {
            display: none; position: fixed; inset: 0; 
            background: rgba(0,0,0,0.85); backdrop-filter: blur(8px);
            z-index: 2000; align-items: center; justify-content: center;
        }
        .modal-content {
            background: var(--surface); border: 1px solid var(--accent);
            width: 90%; max-width: 700px; border-radius: 20px;
            padding: 30px; position: relative; max-height: 85vh; overflow-y: auto;
        }
        .close-modal { position: absolute; top: 20px; right: 20px; font-size: 24px; cursor: pointer; color: var(--muted); }
        .close-modal:hover { color: var(--danger); }
        .modal-body-layout { display: flex; gap: 24px; margin-top: 20px; }
        .modal-info h2 { font-family: 'Rajdhani', sans-serif; color: var(--accent); margin-bottom: 10px; }
        .modal-meta { font-size: 13px; color: var(--muted); margin-bottom: 15px; }
        .modal-meta span { color: var(--text); margin-right: 15px; }
        .modal-desc { font-size: 14px; line-height: 1.6; color: #ccc; }
        .metascore { background: #66cc33; color: #000; padding: 2px 6px; border-radius: 4px; font-weight: bold; }

        @media (max-width: 600px) { .modal-body-layout { flex-direction: column; } .stats { grid-template-columns: 1fr 1fr; } }
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
        <div class="stat-card total"><div class="number"><?= $totale ?></div><div class="label">Totali</div></div>
        <div class="stat-card done"><div class="number"><?= $completati ?></div><div class="label">Finiti</div></div>
        <div class="stat-card playing"><div class="number"><?= $in_corso ?></div><div class="label">In corso</div></div>
        <div class="stat-card todo"><div class="number"><?= $da_iniziare ?></div><div class="label">Idea</div></div>
    </div>

    <div class="add-form">
        <form method="POST" class="form-row">
            <div class="form-group" style="flex:2">
                <label>Titolo Gioco</label>
                <input type="text" name="titolo" placeholder="Es: The Witcher 3" required>
            </div>
            <div class="form-group" style="flex:1">
                <label>Genere</label>
                <select name="genere">
                    <?php foreach($generi as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="aggiungi" class="btn-add">Aggiungi</button>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Titolo (Clicca per info)</th>
                    <th>Genere</th>
                    <th>Stato</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($giochi as $gioco): 
                    $stato_class = match($gioco['stato']) {
                        'In corso' => 'in-corso',
                        'Completato' => 'completato',
                        default => 'da-iniziare'
                    };
                ?>
                <tr class="game-row" data-title="<?= htmlspecialchars($gioco['titolo']) ?>">
                    <td><div class="cover-placeholder">...</div></td>
                    <td class="game-title" onclick="openRawgDetails('<?= addslashes($gioco['titolo']) ?>')">
                        <?= htmlspecialchars($gioco['titolo']) ?>
                    </td>
                    <td><span class="badge-genre"><?= htmlspecialchars($gioco['genere_nome']) ?></span></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="stato_id" value="<?= $gioco['id'] ?>">
                            <select name="stato_valore" class="stato-select <?= $stato_class ?>" onchange="this.form.submit()">
                                <option <?= $gioco['stato']==='Da iniziare' ? 'selected' : '' ?>>Da iniziare</option>
                                <option <?= $gioco['stato']==='In corso' ? 'selected' : '' ?>>In corso</option>
                                <option <?= $gioco['stato']==='Completato' ? 'selected' : '' ?>>Completato</option>
                            </select>
                        </form>
                    </td>
                    <td style="color:var(--muted)"><?= date('d/m/y', strtotime($gioco['data_aggiunta'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="gameModal" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <span class="close-modal" onclick="document.getElementById('gameModal').style.display='none'">&times;</span>
        <div id="modalLoading">Caricamento dati da RAWG...</div>
        <div id="modalData" style="display:none;">
            <div class="modal-body-layout">
                <img id="modalImg" src="" style="width:200px; border-radius:10px; border:1px solid var(--accent);">
                <div class="modal-info">
                    <h2 id="modalTitle"></h2>
                    <div class="modal-meta">
                        Lancio: <span id="modalRelease"></span> 
                        Voto: <span id="modalRating" class="metascore"></span>
                    </div>
                    <div class="modal-meta">Studio: <span id="modalStudio"></span></div>
                    <div class="modal-desc" id="modalDesc"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const RAWG_KEY = '6cfa40be61e946bea7523c571536cc5a';

// 1. CARICAMENTO COVER IN TABELLA (CheapShark - Veloce)
document.querySelectorAll('.game-row').forEach(row => {
    const title = row.getAttribute('data-title');
    const cell = row.querySelector('td');
    fetch(`https://www.cheapshark.com/api/1.0/games?title=${encodeURIComponent(title)}&limit=1`)
        .then(r => r.json())
        .then(data => {
            if(data && data[0]) {
                cell.innerHTML = `<img src="${data[0].thumb}" class="cover-img">`;
            } else {
                cell.innerHTML = '<div class="cover-placeholder">N/A</div>';
            }
        })
        .catch(() => {
            cell.innerHTML = '<div class="cover-placeholder">!</div>';
        });
});

// 2. APERTURA DETTAGLI (RAWG - Approfondito)
function openRawgDetails(title) {
    const modal = document.getElementById('gameModal');
    const mData = document.getElementById('modalData');
    const mLoad = document.getElementById('modalLoading');
    
    modal.style.display = 'flex';
    mData.style.display = 'none';
    mLoad.style.display = 'block';

    // Cerca il gioco su RAWG
    fetch(`https://api.rawg.io/api/games?key=${RAWG_KEY}&search=${encodeURIComponent(title)}&page_size=1`)
        .then(r => r.json())
        .then(data => {
            if(data.results && data.results.length > 0) {
                const game = data.results[0];
                return fetch(`https://api.rawg.io/api/games/${game.id}?key=${RAWG_KEY}`);
            }
            throw new Error("Gioco non trovato");
        })
        .then(r => r.json())
        // ... dentro la fetch dei dettagli, dove gestisci la descrizione:

        .then(details => {
            document.getElementById('modalTitle').innerText = details.name;
            document.getElementById('modalImg').src = details.background_image;
            
            // Pulizia descrizione: 
            // 1. RAWG invia spesso descrizioni con tag <p> o <br>. 
            // 2. Se è troppo lunga, la tagliamo per non annoiare la commissione.
            let descrizionePura = details.description || "Nessuna descrizione disponibile.";
            
            // Tagliamo a 600 caratteri per evitare il muro di testo
            if (descrizionePura.length > 600) {
                descrizionePura = descrizionePura.substring(0, 600) + "...";
            }

            document.getElementById('modalDesc').innerHTML = descrizionePura;
            
            // Miglioriamo la visibilità dello scroll se il testo è comunque lungo
            document.getElementById('modalDesc').style.maxHeight = "300px";
            document.getElementById('modalDesc').style.overflowY = "auto";
            document.getElementById('modalDesc').style.paddingRight = "10px";

            mLoad.style.display = 'none';
            mData.style.display = 'block';
        })
        .catch(err => {
            console.error(err);
            document.getElementById('modalLoading').innerText = "Dettagli non trovati per questo titolo.";
        });
}

function closeModal(e) { 
    // Chiude la modale solo se clicchi sullo sfondo o sulla X
    document.getElementById('gameModal').style.display = 'none'; 
}
</script>

</body>
</html>