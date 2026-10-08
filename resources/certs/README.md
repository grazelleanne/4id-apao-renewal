# HTTPS trust bundle

`cacert.pem` is the curl CA bundle extracted from Mozilla's trusted certificates:
https://curl.se/docs/caextract.html

Downloaded from https://curl.se/ca/cacert.pem. Keep it updated from this official source.

Brevo HTTPS requests use this bundle so Windows PHP does not depend on a missing php.ini CA path. Certificate verification remains enabled. Set `BREVO_CA_BUNDLE` to use another maintained trust bundle.
