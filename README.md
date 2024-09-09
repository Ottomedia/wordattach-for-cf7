# Work in progress #

1. Crea il file "modello" in Word e salvalo col nome `template-form-_N_` dove _N_ è l'ID del form CF7 che raccoglie i dati per compilarlo
1. Metti il file di word che fa da template nella cartella `wp-content/uploads/wpcf7-templates`. 


I file "compilati" vengono salvati temporaneamente in `wp-content/uploads/wpcf7-templates/merged` e inviati via email.

Al momento vengono allegati solo alla _Mail 1_ ... vediamo se abilitare l'invio ad entrambe con uno switch

## Personalizzare il nome del file generato ##

Di default il nome del file generato è `document-XXXX-XX-XX-HH-MM-SS` dove `XXXX-XX-XX-HH-MM-SS` + la data e l'ora della creazione del file.

Il nome di può personalizzare aggiungendo una configurazione addizionale in _Impostazioni aggiuntive_.

Es: `wt_filename: [your-subject]-{datetime}`

La stringa può essere personalizzata usando qualunque tag del form CF7 oltre ai tag "special" già disponibili in CF7 quali _data di invio_ _ora di invio_ _titolo pagina_ ecc... [Vedi tutti i _tag speciali_ qui](https://contactform7.com/special-mail-tags/)

Tutto il contenuto di `wt_filename:` viene filtrato per renderlo un nome di file valido (tolti gli spazi, i caratteri non validi ecc...)

## Convertire in maiuscolo e minuscolo un campo ##
In _Impostazioni aggiuntive_ puoi indicare i campi di cui vuoi cambiare la capitalizzazione prima di inserirli nel template. Per esempio puoi scrivere `wt_uppercase: [codice_fiscale]` per avere tutto il codice fiscale maiuscolo, indipendentemente da come l'ha inserito l'utente.

I campi disponibili sono:
- `wt_uppercase:` _tutto maiuscolo_
- `wt_lowercase:` _tutto minuscolo_
- `wt_ucwords:` _maiuscole le iniziali di ogni parola_
- `wt_ucfirst:` _maiuscola la prima lettera della sringa_

## Formattare un campo data ##
In _Impostazioni aggiuntive_ puoi indicare i campi data di cui vuoi cambiare il formato. Per esempio puoi scrivere `wt_format_date: [date-402]|"d/m/Y"` per avere quel campo data con un formato adatto all'Italia.

Il campo va scritto nel seguente modo:
- `wt_format_date: [nome del tag data da formattare] | formato data secondo PHP date()`

Il nome del tag data e il formato data sono separati dal simbolo `|` (pipe) e il formato della data è specificato usando i codici tipici della funzione [PHP date()](https://www.php.net/manual/en/datetime.format.php)

## Allegare altri file "statici" ##
E' possibile allegare file che vengono inviati via email così come sono, senza essere processati come templates.

Si tratta di una funzione nativa di CF7. [Consultare la documentzione qui](https://contactform7.com/file-uploading-and-attachment/#local-file-attachment)

## Changelog ##

- 1.1.1 - Enable automatic updates
- 1.1.0 - Add formatting directives
- 1.0.0 - Initial release

## todo ##
1. cartella arbitraria per caricare i template
1. multi template: ogni form può compilare più templates
1. cancellare i file "compilati" dalla cartella temporaneo dopo l'invio (magari mettere uno switch per decidere se farlo o no)
2. creare le cartelle con il file .htaccess all'attivazione del plugin
1. carta intestata centro / template personalizzato del centro
1. direttiva per indicare se allegare a mail 1, mail 2 o entrambi
1. cambiare i tag nei template da ${...} a [...] [Per un bug in PhpWord non si può fare]

## references ##
[PHPWord](https://phpoffice.github.io/PHPWord/index.html)

[Stack Excange reference](https://stackoverflow.com/questions/48189010/dynamically-attaching-file-to-contact-form-7-e-mail)

### form object ###
```php
WPCF7_ContactForm::__set_state(array(
   'id' => 5,
   'name' => 'contact-form-1',
   'title' => 'Contact form 1',
   'locale' => 'it_IT',
   'properties' => 
  array (
    'form' => '<label> Your name
    [text* your-name autocomplete:name] </label>

<label> Your email
    [email* your-email autocomplete:email] </label>

<label> Subject
    [text* your-subject] </label>

<label> Your message (optional)
    [textarea your-message] </label>
[file allegato1 filetypes:txt|pdf|doc|docx|xls|xlsx]
[submit "Submit"]',
    'mail' => 
    array (
      'active' => true,
      'subject' => '[your-subject]',
      'sender' => '[_site_title] <wordpress@contratti.local>',
      'recipient' => 'stefano@garuti.it',
      'body' => '[your-name]
[your-email]
[your-subject]
[your-message]
[allegato1]',
      'additional_headers' => 'Reply-To: [your-email]',
      'attachments' => '[allegato1]',
      'use_html' => true,
      'exclude_blank' => false,
    ),
    'mail_2' => 
    array (
      'active' => true,
      'subject' => '[_site_title] "[your-subject]"',
      'sender' => '[_site_title] <wordpress@contratti.local>',
      'recipient' => '[your-email]',
      'body' => 'Message Body:
[your-message]

-- 
This email is a receipt for your contact form submission on our website ([_site_title] [_site_url]) in which your email address was used. If that was not you, please ignore this message.',
      'additional_headers' => 'Reply-To: [_site_admin_email]',
      'attachments' => '[allegato1]',
      'use_html' => false,
      'exclude_blank' => false,
    ),
    'messages' => 
    array (
      'mail_sent_ok' => 'Thank you for your message. It has been sent.',
      'mail_sent_ng' => 'There was an error trying to send your message. Please try again later.',
      'validation_error' => 'One or more fields have an error. Please check and try again.',
      'spam' => 'There was an error trying to send your message. Please try again later.',
      'accept_terms' => 'You must accept the terms and conditions before sending your message.',
      'invalid_required' => 'Please fill out this field.',
      'invalid_too_long' => 'This field has a too long input.',
      'invalid_too_short' => 'This field has a too short input.',
      'upload_failed' => 'There was an unknown error uploading the file.',
      'upload_file_type_invalid' => 'You are not allowed to upload files of this type.',
      'upload_file_too_large' => 'The uploaded file is too large.',
      'upload_failed_php_error' => 'There was an error uploading the file.',
      'invalid_date' => 'Inserisci la data nel formato YYYY-MM-DD.',
      'date_too_early' => 'Data troppo antecedente per questo campo.',
      'date_too_late' => 'Data troppo posticipata per questo campo.',
      'invalid_number' => 'Inserisci un numero.',
      'number_too_small' => 'Numero troppo corto per questo campo.',
      'number_too_large' => 'Numero troppo lungo per questo campo.',
      'quiz_answer_not_correct' => 'La risposta al quiz non è corretta.',
      'invalid_email' => 'Inserisci un indirizzo email.',
      'invalid_url' => 'Inserisci un URL.',
      'invalid_tel' => 'Inserisci numero di telefono.',
      'captcha_not_match' => 'Il codice che hai inserito non è valido.',
    ),
    'additional_settings' => '',
  ),
   'unit_tag' => NULL,
   'responses_count' => 0,
   'scanned_form_tags' => 
  array (
    0 => 
    WPCF7_FormTag::__set_state(array(
       'type' => 'text*',
       'basetype' => 'text',
       'raw_name' => 'your-name',
       'name' => 'your-name',
       'options' => 
      array (
        0 => 'autocomplete:name',
      ),
       'raw_values' => 
      array (
      ),
       'values' => 
      array (
      ),
       'pipes' => 
      WPCF7_Pipes::__set_state(array(
         'pipes' => 
        array (
        ),
      )),
       'labels' => 
      array (
      ),
       'attr' => '',
       'content' => '',
    )),
    1 => 
    WPCF7_FormTag::__set_state(array(
       'type' => 'email*',
       'basetype' => 'email',
       'raw_name' => 'your-email',
       'name' => 'your-email',
       'options' => 
      array (
        0 => 'autocomplete:email',
      ),
       'raw_values' => 
      array (
      ),
       'values' => 
      array (
      ),
       'pipes' => 
      WPCF7_Pipes::__set_state(array(
         'pipes' => 
        array (
        ),
      )),
       'labels' => 
      array (
      ),
       'attr' => '',
       'content' => '',
    )),
    2 => 
    WPCF7_FormTag::__set_state(array(
       'type' => 'text*',
       'basetype' => 'text',
       'raw_name' => 'your-subject',
       'name' => 'your-subject',
       'options' => 
      array (
      ),
       'raw_values' => 
      array (
      ),
       'values' => 
      array (
      ),
       'pipes' => 
      WPCF7_Pipes::__set_state(array(
         'pipes' => 
        array (
        ),
      )),
       'labels' => 
      array (
      ),
       'attr' => '',
       'content' => '',
    )),
    3 => 
    WPCF7_FormTag::__set_state(array(
       'type' => 'textarea',
       'basetype' => 'textarea',
       'raw_name' => 'your-message',
       'name' => 'your-message',
       'options' => 
      array (
      ),
       'raw_values' => 
      array (
      ),
       'values' => 
      array (
      ),
       'pipes' => 
      WPCF7_Pipes::__set_state(array(
         'pipes' => 
        array (
        ),
      )),
       'labels' => 
      array (
      ),
       'attr' => '',
       'content' => '',
    )),
    4 => 
    WPCF7_FormTag::__set_state(array(
       'type' => 'file',
       'basetype' => 'file',
       'raw_name' => 'allegato1',
       'name' => 'allegato1',
       'options' => 
      array (
        0 => 'filetypes:txt|pdf|doc|docx|xls|xlsx',
      ),
       'raw_values' => 
      array (
      ),
       'values' => 
      array (
      ),
       'pipes' => 
      WPCF7_Pipes::__set_state(array(
         'pipes' => 
        array (
        ),
      )),
       'labels' => 
      array (
      ),
       'attr' => '',
       'content' => '',
    )),
    5 => 
    WPCF7_FormTag::__set_state(array(
       'type' => 'submit',
       'basetype' => 'submit',
       'raw_name' => '',
       'name' => '',
       'options' => 
      array (
      ),
       'raw_values' => 
      array (
        0 => 'Submit',
      ),
       'values' => 
      array (
        0 => 'Submit',
      ),
       'pipes' => 
      WPCF7_Pipes::__set_state(array(
         'pipes' => 
        array (
          0 => 
          WPCF7_Pipe::__set_state(array(
             'before' => 'Submit',
             'after' => 'Submit',
          )),
        ),
      )),
       'labels' => 
      array (
        0 => 'Submit',
      ),
       'attr' => '',
       'content' => '',
    )),
  ),
   'shortcode_atts' => 
  array (
  ),
   'hash' => '22a995c261ea088a9c585024378e17dd068544b8',
   'schema' => 
  WPCF7_SWV_Schema::__set_state(array(
     'properties' => 
    array (
      'version' => 'Contact Form 7 SWV Schema 2024-02',
      'locale' => 'it_IT',
    ),
     'rules' => 
    array (
      0 => 
      Contactable\SWV\FileRule::__set_state(array(
         'properties' => 
        array (
          'field' => 'allegato1',
          'accept' => 
          array (
            0 => '.txt',
            1 => '.pdf',
            2 => '.doc',
            3 => '.docx',
            4 => '.xls',
            5 => '.xlsx',
          ),
          'error' => 'You are not allowed to upload files of this type.',
        ),
      )),
      1 => 
      Contactable\SWV\MaxFileSizeRule::__set_state(array(
         'properties' => 
        array (
          'field' => 'allegato1',
          'threshold' => 1048576,
          'error' => 'The uploaded file is too large.',
        ),
      )),
      2 => 
      Contactable\SWV\RequiredRule::__set_state(array(
         'properties' => 
        array (
          'field' => 'your-name',
          'error' => 'Please fill out this field.',
        ),
      )),
      3 => 
      Contactable\SWV\MaxLengthRule::__set_state(array(
         'properties' => 
        array (
          'field' => 'your-name',
          'threshold' => 400,
          'error' => 'This field has a too long input.',
        ),
      )),
      4 => 
      Contactable\SWV\RequiredRule::__set_state(array(
         'properties' => 
        array (
          'field' => 'your-email',
          'error' => 'Please fill out this field.',
        ),
      )),
      5 => 
      Contactable\SWV\EmailRule::__set_state(array(
         'properties' => 
        array (
          'field' => 'your-email',
          'error' => 'Inserisci un indirizzo email.',
        ),
      )),
      6 => 
      Contactable\SWV\MaxLengthRule::__set_state(array(
         'properties' => 
        array (
          'field' => 'your-email',
          'threshold' => 400,
          'error' => 'This field has a too long input.',
        ),
      )),
      7 => 
      Contactable\SWV\RequiredRule::__set_state(array(
         'properties' => 
        array (
          'field' => 'your-subject',
          'error' => 'Please fill out this field.',
        ),
      )),
      8 => 
      Contactable\SWV\MaxLengthRule::__set_state(array(
         'properties' => 
        array (
          'field' => 'your-subject',
          'threshold' => 400,
          'error' => 'This field has a too long input.',
        ),
      )),
      9 => 
      Contactable\SWV\MaxLengthRule::__set_state(array(
         'properties' => 
        array (
          'field' => 'your-message',
          'threshold' => 2000,
          'error' => 'This field has a too long input.',
        ),
      )),
    ),
  )),
   'pipes' => NULL,
))
```
