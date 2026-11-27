---
title: "Contratto — CreateAbsenceRequestAction"
type: contract
action: "Modules\Employee\Actions\CreateAbsenceRequestAction"
tags: [contract, action, assenze]
created: 2026-11-24
updated: 2026-11-24
---

# CreateAbsenceRequestAction

Sorgente: `app/Actions/CreateAbsenceRequestAction.php`.

## Input

- `$userId` (`int|string`, UUID), `$type`, `$startsAt`, `$endsAt`, `$notes` facoltative.

## Output

- `AbsenceRequest` appena creata.

## Invarianti

| ID | Regola | Group Pest |
|---|---|---|
| EMPLOYEE-ABSENCE-CREATE-INV-1 | Ogni richiesta nasce con `status = AbsenceRequestStatusEnum::PENDING->value`. | — |
| EMPLOYEE-ABSENCE-CREATE-INV-2 | `user_id` si salva così com'è (stringa da 36), mai convertito in intero. | — |

## Effetti collaterali

- Insert in `absence_requests` (connessione `employee`).

## Errori

- Errori del database si propagano.

## Non fa

- Non valida che `$endsAt` sia dopo `$startsAt`, né che `$type` sia un caso di `AbsenceRequestTypeEnum`, né le
  sovrapposizioni con altre assenze: valida chi la chiama (form Filament).
- Non notifica il responsabile.
