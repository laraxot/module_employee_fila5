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
