---
title: "ADR — modulo Employee"
type: index
tags: [adr, decisioni, employee, hr]
created: 2026-11-24
updated: 2026-11-24
---

# ADR del modulo Employee

Il **perché** della forma dei dati HR: il dipendente è un utente (stessa riga di `users`), gli id utente nelle tabelle
del modulo sono stringhe senza FK perché `users` può stare su un altro database, stati e tipi sono enum.

Leggili prima di aggiungere una tabella che punta a un utente, un modello "figlio" di User o un nuovo stato.
Formato e regole: `Modules/Xot/docs/rules/action-behavioral-contract.md`.

## Decisioni

- [Il dipendente è un figlio Parental di User](employee-parental-child-of-user.md) — `Employee extends User`, `HasParent`, tabella `users`.
- [Id utente stringa da 36 senza FK](user-id-string-columns-without-cross-db-fk.md) — UUID non troncato, nessun vincolo tra connessioni.
- [Enum di dominio invece di costanti](domain-enums-over-model-constants.md) — stati e tipi in `app/Enums`, `->value` ai confini.
