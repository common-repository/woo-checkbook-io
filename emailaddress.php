<?php

/* Starts the session */
function checkbook_io_start_session(){
  if (!session_id())
    session_start();
}

checkbook_io_start_session();

/* Handle custom email address and name input */
if (isset($_POST['custom_email_address']) && isset($_POST['custom_name'])) {
  $email = sanitize_email( $_POST['custom_email_address'] );
  $custom_name = sanitize_text_field( $_POST['custom_name'] );
  
  // Validate email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    var_dump(http_response_code(422));
    echo "Email validation error";
    die();
  }

  // Validate username
  if (!ctype_alnum($custom_name )) {
    var_dump(http_response_code(422));
    echo "Username validation error";
    die();
  }

  $_SESSION['custom_email_address'] = sanitize_text_field( $email );
  $_SESSION['custom_name'] = sanitize_text_field( $custom_name );
  echo esc_textarea( "Email: " . $email . " Name: " . $custom_name ); 
} else {
  var_dump(http_response_code(404));
  echo "Email or username were not provided";
}

die();
