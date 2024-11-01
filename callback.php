<?php
/* Handle the OAuth Callback */

// if there is an error, close the modal
if (isset($_GET['error'])) {
  ?>
  	<script>
    window.parent.modal.close();
  	</script>
	<?php
} else {
  // get the authorization code
  $authorization_code = sanitize_text_field($_GET['code']);
  if (!$authorization_code) 
    { ?>
    <script>
      window.parent.modal.close();
    </script>
    <?php
    }
  // redirect to the checkout page with the code
  ?>
    <script>
      window.parent.document.location.href = window.parent.document.location.href + "?auth_code=<?php echo esc_textarea( $authorization_code ); ?>";
    </script>
  <?php
}
?>
