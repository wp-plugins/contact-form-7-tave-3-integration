<?php
/*
Plugin Name: Contact Form 7 T&aacute;ve 3 Integration
Plugin URI: http://rowellphoto.com/tave-contact-form-integration
Description: Submit to Tave from Contact Form 7 (this plugin requires <a href="http://contactform7.com/">Contact Form 7</a>) activate, use the same input field names (ex: FirstName, LastName) in your contact form, set your studio secret key in the options. Visit <a href="http://tave.com">T&aacute;ve.com</a> for the best studio management software available.
Author: Ryan Rowell
Version: 2014.02.24
Author URI: http://www.rowellphoto.com
*/

function tave_getdata($data, $name) {
  return isset($data[$name]) ? $data[$name] : null;
}

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there! why call me directly when its more fun to use me as prescribed";
	exit;
}

function cf7tave_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/cf7-tave.php' ) ) {
		$links[] = '<a href="admin.php?page=cf7-tave/cf7-tave.php">'.__('Settings').'</a>';
	}

	return $links;
}

add_filter( 'plugin_action_links', 'cf7tave_plugin_action_links', 10, 2 );


/**
 * Send the message to Táve to create the new lead
 * @param  object $contactForm
 * @return bool
 */
function tave_send($contactForm) {
  $ignoreFields = array('_wpnonce'); // default fields
  $ignoreFields = array_merge($ignoreFields, explode(',', get_option('tave-ignore-fields')));
  //ini_set('html_errors','on'); // Debugging
  //ini_set('display_errors','on'); // Debugging
  //ini_set('error_reporting',E_ALL); // Debugging

  $post = $contactForm->posted_data;
  $apiKey = get_option('tave-api-key');
  $studioID = get_option('tave-studio-id');
  if( empty($apiKey) || empty($studioID) ) {
    die("API key or StudioID is invalid.  Please let the site owner know about this error, and that its one of their settings in the Tave settings for their contact form.");
  }

  $url = "https://my.tave.com/WebService/CreateLead/{$studioID}";
  $data = array();
  $convertFunction = function_exists('mb_convert_encoding');
  foreach ($post as $key => $value) {
    if (in_array($key, $ignoreFields) || strpos($key, '_wpcf7') === 0) {
      continue;
    }

    if ($convertFunction) {
      $data[$key] = mb_convert_encoding(trim($value), 'HTML-ENTITIES', 'UTF-8');
    }
    else {
      $data[$key] = trim($value);
    }
  }

  if( ! array_key_exists('FirstName', $data) || ! array_key_exists('JobType', $data) ) {
    return;
  }

  $data["SecretKey"] = $apiKey;

  /* send this data to Tave via the API */
  $curlHandle = curl_init();
  curl_setopt_array($curlHandle, array(
    CURLOPT_URL => $url,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 20
    ));


  /* get the response from the Tave API */
  $response = trim(curl_exec($curlHandle));
  $httpcode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

  //var_dump($response); // Debugging
  //var_dump($data);

  /* the HTTP code will be 200 if there is a success */
  if (curl_errno($curlHandle) == 0 && $httpcode == 200 && $response == 'OK') {
	  curl_close($curlHandle);
	  return true;
	  
  }
  else {
  	//var_dump($curlHandle);
  	//var_dump($httpcode);
  	//var_dump($response);
    //error_log("Tave Error: ".curl_errno($curlHandle).": $httpcode $response"); // Debugging
    
    curl_close($curlHandle);
	return false;
  }

  //curl_close($curlHandle);
}

add_action('wpcf7_before_send_mail', 'tave_send', 1);


// create custom plugin settings menu inside wordpress admin
add_action('admin_menu', 'tave_wpcf7_create_menu');

function tave_wpcf7_create_menu() {

  	//create new top-level menu in wordpress
	//add_menu_page('Tave Plugin Settings', 'Tave Settings', 'administrator', __FILE__, 'tave_wpcf7_settings_page',plugins_url('/images/icon.png', __FILE__));
	if (function_exists('wpcf7_admin_menu')) {
		add_submenu_page( 'wpcf7', __( 'T&aacute;ve Plugin Settings', 'wpcf7' ), __( 'T&aacute;ve Settings', 'wpcf7' ), 'administrator', __FILE__, 'tave_wpcf7_settings_page' ); 
	}
  //call register settings function for wordpress
  add_action( 'admin_init', 'tave_wpcf7_register_mysettings' );
}


function tave_wpcf7_register_mysettings() {
  //register our settings
  register_setting( 'tave_wpcf7-settings-group', 'tave-api-key' );
  register_setting( 'tave_wpcf7-settings-group', 'tave-studio-id' );
  register_setting( 'tave_wpcf7-settings-group', 'tave-ignore-fields' );

}

function tave_wpcf7_settings_page() {

?>

<div class="metabox-holder">
	<h2>Contact Form 7 T&aacute;ve Integration</h2>
	<div style="width:49%;float:left">
		<form method="post" action="options.php">
			<?php settings_fields( 'tave_wpcf7-settings-group' ); ?>
			<table class="form-table">
				<tr valign="top">
				<th scope="row">T&aacute;ve Secret Key:</th>
				<td><input type="text" name="tave-api-key" value="<?php echo get_option('tave-api-key'); ?>" size="80"/><br>Your secret key can be found in your T&aacute;ve dashboard in Settings>New Lead API. <a href="https://my.tave.com/Settings/NewLeadAPI">or here if your logged into T&aacute;ve.com</a></td>
				</tr>
				<tr valign="top">
				<th scope="row">T&aacute;ve Studio ID:</th>
				<td><input type="text" name="tave-studio-id" value="<?php echo get_option('tave-studio-id'); ?>" size="80"/><br>Your Studio ID can be found in your T&aacute;ve dashboard in Settings>New Lead API. <a href="https://my.tave.com/Settings/NewLeadAPI">or here if your logged into T&aacute;ve.com</a></td>
				</tr>
				<tr valign="top">
				<th scope="row">Exclude Input Field Names:</th>
				<td><input type="text" name="tave-ignore-fields" value="<?php echo get_option('tave-ignore-fields'); ?>" size="80"/><br>These are input fields you dont want to pass to T&aacute;ve. Perhaps you have form questions that you dont want in your T&aacute;ve database, you just need to enter their field names with a comma seperating them. ex: FirstName, MothersName........  </td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		<p>Plugin is the brainchild of <a href="http://rowellphoto.com/">Ryan Rowell</a>, and much thanks is given to Jason Pirkey of <a href="http://tave.com">T&aacute;ve</a> for helping interpret the API, Without him this plugin wouldn't work.</p>
	</div>
	<div style="width:49%;float:left">
		<div class="postbox-container">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><span>Quick examples to get your form started</span></h3>
				<div class="inside" style="padding:8px;overflow:auto">
				<p><strong>The JobType field is required.</strong> if you dont want the field in your form, you can simply add in a hidden field into the form that will just tell T&aacute;ve to always use a specific JobType. Here is an example:</p>
				<pre>&lt;input type="hidden" name="JobType" value="Wedding"&gt;</pre>
				<p>Form example to get you started (copy and paste into your contact form):</p>
				<pre style="padding:8px;">
&lt;p&gt;Your First Name (required)&lt;br /&gt;
[text* FirstName akismet:author tabindex:01]&lt;/p&gt;
&lt;p&gt;Your Last Name (required)&lt;br /&gt;
[text* LastName tabindex:02]&lt;/p&gt;
&lt;p&gt;Your Email (required)&lt;br /&gt;
[email* Email tabindex:03 akismet:author_email]&lt;/p&gt;
&lt;p&gt;Your Phone Number&lt;br /&gt;
[text MobilePhone tabindex:04]&lt;/p&gt;
&lt;p&gt;Your Event Date&lt;br /&gt;
[text EventDate tabindex:05]&lt;/p&gt;
&lt;p&gt;Service Type:&lt;br /&gt;
[select* JobType tabindex:06 &quot;Wedding&quot; &quot;Family Portrait&quot;]&lt;/p&gt;
&lt;p&gt;Referrer:&lt;br /&gt;
[select Source tabindex:07 &quot;Friend&quot; &quot;Another Vendor&quot; &quot;Facebook&quot;]&lt;/p&gt;
&lt;p&gt;Your Message:&lt;br /&gt;
[textarea* Message tabindex:08]&lt;/p&gt;
[submit tabindex:10 &quot;Send&quot;]
[response]
				</pre>
				<p>
				if you are using <a href="http://wordpress.org/extend/plugins/cf7-calendar/" rel="nofollow">Contact Form 7 Calendar</a> Plugin you can use [cf7cal EventDate tabindex:05] in your form instead of [text EventDate tabindex:05], it will help keep the date in a format that makes sense to the tave database and will make it easier for your site user to input the date. </p>
				<p>In the mail secion to send yourself (copy and paste):</p>
				<pre style="padding:8px;">
First Name: [FirstName] 
Last Name: [LastName]
Email: [Email]
Phone: [MobilePhone]
Found us here: [Source]
Event Date: [EventDate]
Type of Photography: [JobType]

Their Message:
[Message]
				</pre>
			</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>
