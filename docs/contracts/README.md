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

## Harness minimo: ownership, evidenza e recupero (2026-11-27)

I contratti di **module_employee_fila5** restano accanto alle Action, interfacce o superfici di questo package; root e Xot definiscono lo standard, non copiano le promesse. Un contratto comportamentale esplicita **Input, Output, Invarianti, Effetti collaterali, Errori, Non fa**; ogni ID di invariante punta a un group Pest effettivo oppure segnala `non dimostrata`. Se esiste la classe, il docblock deve rimandare al file con `@contract` e `@see`; per temi senza classi PHP non inventare Action/contratti.

- [Standard Xot](https://github.com/laraxot/module_xot_fila5/blob/dev/docs/contracts/contract-authoring-standard.md) · [ownership root](https://github.com/laraxot/base_ptvx_fila5/blob/dev/docs/adr/0001-harness-minimo-e-ownership.md) · [runbook ECC/Second Brain](https://github.com/laraxot/base_ptvx_fila5/blob/dev/docs/harness/README.md).
- **BMAD**: prima di cambiare una promessa, fissa nella story owner scope, esclusioni, evidenza e approvazioni richieste.
- **ECC**: i marker di merge rendono invalido l'indice; citare una suite non significa averla eseguita. Distingui `pass`, `fail`, `non eseguito` e registra il debito in [rollout #15](https://github.com/laraxot/base_ptvx_fila5/issues/15).
