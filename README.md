# telegram-lootbot-contrabbandiere

## Requisiti
Le query sul db usano mysqli per questo l'SQL è valido e testato solo su un dbms MySQL.

## Setup
Una volta che i sorgenti compilano è necessario impostare a mano il webhook da telegram.
Va quindi configurato il vettore dei config, contenuto dentro il file \_config.php.

Inoltre sarebbe opportuno (ma non necessario) eseguire periodicamente lo script update_items.php per mantenere la tabella sugli oggetti aggiornata. In realtà non serve in quanto il bot aggiorna la tabella quando qualcuno inoltra un oggetto che non vi è presente.

## Deploy
Se tutto funziona è possibile usare il bot da Telegram

## Il progetto
Questo progetto dipende da un altro bot pertanto la sua vita è segnata da quella di Loot Bot.
Accetto le PR che ritengo migliorino il codice in qualsiasi modo.
