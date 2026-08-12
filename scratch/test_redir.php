<?php
$url = 'http://localhost/Ascendance/membership-checkout/?level=1';
$headers = get_headers($url, 1);
print_r($headers);
