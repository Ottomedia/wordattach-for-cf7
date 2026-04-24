# Changelog

Tutte le modifiche rilevanti a questo progetto sono documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/it/1.1.0/),
e questo progetto aderisce al [Semantic Versioning](https://semver.org/lang/it/).

## [1.6.1] - 2026-04-24

### Changed
- Il workflow di rilascio ora crea automaticamente anche la GitHub Release dopo il tag versione.
- Le note della release vengono compilate prendendo in automatico il contenuto della sezione corrispondente in `CHANGELOG.md`.

### Docs
- Aggiornata la documentazione tecnica con esempio pratico sul formato da usare nel changelog per la release automatica.

### Notes
- Nessuna modifica funzionale al comportamento del plugin lato compilazione template e invio allegati.

## [1.6.0] - 2026-04-24

### Changed
- Migrazione dell'infrastruttura di rilascio da Bitbucket a GitHub, con tagging automatico delle nuove versioni.
- Aggiornati i riferimenti del plugin al repository GitHub (metadati plugin e link documentazione).
- Migliorata la leggibilita' della documentazione delle direttive nel README.

### Notes
- Nessuna modifica funzionale al comportamento del plugin lato compilazione template e invio allegati.


## [1.5.2] - 2025-01-20

### Fixed
- Attivato l'escaping dell'output in PhpWord per prevenire la corruzione dei file XML generati quando i dati del form contengono caratteri speciali

### Changed
- Migliorato il formato del file CHANGELOG.md per rispettare le convenzioni di Keep a Changelog
- Aggiunta documentazione dettagliata sugli standard di codifica WordPress e formato changelog nelle istruzioni per AI coding agent

## [1.5.1] - 2025-11-19

### Changed
- Aggiornata la documentazione README con istruzioni dettagliate per la configurazione del path assoluto del template

## [1.5.0] - 2025-11-19

### Added
- Sistema di debug opzionale configurabile tramite direttive `wt_debug`, `wt_debug_to` e `wt_debug_level`
- Report di debug inviabile via email o log per semplificare la diagnosi dei problemi
- Diagnostica avanzata per template mancanti con suggerimenti sul path corretto e verifica permessi

### Changed
- Migliorato il pattern di default per il nome file generato quando `wt_filename` non è specificato
- Ottimizzata la gestione degli array nei dati del form (singolo elemento → scalare, multipli → stringa separata da virgole)

## [1.4.0] - 2024-10-17

### Added
- Supporto per il tag speciale `[_date]` per includere la data di invio del form

## [1.3.0] - 2024-10-16

### Added
- Supporto per path personalizzato del template tramite direttiva `wt_template`
- Supporto per nome file personalizzato tramite direttiva `wt_filename` con sostituzione dei mail tag

## [1.2.0] - 2024-09-20

### Changed
- I file generati vengono ora eliminati automaticamente dopo l'invio dell'email

## [1.1.2] - 2024-09-20

### Fixed
- Correzione bug nel sistema di aggiornamenti automatici

## [1.1.1] - 2024-09-09

### Added
- Attivato il sistema di aggiornamenti automatici del plugin

## [1.1.0] - 2024-09-09

### Added
- Direttive di formattazione: `wt_uppercase`, `wt_lowercase`, `wt_ucwords`, `wt_ucfirst`
- Direttiva `wt_format_date` per formattare campi data con pattern personalizzati

## [1.0.0] - 2024-09-05

### Added
- Release iniziale del plugin
- Integrazione con Contact Form 7 per generare documenti Word da template
- Processamento automatico dei dati del form in template .docx
- Allegato automatico del documento generato all'email in uscita
- Supporto per placeholder nel formato `${field-name}`

