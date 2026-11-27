---
title: "ADR — modulo Employee"
type: index
tags: [adr, decisioni, employee, hr]
created: 2026-11-24
updated: 2026-11-27
---

# ADR del modulo Employee

Il **perché** della forma dei dati HR: il dipendente è un utente (stessa riga di `users`), gli id utente nelle tabelle
del modulo sono stringhe senza FK perché `users` può stare su un altro database, stati e tipi sono enum.

Leggili prima di aggiungere una tabella che punta a un utente, un modello "figlio" di User o un nuovo stato.
Formato e regole: `Modules/Xot/docs/rules/action-behavioral-contract.md`.

Un file per decisione, nome `NNNN-argomento-in-kebab-case.md` senza date (la data sta nella frontmatter);
eccezioni: `README.md`, `index.md`, `0000-template.md`. Ogni ADR ha le sezioni `Contesto`, `Driver della decisione`,
`Alternative scartate`, `Decisione`, `Conseguenze`, `Conferma` (o `Verifica`).

## Decisioni

- [0001](0001-employee-owns-hr-domain.md) — Employee possiede il dominio HR (accepted).
- [0002](0002-employee-parental-child-of-user.md) — `Employee extends User`, `HasParent`, tabella `users` (accepted).
- [0003](0003-user-id-string-columns-without-cross-db-fk.md) — UUID non troncato, nessun vincolo tra connessioni (accepted).
- [0004](0004-domain-enums-over-model-constants.md) — stati e tipi in `app/Enums`, `->value` ai confini (accepted).

Elenco con stato e conferma: [index.md](index.md).

## Harness minimo: ownership, evidenza e recupero (2026-11-27)

Questo indice appartiene a **module_employee_fila5**: ogni decisione su codice, dominio o tema va nell'ADR del package owner; il root registra soltanto i vincoli di composizione. Un ADR richiede una scelta reale con alternative e conseguenze, più una **Conferma** ripetibile (oppure la dichiarazione esplicita che manca). Non creare ADR fittizi per riempire un indice.

- [Confine LaravelDay/harness Xot](https://github.com/laraxot/module_xot_fila5/blob/dev/docs/adr/0042-laravel-day-harness-adoption-boundary.md); [ownership root](https://github.com/laraxot/base_ptvx_fila5/blob/dev/docs/adr/0001-harness-minimo-e-ownership.md).
- **BMAD**: la story owner-local conserva `scope`, `must_not_change`, `evidence`, `approval_required` nei cambi rischiosi.
- **ECC**: nessun marker di merge; distinguere la prova eseguita dal semplice riferimento a un test; se la conferma non è disponibile segnare `non eseguita`.
- **Second Brain**: QMD/Graphify e `.harness/memory/{facts,lessons}.md` sono puntatori a evidenza datata, non copie degli ADR. [Runbook](https://github.com/laraxot/base_ptvx_fila5/blob/dev/docs/harness/README.md) · [Rollout #15](https://github.com/laraxot/base_ptvx_fila5/issues/15).
