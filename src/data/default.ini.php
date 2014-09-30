<?php exit; ?>
[database]
; for further information please refere to:
; http://redbeanphp.com/connection
; Data Source Name
dsn = "mysql:host=localhost;dbname=thumbnailer"
; username and password to access the database
username = "root"
password = ""

[dir]
; webserver root directory
; a relative path from the installation directory may be given
webserver = ""
; root directory of your application inside of `document-root`
; => where do you want Thumbnailer to start working
;
; `app-root` and `document-root` must be concatenate able in PHP like so:
;   $document-root . $app-root
;
; if your `docuent-root` equals your application directory
; then just leave `app-root` blank.
app = ""

