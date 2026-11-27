---
title: "ADR — Employee possiede il dominio HR"
type: decision
status: accepted
module: Employee
created: 2026-11-27
updated: 2026-11-27
---

# Employee possiede il dominio HR

## Contesto

Employee gestisce dipendenti, assenze e presenze, mentre identità e autenticazione appartengono a User.

## Driver della decisione

- separare il dominio HR dall'identità;
- mantenere compatibilità tra connessioni e moduli;
- conservare regole verificabili nei modelli e nelle Action.

## Alternative scartate

- duplicare utenti nel modulo Employee: crea due fonti di verità;
- spostare logica HR in User: viola il confine del dominio.

## Decisione

Employee resta owner di dati e workflow HR e riferisce l'identità User tramite il contratto esistente, senza replicare credenziali.

## Conseguenze

Il modulo mantiene responsabilità chiare; i riferimenti tra database richiedono ID stringa e controlli applicativi.

## Conferma

Verificare modelli, migrazioni e test del modulo con `phpstan analyse Modules/Employee` e Pest scoped.
