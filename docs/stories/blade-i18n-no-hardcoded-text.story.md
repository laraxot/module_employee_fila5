---
title: "Niente testi fissi nelle Blade del modulo Employee (multilingua it/en/de/es)"
type: story
status: implemented
module: Employee
updated: 2026-10-30
tags: [i18n, blade, multilingua]
---

# Niente testi fissi nelle Blade del modulo Employee

## Scopo

Il sito risponde in italiano, inglese, tedesco e spagnolo: un testo scritto dentro una Blade esce uguale in ogni lingua. Il `fallback_locale` e' `it`, quindi una chiave mancante in `en`/`de`/`es` non da' errore e mostra l'italiano.

## Regola operativa

- Ogni testo visibile passa da `{{ __('employee::contesto.collezione.chiave.tipo') }}`: cinque elementi, dove `contesto` e' il nome del file lingua e `tipo` il genere di testo (`label`, `title`, `description`, `placeholder`, `tooltip`, `helper_text`, `message`, `aria_label`, `alt`; [regola 008](../../../../../docs/wiki/rules/008-translation-5-level-protocol.md)). Mai la frase come chiave: nessun file la traduce, e il traduttore ignora `['default' => ...]`.
- La stringa va in `Modules/Employee/lang/{it,en,de,es}/<contesto>.php`, con le stesse chiavi e gli stessi `:parametri` nelle quattro lingue. `it` e' l'originale; `en`, `de`, `es` sono traduzioni vere.
- Vale anche per `aria-label`, `placeholder`, `title`, `alt` e, in PHP, per `->label()`, `->placeholder()`, `->tooltip()`, `->helperText()` e per le etichette di stato restituite dai componenti Livewire.
- Frasi con variabili: segnaposto `:param` e `trans_choice` per i plurali (conteggi di timbrature, ore).
- Link a pagine CMS: niente `href="/it/..."`, niente `url('/...')`, niente nomi di rotta inventati per pagina. Si usa la rotta generica della shell Folio, `route('container0.view', ['container0' => 'area-personale', 'slug0' => 'notifiche'])`: il prefisso lingua lo aggiunge Folio. L'indice ha un nome diverso per tema (`container0` in Two e Meetup, `container0.index` in Sixteen e TwentyOne): una vista di modulo condivisa tra temi usa `view` oppure riceve l'URL dal chiamante. I nomi Folio veri (`home`, `login`, `logout`) restano validi.
- Non sono errori: marchi e nomi propri, sigle, numeri, valori che arrivano dal modello.

## Cosa e' stato fatto

- Rilevatore `bashscripts/tools/i18n/find-hardcoded-blade-text.py` sul modulo: da 67 occorrenze in 9 file a 3 in 2 file (stesso strumento, commit di partenza contro stato attuale).
- 7 Blade convertite: viste di timbratura, ore lavorate e tabellone delle ore.
- Nuova collezione `work_hours_board` in `it`, `en`, `de`, `es` (8 chiavi per lingua).
- La cartella `lang/es` non esisteva: creati file parziali (`time_entries`, `time_tracking`, `workhour`, `work_hours`) con le sole chiavi usate dalle Blade. Completate le chiavi preesistenti usate dalle Blade: 8 in `de`, 58 in `es`.
- Controllo statico sulle 68 Blade di User, Employee e TechPlanner: 311 chiavi usate, nessuna mancante in `it`, `en`, `de`, `es`.

## Residui dichiarati (3 occorrenze)

| Categoria | N |
|---|---:|
| Scaffold nWidart non instradato (indice e layout master) | 3 |

Testi in PHP non toccati: circa 13 chiamate `->label()`, `->placeholder()`, `->tooltip()` con testo fisso (conteggio euristico) e le etichette di stato della timbratura restituite in inglese dal componente Livewire e mostrate dalla Blade.

## Limiti

- Nessuna verifica a video: chiavi e compilazione sono controllate in modo statico.
- `lang/es` e' parziale: le chiavi non ancora tradotte ricadono sull'italiano.
- Una parte delle chiavi usate dalle Blade (circa 47 usi) e' nata con quattro elementi invece di cinque: funziona in tutte le lingue ma non e' stata rinominata.
- Verifica del 2026-10-30: PHPStan sui moduli senza errori, compilazione Blade dei file convertiti senza errori, nessuna chiave usata dalla campagna manca in `it`, `en`, `de`, `es`.

## Riferimenti

Metodo e regola completa: [story del tema Two](../../../../Themes/Two/docs/stories/blade-i18n-no-hardcoded-text.story.md); protocollo a cinque elementi: [regola 008](../../../../../docs/wiki/rules/008-translation-5-level-protocol.md). Il dettaglio per file e' nel repo privato di audit.
