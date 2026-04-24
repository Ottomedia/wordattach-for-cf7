=== Word Template Attachments for CF7 ===
Requires at least: 6.5
Requires PHP: 8.0


Fills Word templates with form data and send them via email

== Description ==
Il plugin permette di fare un "mail merge" dei dati di qualunque form di Contact Form 7 in un file di Word (che chiamiamo template) che viene poi inviato in allegato alla mail che CF7 invia.

Può essere utile per utilizzare i dati del form per compilare un modulo d'ordine, un formulario, un attesto ecc ecc.

Per istruzioni su installazione, configurazione e utilizzo vai sulla [pagina README del progetto](https://github.com/Ottomedia/wordattach-for-cf7/blob/main/README.md)


== Installation ==
Per istruzioni su installazione, configurazione e utilizzo vai sulla [pagina README del progetto](https://github.com/Ottomedia/wordattach-for-cf7/blob/main/README.md)

== Debug ==
È possibile attivare un report di debug che va su `error_log` oppure viene accodato alla mail admin (Mail 1) di CF7.

Impostazioni aggiuntive (CF7):
```
wt_debug: on
wt_debug_to: mail
wt_debug_level: basic
```

- `wt_debug`: on/off abilita il debug
- `wt_debug_to`: log | mail | both (dove inviare il report)
- `wt_debug_level`: basic | verbose

Il report include ambiente (PHP/WP/CF7, percorsi WP), step eseguiti, esiti e messaggi d'errore. In caso di template mancanti mostra suggerimenti su percorso consigliato, virgolette per path con spazi e permessi directory.

