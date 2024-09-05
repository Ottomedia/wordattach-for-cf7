# Work in progress #

1. Crea il file "modello" in Word e salvalo col nome `template-form-_N_` dove _N_ è l'ID del form CF7 che raccoglie i dati per compilarlo
1. Metti il file di word che fa da template nella cartella `wp-content/uploads/wpcf7-templates`. 


I file "compilati" vengono salvati temporaneamente in `wp-content/uploads/wpcf7-templates/merged` e inviati via email.

Al momento vengono allegati solo alla _Mail 1_ ... vediamo se abilitare l'invio ad entrambe con uno switch

## todo ##
1. cancellare i file "compilati" dalla cartella temporaneo dopo l'invio (magari mettere uno switch per decidere se farlo o no)
2. creare le cartelle con il file .htaccess all'attivazione del plugin

[PHPWord](https://phpoffice.github.io/PHPWord/index.html)

[Stack Excange reference](https://stackoverflow.com/questions/48189010/dynamically-attaching-file-to-contact-form-7-e-mail)
