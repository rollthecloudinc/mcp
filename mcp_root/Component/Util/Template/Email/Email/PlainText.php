<?php 
printf(
	"Name: %s\nCompany: %s\nEmail: %s\nPhone: %s\nWebsite: %s\nMessage:\n%s"
	,wordwrap($values['name'],70)
	,wordwrap(strlen($values['company']) == 0?'--':$values['company'],70)
	,wordwrap($values['email'],70)
	,wordwrap(strlen($values['phone']) == 0?'--':$values['phone'],70)
	,wordwrap(strlen($values['website'])==0?'--':$values['website'],70)
	,wordwrap($values['message'],70)
);
?>