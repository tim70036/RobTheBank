<?php
// Server time
// Request: GET /time

// Response: Numeric unix time without milliseconds.

// Example: 1445324591

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	echo json_encode(time());
}

?>