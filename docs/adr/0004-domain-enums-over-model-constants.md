---
title: "ADR — Stati e tipi del dominio HR sono enum, non costanti dei modelli"
type: decision
status: accepted
created: 2026-11-24
updated: 2026-11-24
related:
  - ../bmad/domain-enums-over-model-constants.md
  - ../../app/Enums/AbsenceRequestStatusEnum.php
---

# ADR: stati e tipi del dominio HR sono enum, non costanti dei modelli

## Contesto

- Stati e tipi di richieste di assenza, timbrature e ore lavorate erano costanti stringa sparse nei modelli; form,
  factory, query e policy le ripetevano a mano.
- La decisione esiste in forma breve in `docs/bmad/domain-enums-over-model-constants.md`.
- Domanda: dove sta il vocabolario finito del dominio?

## Driver della decisione

- Un solo elenco tipizzato: stati e tipi del dominio in un punto solo, con etichette tradotte.
- Tipo sicuro ai confini: form, factory, query e policy non ripetono stringhe a mano né scrivono valori fuori lista.
- Separazione netta: il vocabolario di dominio non si mescola alle costanti tecniche dei modelli.

## Decisione

- Backed enum in `app/Enums/`: `AbsenceRequestStatusEnum`, `AbsenceRequestTypeEnum`, `TimeEntryStatusEnum`,
  `WorkHourStatusEnum`, `WorkHourTypeEnum`.
- Il database conserva le stesse stringhe; ai confini (query, factory, form, persistenza) si usa `->value`
  (es. `ApproveAbsenceRequestAction`).
- Costanti tecniche (percorsi, chiavi di cache, limiti numerici) restano costanti.

## Alternative scartate

- Costanti `STATUS_*` nei modelli: nessun tipo, nessuna etichetta tradotta, facile scrivere un valore fuori elenco.
- Colonne `enum` del database come unica fonte: cambiano solo con una migrazione e il codice non le vede.

## Conseguenze

- Pro: un solo elenco, tipizzato, con etichette.
- Debito: `WorkHour::TYPES` e `WorkHour::STATUSES` restano come liste legacy derivate dagli enum; la migrazione
  `create_work_hours_table` usa ancora colonne `enum` con i valori scritti a mano.

## Verifica

`Modules/Employee/tests/Unit/Docs/EmployeeDecisionsTest.php`, test `it keeps domain vocabularies in enums, not in model constants`:
fallisce se un modello di `app/Models` dichiara una costante `STATUS_*` o `TYPE_*`.
