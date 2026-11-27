---
title: "Contratti — modulo Employee"
type: index
tags: [contracts, actions, employee, assenze]
created: 2026-11-24
updated: 2026-11-27
---

# Contratti del modulo Employee

Cosa promettono le Action che scrivono richieste di assenza. Nessuna invariante ha ancora un group Pest: i test di
modello del modulo richiedono un database di test migrato (debito dichiarato con «—»).

Leggili prima di chiamare queste Action fuori dalla tabella Filament: i controlli di stato e di permesso stanno nel
chiamante, non qui. Formato: `Modules/Xot/docs/rules/action-behavioral-contract.md`.

`TimeTracking/ClockInAction` e `ClockOutAction` non hanno chiamanti nel codice: niente contratto finché non si decide se usarle o toglierle.

## Formato

Un file per Action, nome in kebab-case della classe, frontmatter `type: contract` e `action: "Modules\\Employee\\Actions\\..."`,
e queste sei sezioni nell'ordine:

```markdown
## Input
## Output
## Invarianti
## Effetti collaterali
## Errori
## Non fa
```

Il docblock dell'Action rimanda al file con `@contract docs/contracts/<file>.md`. Gli ID di invariante sono stabili
(`<SCOPE>-INV-<N>`) e un test Pest li usa come `->group('<ID>')`. Template: [_template.md](_template.md).
Inventario machine-readable: [INDEX.md](INDEX.md); tabella di lavoro: [index.md](index.md).

## Contratti di Action

- [CreateAbsenceRequestAction](create-absence-request-action.md) — crea una richiesta di assenza in stato PENDING.
- [ApproveAbsenceRequestAction](approve-absence-request-action.md) — approva, con chi e quando; non controlla lo stato di partenza.
- [RejectAbsenceRequestAction](reject-absence-request-action.md) — rifiuta, con chi e quando; non controlla lo stato di partenza.
