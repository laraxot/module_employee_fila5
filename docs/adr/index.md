---
title: "Indice degli ADR — Employee"
type: index
module: Employee
status: active
updated: 2026-11-27
---

# Indice degli ADR — Employee

| ADR | Stato | Perché | Conferma |
|---|---|---|---|
| [0001-employee-owns-hr-domain](0001-employee-owns-hr-domain.md) | accepted | Employee è l'owner di dipendenti, assenze e presenze; l'identità resta di User. | `phpstan analyse Modules/Employee` e Pest scoped |
| [0002-employee-parental-child-of-user](0002-employee-parental-child-of-user.md) | accepted | Il dipendente è un figlio Parental di User sulla tabella `users`: una sola riga per persona, `Auth::user()` può essere un `Employee`. | Sì: `EmployeeDecisionsTest.php`, group `employee-parental-child-of-user` |
| [0003-user-id-string-columns-without-cross-db-fk](0003-user-id-string-columns-without-cross-db-fk.md) | accepted | Gli id utente nelle tabelle del modulo sono stringhe da 36, senza FK verso `users` (connesioni diverse). | Sì: `EmployeeDecisionsTest.php`, group `employee-user-id-string-columns` |
| [0004-domain-enums-over-model-constants](0004-domain-enums-over-model-constants.md) | accepted | Stati e tipi del dominio HR sono backed enum in `app/Enums`, non costanti dei modelli. | Sì: `EmployeeDecisionsTest.php`, group `employee-domain-enums` |

Formato e regole: [README](README.md).
