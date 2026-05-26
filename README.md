# 🎮 Gaming Vault: La mia Libreria Videoludica

Benvenuti nel progetto **Gaming Vault**! 
Questa è un'applicazione web che ho creato come **progetto "Capolavoro"** per il mio esame di maturità. Serve a gestire una collezione personale di videogiochi in modo semplice e moderno.

---

## 📖 Cos'è Gaming Vault?
L'idea nasce dalla necessità di avere un posto dove salvare i propri giochi preferiti. Invece di una semplice lista, ho creato un sistema che:
1. Salva i titoli in un **Database**.
2. Organizza i giochi per **Genere** (Azione, RPG, ecc.).
3. **Cerca automaticamente la copertina** su internet grazie a un collegamento esterno (API), così non devi caricarla tu manualmente!

---

## 🛠️ Come è stato costruito? (Il "motore")

Per realizzare questo sito ho usato diverse tecnologie che abbiamo studiato:

* **HTML & CSS:** Per la struttura e il design. Ho scelto uno stile "Dark Mode" (scuro) con colori accesi come l'azzurro neon per richiamare il mondo dei videogiochi.
* **PHP:** È il "cervello" del sito. Si occupa di ricevere i dati che scrivi nel modulo e di salvarli correttamente.
* **MySQL:** È il grande archivio (Database) dove vengono conservate tutte le informazioni dei giochi in modo sicuro.
* **JavaScript:** Si occupa della magia! Quando aggiungi un gioco, JavaScript "chiama" un servizio esterno (CheapShark API) per trovare l'immagine corretta del titolo.

---

## 📂 Cosa trovi in questo archivio?

* `index_gaming.php`: La pagina principale dove vedi la tua collezione e aggiungi nuovi giochi.
* `db.php`: Il file che permette al sito di parlare con il Database.
* `relazione.html`: Una guida tecnica dettagliata che spiega ogni riga di codice (utile per i professori!).
* `img/`: La cartella dove tengo le icone o i segnaposto.

---

## 🚀 Come provarlo sul tuo PC
Se vuoi far girare questo progetto sul tuo computer, ecco i passaggi:

1. Scarica e installa **XAMPP**.
2. Copia questa cartella dentro `C:/xampp/htdocs/`.
3. Apri **phpMyAdmin** e crea un database chiamato `gaming_vault`.
4. Crea le tabelle (puoi trovare i comandi SQL dentro la `relazione.html`).
5. Apri il browser e scrivi: `localhost/Gaming-vault---Capolavoro`.

---

### 👤 Autore
**Matteo Paraschiv** *Studente di Informatica - Progetto Capolavoro 2026*