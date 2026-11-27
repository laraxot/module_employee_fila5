---
title: "Indice operativo dei contratti — Employee"
type: index
module: Employee
status: active
updated: 2026-11-27
---

# Indice operativo dei contratti — Employee

Registro dei contratti `type: contract` presenti in questa cartella (ADR 0012 di Xot). Ogni file ha le sei sezioni
canoniche (`Input`, `Output`, `Invarianti`, `Effetti collaterali`, `Errori`, `Non fa`) e il marker `action:`; la classe
implementatrice rimanda al file con `@contract docs/contracts/<file>.md`. Copertura = invarianti il cui group Pest
esiste davvero in `tests/` del modulo. Comandi da `laravel/`; un gruppo = una promessa:
`vendor/bin/pest Modules/Employee/tests --group=<ID>`.

## Richieste di assenza

| Operazione | Contratto | Classe | Invarianti | Copertura | Comando di esempio |
|---|---|---|---|---|---|
| CreateAbsenceRequestAction | [create-absence-request-action](create-absence-request-action.md) | `Modules\Employee\Actions\CreateAbsenceRequestAction` | `EMPLOYEE-ABSENCE-CREATE-INV-1..2` | 0/2 | — |
| ApproveAbsenceRequestAction | [approve-absence-request-action](approve-absence-request-action.md) | `Modules\Employee\Actions\ApproveAbsenceRequestAction` | `EMPLOYEE-ABSENCE-APPROVE-INV-1..2` | 0/2 | — |
| RejectAbsenceRequestAction | [reject-absence-request-action](reject-absence-request-action.md) | `Modules\Employee\Actions\RejectAbsenceRequestAction` | `EMPLOYEE-ABSENCE-REJECT-INV-1..2` | 0/2 | — |

## Note

- L'inventario machine-readable e' [INDEX.md](INDEX.md); il formato e' descritto in [README.md](README.md) e il template in [_template.md](_template.md).
- Nessuna invariante ha un group Pest oggi: i test di modello del modulo richiedono un database di test migrato (debito dichiarato con «—»).
- Le decisioni dietro i contratti stanno in [../adr/](../adr/index.md).
