---
title: "ADR — Gli id utente nelle tabelle del modulo sono stringhe da 36, senza FK verso users"
type: decision
status: accepted
created: 2026-11-24
updated: 2026-11-24
related:
  - ../../database/migrations/2026_07_24_000001_create_absence_requests_table.php
  - ../../database/migrations/2026_07_24_000004_create_time_records_table.php
---

# ADR: gli id utente nelle tabelle del modulo sono stringhe da 36, senza FK verso `users`

## Contesto

- `BaseUser` usa `HasUuids`: l'id utente è un UUID di 36 caratteri.
- Una colonna intera lo tronca in silenzio (MySQL in modalità non strict, o un cast `(int)`): la richiesta di assenza
  finisce legata all'utente sbagliato o a nessuno (commento nelle migrazioni).
- `users` sta sulla connessione `user`; le tabelle del modulo sulla connessione `employee`
  (`Modules/Employee/app/Models/BaseModel.php`). Con database distinti MySQL non ammette FK tra i due.
- Domanda: che tipo e che vincoli per `user_id`, `decided_by_user_id` e simili?

## Driver della decisione

- Integrità dell'id: l'UUID va conservato intero, mai troncato da un cast o da una colonna intera.
- Portabilità multi-database: `users` può stare su un'altra connessione, dove MySQL non ammette FK.
- Uniformità tra driver: la stringa da 36 funziona uguale su SQLite e MySQL.

## Decisione

- `$table->string('<colonna>', 36)` per `absence_requests.user_id`, `absence_requests.decided_by_user_id`,
  `time_records.user_id`.
- Nessuna FK verso `users`: l'integrità è applicativa (relazioni Eloquent), come dichiarato nei commenti delle migrazioni.

## Alternative scartate

- `foreignUuid(...)->constrained()`: fallisce quando `users` è su un altro database.
- `unsignedBigInteger`: tronca l'UUID.
- `uuid()`: corretto sui database che lo supportano, ma SQLite e MySQL lo trattano diversamente; la stringa da 36 è uguale ovunque.

## Conseguenze

- Pro: funziona con un database solo o con utenti separati.
- Contro: cancellare un utente non cancella né annulla le righe del modulo.
- Debito: `create_work_hours_table` usa ancora `uuid('employee_id')` con `foreign()` verso `users`, e
  `AbsenceRequestsTable` passa `(int) Auth::id()` ad Approve/Reject: con id UUID `decided_by_user_id` riceve un valore
  troncato. Da correggere (decisione per chi mantiene il modulo).

## Verifica

`Modules/Employee/tests/Unit/Docs/EmployeeDecisionsTest.php`, test `it stores user ids as 36-char strings without a cross-database foreign key`
(source-level, verde). `Modules/Employee/tests/Feature/UserIdColumnsAcceptUuidTest.php` controlla lo schema reale ma
richiede le tabelle migrate nel database di test.
