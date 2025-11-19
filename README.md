# Word Template Attachments for CF7

## Cosa fa questo plugin
Il plugin permette di fare un "mail merge" dei dati di qualunque form di Contact Form 7 in un file di Word (che chiamiamo template) che viene poi inviato in allegato alla mail che CF7 invia.

Può essere utile per utilizzare i dati del form per compilare un modulo d'ordine, un formulario, un attesto ecc ecc.

## Preparare il template

1. Scrivi il file "modello" in Word. In ogni punto in cui voui che venga inserito un campo di CF7 inserisci il segnaposto `${_nome_del_campo_CF7_}` (per esempio, per inserire il campo `[your-subject]` dovrai scrivere in Word `${your-subject})` 
1. Salva il file col nome `template-form-_N_.docx` dove _N_ è l'ID del form CF7 che raccoglie i dati per compilarlo
1. Metti il file di Word che fa da template nella cartella `wp-content/uploads/wpcf7-templates`. 

Attenzione: i file generati vengono spediti per email e non vengono mantenuti sul server dopo l'invio

Al momento vengono allegati solo alla _Mail 1_ ... vediamo se abilitare l'invio ad entrambe con uno switch

## Configurazioni

## Riepilogo delle direttive disponibili 

Le direttive si inseriscono nel form di Contact Form 7 alla voce "Impostazioni aggiuntive" (Additional Settings), una per riga. Vengono lette al momento dell'invio del modulo e non richiedono altro codice o modifiche al template (salvo dove specificato).

| Direttiva | Forma | Scopo |
|-----------|-------|-------|
| `wt_template:` | `wt_template: /percorso/assoluto/al/file.docx` (ripetibile) | Specifica uno o più template `.docx`. Se assente usa `wp-content/uploads/wpcf7-templates/template-form-{ID}.docx`. |
| `wt_filename:` | `wt_filename: [your-subject]-{datetime}` | Pattern nome file generato (senza estensione). Mail tags e special tags CF7 supportati. |
| `wt_uppercase:` | `wt_uppercase: [campo]` | Converte il campo in maiuscolo. |
| `wt_lowercase:` | `wt_lowercase: [campo]` | Converte il campo in minuscolo. |
| `wt_ucwords:` | `wt_ucwords: [campo]` | Capitalizza l'iniziale di ogni parola. |
| `wt_ucfirst:` | `wt_ucfirst: [campo]` | Capitalizza la prima lettera della stringa. |
| `wt_format_date:` | `wt_format_date: [campo-data]|d/m/Y` | Riformatta una data con specifica di `date()`. |
| `wt_debug:` | `wt_debug: on|off` | Abilita il sistema di debug. |
| `wt_debug_to:` | `wt_debug_to: log|mail|both` | Destinazione del report: log file, email admin, oppure entrambi. |
| `wt_debug_level:` | `wt_debug_level: basic|verbose` | Livello di dettaglio (estensioni future). |

Note:
- Ogni direttiva va su una riga distinta nelle "Impostazioni aggiuntive" del form CF7.
- Direttive ripetibili (es. `wt_template`, trasformazioni) vengono iterate in ordine.
- Campi array (checkbox multipli) vengono normalizzati prima delle trasformazioni.


### Utilizzare un template con nome e percorso a piacere

E' possibile salvare il template anche in altre locazioni del server e dargli un nome personalizzato.

Nella sezione _Impostazioni aggiuntive_ del form di CF7 puoi indicare il _path_ e il nome del file (ricordati di terminarlo con `.docx`) da utilizzare.

Usa la direttiva `wt_template:` seguita dal path completo. 

_Esempio_
```
wt_template: C:\Users\Mario\app\public\wp-content\uploads\2024\10\test-form.docx
```

### Personalizzare il nome del file generato

Di default il nome del file generato è `document-XXXX-XX-XX-HH-MM-SS` dove `XXXX-XX-XX-HH-MM-SS` + la data e l'ora della creazione del file.

Il nome di può personalizzare aggiungendo una configurazione addizionale nella sezione _Impostazioni aggiuntive_ del form di CF7 .

_Esempio_
```
wt_filename: [your-subject]-{datetime}
```

La stringa può essere personalizzata usando qualunque tag del form CF7 oltre ai tag "special" già disponibili in CF7 quali _data di invio_, _ora di invio_, _titolo pagina_ ecc... [Vedi tutti i _tag speciali_ qui](https://contactform7.com/special-mail-tags/)

Tutto il contenuto di `wt_filename` viene filtrato per renderlo un nome di file valido (tolti gli spazi, i caratteri non validi ecc...)

### Convertire in maiuscolo e minuscolo un campo
Nella sezione _Impostazioni aggiuntive_ del form di CF7  puoi indicare i campi di cui vuoi cambiare la capitalizzazione prima di inserirli nel template. Per esempio puoi scrivere `wt_uppercase: [codice_fiscale]` per avere tutto il codice fiscale maiuscolo, indipendentemente da come l'ha inserito l'utente.

I campi disponibili sono:
- `wt_uppercase:` _tutto maiuscolo_
- `wt_lowercase:` _tutto minuscolo_
- `wt_ucwords:` _maiuscole le iniziali di ogni parola_
- `wt_ucfirst:` _maiuscola la prima lettera della sringa_

_Esempio_
```
wt_uppercase: [your-name]
wt_uppercase: [your-subject]
```

### Formattare un campo data
Nella sezione _Impostazioni aggiuntive_ del form di CF7  puoi indicare i campi data di cui vuoi cambiare il formato. Per esempio puoi scrivere `wt_format_date: [date-402]|"d/m/Y"` per avere quel campo data con un formato adatto all'Italia.

Il campo va scritto nel seguente modo:
- `wt_format_date: [nome del tag data da formattare] | formato data secondo PHP date()`

Il nome del tag data e il formato data sono separati dal simbolo `|` (pipe) e il formato della data è specificato usando i codici tipici della funzione [PHP date()](https://www.php.net/manual/en/datetime.format.php)

_Esempio_
```
wt_format_date: [date-402]|d/m/Y
```
### Inserire la data di compilazione nel documento generato
La data di invio del modulo si può ottenere con il tag `[_date]`

Di conseguenza, nel template Word dove si vuole ottenere la data di invio, bisogna inserire il segnaposto `${_date}`

### Allegare altri file "statici" 
E' possibile allegare file che vengono inviati via email così come sono, senza essere processati come templates.

Si tratta di una funzione nativa di CF7. [Consultare la documentzione qui](https://contactform7.com/file-uploading-and-attachment/#local-file-attachment)

## Privacy 
I file "compilati" vengono salvati nella cartella di uploads temporanei di CF7 (di soli `wp-content/uploads/wpcf7_uploads`) in una sottocartella temporanea chiamata `wacf7_merged` e inviati via email. 
Dopo che sono stati inviati la cartella temporaneo viene rimossa. **Nessun file viene mantenuto sul server**


## da fare / roadmap 
1. più direttive separate da una virgola
1. multi template: ogni form può compilare più templates
2. creare le cartelle con il file .htaccess all'attivazione del plugin
1. carta intestata centro / template personalizzato del centro
1. direttiva per indicare se allegare a mail 1, mail 2 o entrambi
1. _cambiare i tag nei template da ${...} a [...]_ **[Per un bug in PhpWord non si può fare]**

## Debug e Troubleshooting

Puoi attivare un sistema di debug che scrive su `error_log` oppure accoda un report alla mail admin (Mail 1) inviata da CF7.

- `wt_debug: on|off` abilita o disabilita il debug (default: off)
- `wt_debug_to: log|mail|both` seleziona dove inviare il report (log, email admin, o entrambi)
- `wt_debug_level: basic|verbose` controlla il livello di dettaglio (attualmente usato per futuri approfondimenti)

Esempi (Impostazioni aggiuntive CF7):
```
wt_debug: on
wt_debug_to: both
wt_debug_level: basic
```

Il report nell'email è formattato in modo leggibile (HTML o testo) e include:
- Environment: versioni PHP/WP/CF7, `uploads.basedir`, dir temporanea CF7, dir template
- Steps: percorso template scelti, esito controlli, salvataggio file, allegati
- Errori/exception di PhpWord quando presenti

Quando un template non viene trovato:
- Viene mostrato `template_missing` con: path normalizzato, esistenza/scrivibilità della cartella, `realpath`, se è sotto `uploads`.
- Se il path arriva da direttiva (`wt_template:`) include suggerimenti:
  - `hint_suggested_path`: propone un percorso valido sotto `wp-content/uploads/wpcf7-templates/` usando il nome file fornito (se termina in `.docx`) oppure `template-form-{ID}.docx`.
  - `hint_spaces_in_path`: se il path contiene spazi consiglia di racchiuderlo tra virgolette per evitare parsing errato.
  - `hint_dir_permissions`: la directory esiste ma non è scrivibile; verifica permessi / owner / ACL.

Suggerimento percorso di default:
```
wp-content/uploads/wpcf7-templates/template-form-{ID_FORM}.docx
```
o, se fornisci un nome file valido `.docx`, usa quello nella stessa cartella.

