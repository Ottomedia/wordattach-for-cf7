## References 
[PHPWord](https://phpoffice.github.io/PHPWord/index.html)

[Stack Excange reference](https://stackoverflow.com/questions/48189010/dynamically-attaching-file-to-contact-form-7-e-mail)

## Release

Le release vengono gestite automaticamente da GitHub Actions tramite il workflow `.github/workflows/auto-tag-version.yml`.

Il comportamento replica la pipeline precedente:
- il workflow parte su `push` verso `main`
- controlla solo il commit `HEAD` rispetto a `HEAD~1`
- crea un tag annotato quando cambia `wordattach-for-cf7.php` e il valore di `Version:` non esiste già come tag Git
- crea la GitHub Release usando il testo della sezione corrispondente in `CHANGELOG.md`

Flusso operativo:
1. Aggiorna `Version:` in `wordattach-for-cf7.php`
2. Esegui il merge o push su `main`
3. GitHub Actions crea il tag `x.y.z` sul commit corrente
4. GitHub Actions pubblica la release `x.y.z` con note prese dal changelog

Formato CHANGELOG richiesto per la release automatica:
- La versione deve avere un header esatto nel formato `## [x.y.z]` (stessa versione presente in `wordattach-for-cf7.php`).
- Il workflow estrae tutto il testo dopo `## [x.y.z]` fino al prossimo header `## [...]`.
- Puoi usare sezioni come `### Added`, `### Changed`, `### Fixed`, `### Notes` e liste puntate.
- Evita di mettere `v` dentro le parentesi della versione nel changelog (`[1.6.0]`, non `[v1.6.0]`).

Esempio concreto (verrà pubblicato nella release 1.6.1):
```md
## [1.6.1] - 2026-04-24

### Changed
- Aggiornata la pipeline di rilascio con creazione automatica della GitHub Release.
- Migliorati i riferimenti documentali al repository GitHub.

### Fixed
- Corretto un caso limite nella gestione del tag versione già esistente.

### Notes
- Nessun impatto sul comportamento dei template generati per gli utenti finali.
```

Nota pratica:
- Se la sezione della versione non viene trovata o risulta vuota, la release viene creata con un messaggio di fallback.

## CF7 form object
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

