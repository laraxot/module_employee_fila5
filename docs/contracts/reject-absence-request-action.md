---
title: "Contratto — RejectAbsenceRequestAction"
type: contract
action: "Modules\Employee\Actions\RejectAbsenceRequestAction"
tags: [contract, action, assenze]
created: 2026-11-24
updated: 2026-11-24
---

# RejectAbsenceRequestAction

Sorgente: `app/Actions/RejectAbsenceRequestAction.php`. Chiamata dall'azione `reject` di
`app/Filament/Resources/AbsenceRequestResource/Tables/AbsenceRequestsTable.php`.

## Input

- `AbsenceRequest $request`, `$decidedByUserId` (`int|string`), `$decidedAt` facoltativo (default `now()`).

## Output

- La stessa `AbsenceRequest`, salvata.

## Invarianti

| ID | Regola | Group Pest |
|---|---|---|
| EMPLOYEE-ABSENCE-REJECT-INV-1 | Scrive insieme `status = AbsenceRequestStatusEnum::REJECTED->value`, `decided_by_user_id` e `decided_at`. | — |
| EMPLOYEE-ABSENCE-REJECT-INV-2 | `decided_at` è `$decidedAt` se dato, altrimenti l'istante della chiamata. | — |

## Effetti collaterali

- Update della riga in `absence_requests` con `forceFill()->save()` (ignora `$fillable`).

## Errori

- Errori del database si propagano.

## Non fa

- **Non controlla lo stato di partenza**: rifiuta anche una richiesta già decisa. Il vincolo "solo PENDING" sta solo nella
  `visible()` dell'azione della tabella; chi la chiama da altrove deve controllarlo.
- Non controlla l'autorizzazione: la tabella usa `->authorize('reject')` (policy).
- Non notifica il dipendente.
- Debito del chiamante: la tabella passa `(int) Auth::id()`, che tronca un id UUID (vedi
  [ADR](../adr/user-id-string-columns-without-cross-db-fk.md)).
