---
title: "Audit qualita' modulo Employee"
type: story
status: in-review
module: Employee
created: 2026-11-19
updated: 2026-11-19
issues: ["laraxot/base_techplanner_fila5#64"]
tags: [audit, qualita, docs, phpstan, pest]
---

# Audit qualita' — modulo Employee

Story BMAD dell'audit di qualita' coordinato nella issue
[#64](https://github.com/laraxot/base_techplanner_fila5/issues/64).
Ambito: solo `Modules/Employee` (documentazione e `app/`).

## Baseline e risultato

| Verifica | Prima | Dopo |
|---|---|---|
| PHPStan max (`Modules/Employee`) | 0 errori | 0 errori |
| Pest (`Modules/Employee/tests`) | 45 falliti / 18 passati | 18 falliti / 45 passati |
| Occorrenze `mixed` in `app/` | 65 | 60 |

## Documentazione

- File `.md` con data/timestamp nel nome: 1 rinominati, 0 uniti al file canonico
  omonimo (contenuto unico conservato sotto "Contenuto unito da ..."), 1 rimossi perche' vuoti
  o gia' contenuti nel canonico. La data originale e' ora nel frontmatter `created:`.
- Duplicati esatti (contenuto identico) rimossi: 67; tenuta la copia meglio collocata
  (no `archive/`, `raw/`, suffissi `-1`/`_1`, maiuscole o underscore) e link interni aggiornati.
- `docs/index.md` (indice canonico): aggiunta la sezione "Mappa delle cartelle" con link relativi
  a tutte le aree; link rotti agli altri moduli corretti dove la destinazione era determinabile.

## Codice (`app/`)

- `Models/Employee.php`: accessor `getEmailAttribute`/`getPhoneAttribute` tipizzati `?string $value` invece di `mixed`
- `Models/Admin.php`: rimosse righe duplicate del PHPDoc e `@property-read mixed|null $pivot` (si eredita `Pivot|null` da `BaseUser`)
- `Filament/Widgets/TimeClockWidget.php`, `WorkHoursBoardWidget.php`: rimosso override `getFormSchema()` che ripeteva il default `[]` di `XotBaseSchemaWidget` con un tipo errato (`array<string, mixed>`)
- Test: aggiunto `uses(TestCase::class)` a 11 file che ne erano privi (fallivano con `Target class [db] does not exist`)

## Scoperte da gestire (non risolte in questo audit)

- BUG di schema: nessuna migrazione aggiunge a `users` le colonne di `Employee` (`employee_code`, `personal_data`, `contact_data`, `work_data`, `documents`, `photo_url`, `department_id`, `manager_id`, `position_id`, `salary_data`); inoltre `user_id` in `$fillable`/factory non ha senso per un modello STI su `users`. Causa delle 18 failure di `EmployeeTest` (lasciato senza `uses(TestCase)` finche' lo schema non esiste). Serve decisione: migrazione nel modulo Employee o tabella dedicata
- Nell'indice restano link rotti verso file mai esistiti o di altri progetti: da rivedere a mano.
- `docs/` contiene ancora `INDEX.md`, `README.md` e `index.md` affiancati (collisione su filesystem
  case-insensitive): da consolidare in un solo indice con decisione condivisa.

## Criteri di accettazione

- [x] PHPStan max a 0 errori senza ignore/baseline
- [x] Nessun `.md` con data nel nome
- [x] Nessun duplicato esatto in `docs/`
- [x] Test Pest non peggiorati
- [ ] Punti aperti sopra risolti
