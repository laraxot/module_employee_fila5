---
title: "ADR — Il dipendente è un figlio Parental di User sulla tabella users"
type: decision
status: accepted
created: 2026-11-24
updated: 2026-11-24
related:
  - ../../app/Models/Employee.php
  - ../../app/Models/User.php
---

# ADR: il dipendente è un figlio Parental di User sulla tabella `users`

## Contesto

- Un dipendente è prima di tutto una persona che entra nel pannello: password, ruoli, team, consensi, media e log degli
  accessi vengono dal modulo User (`Modules/Employee/app/Models/User.php` estende `Modules\User\Models\BaseUser`).
- I dati di lavoro (`employee_code`, `personal_data`, `contact_data`, `work_data`, `department_id`, `manager_id`,
  `position_id`, `salary_data`) sono propri del dipendente.
- Domanda: tabella `employees` collegata a `users`, o stessa riga con un tipo?

## Decisione

`Modules/Employee/app/Models/Employee.php`: `class Employee extends User`, `use HasParent;`, `protected $table = 'users'`.
Single-table inheritance di `tightenco/parental`: il discriminante è la colonna `type` di `users`, e `Employee::query()`
restituisce solo le righe del suo tipo.

## Alternative scartate

- Tabella `employees` 1:1 con `users`: due righe per persona da tenere allineate, join per ogni elenco, e l'utente
  autenticato non sarebbe un `Employee` (le policy dovrebbero ricaricarlo).
- Campi del dipendente direttamente su `BaseUser`: il modulo User, condiviso, conoscerebbe il dominio HR.

## Conseguenze

- Pro: `Auth::user()` può già essere un `Employee`; relazioni di User (ruoli, team, consensi) valgono senza codice.
- Contro: le colonne del dipendente stanno su `users`, quindi sulla connessione `user`, mentre le tabelle del modulo
  (`absence_requests`, `time_records`, ...) stanno sulla connessione `employee`: niente FK tra le due (vedi
  [0003-user-id-string-columns-without-cross-db-fk](0003-user-id-string-columns-without-cross-db-fk.md)).
- Le relazioni `workHours`/`leaves` del dipendente puntano a tabelle di un'altra connessione: niente `whereHas` tra
  database diversi.

## Verifica

- `Modules/Employee/tests/Unit/Docs/EmployeeDecisionsTest.php`, test `it keeps Employee a Parental child of User on the users table`
  (source-level, verde; group `employee-parental-child-of-user`).
- `Modules/Employee/tests/Unit/Models/EmployeeTest.php` (`employee uses users table via parental sti`) prova lo stesso a
  runtime ma richiede un database di test con `users` migrata.
