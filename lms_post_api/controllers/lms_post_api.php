<?php
class Lms_post_api extends Lms_Api_Controller
{
# Private Data
	private $mod_cms_vars = array();
	private $post;
	private $mdv_db;
	private $json_msg_0;
	private $json_msg_1;
	private $json_msg_2;
	
# Private Methods
	
	
# Public Methods

	// Constructor
	public function __construct()
	{
		// Inherit from parents
		parent::__construct();
		
		// Save $_POST
		$this->post = $_POST;
		
		// Connecto MDV DB
		$this->mdv_db = $this->load->database( $this->config->item( 'mdvdb_creds' ), TRUE );
		
		// JSON messages
		$this->json_msg_0 = array( 'status' => 0, 'msg' => "MySQL connection couldn't be established.", 'alert' => "Ha%20ocurrido%20un%20error.%20Por%20favor%20intente%20de%20nuevo%20m%E1s%20tarde." );
		$this->json_msg_1 = array( 'status' => 1, 'msg' => "Missing required fields.", 'alert' => "Le%20faltan%20campos%20obligatorios.%20Por%20favor%20ll%E9nelos%20y%20vuelve%20a%20enviarlo." );
		$this->json_msg_2 = array( 'status' => 2, 'msg' => "Lead has been saved.", 'alert' => "Gracias%20por%20su%20inter%E9s.%20Uno%20de%20nuestros%20representantes%20se%20pondr%E1%20en%20contacto%20con%20usted%20lo%20m%E1s%20antes%20posible." );
		
		// Fetch CMS vars (needed)
		$this->mod_cms_vars['crm_type'] = parseStr( '{pyro:variables:crm_type}' );
		$this->mod_cms_vars['crm_email'] = parseStr( '{pyro:variables:crm_email}' );
			if( strpos( $this->mod_cms_vars['crm_email'], "|" ) )
			{
				$this->mod_cms_vars['crm_email'] = processArrayVar( $this->mod_cms_vars['crm_email'] );
			}
		$this->mod_cms_vars['crm_type_exclusion'] = explode( ',', parseStr( '{pyro:variables:crm_type_exclusion}' ) );
	}
	
	// Index method
	public function index()
	{
		echo "No $_POST vars present.";
	}
	
	// Newsletter Subscriber Form
	public function newsletter()
	{
		// Validate two fields
		$email = $this->_postItem( 'email' );
		$client_id = $this->_postItem( 'cid' );
		if( $client_id && ( $email != false && strpos( $email, "@" ) != false ) )
		{
			// loop and send to each branch
			$success = array();
			foreach( explode( ",", $client_id ) as $cid )
			{
				// create sql
				$sql = "INSERT INTO `website_email_capture` ( `CLIENT_ID`, `EMAIL`, `DATE_SUBSCRIBED` ) VALUES ( '".$cid."', '".$email."' ,'".date( 'Y-m-d H:i:s' )."')";
				
				// process insert query
				array_push( $success, $this->mdv_db->query( $sql ) );
			}
			
			// return
			if( !in_array( false, $success ) )
			{
				echo json_encode( $this->json_msg_2 );
			}
			else
			{
				echo json_encode( $this->json_msg_0 );
			}
		}
		else
			echo json_encode( $this->json_msg_1 );
			
	}
	
	// Process Reservation Form
	public function reservation()
	{
		// main fields required
		$main_fields_required = array( 'CLIENT_ID', 'TYPE', 'CONTACT_NAME', 'CONTACT_TELEPHONE' );
		
		// additional fields required
		$add_fields = array( "veh_id", "veh_vin", "vehicle", "veh_price" );
		
		// call post template
		$this->_postTemplate( 'contact', $main_fields_required, $add_fields );
	}
	
	public function part()
	{
		// main fields required
		$main_fields_required = array( 'CLIENT_ID', 'TYPE', 'CONTACT_NAME', 'CONTACT_TELEPHONE', 'CONTACT_EMAIL' );
		
		// additional fields required
		$add_fields = array( array( 'year', true ), array( 'make', true ), array( 'model', true ), 'trim', 'parts_for', 'urgency', 'description', 'series_num', 'dealer' );
		
		// call post template
		$this->_postTemplate( 'parts', $main_fields_required, $add_fields );
	}
	
	public function service()
	{
		// main fields required
		$main_fields_required = array( 'CLIENT_ID', 'TYPE', 'CONTACT_NAME', 'CONTACT_TELEPHONE', 'CONTACT_EMAIL' );
		
		// additional fields required
		$add_fields = array( array( 'preferred_date', true ), 'preferred_time', 'service_type', 'year', array( 'make', true ), 'model', array( 'mileage', true ), 'dealer', 'location' );
		
		// call post template
		$this->_postTemplate( 'service_apt', $main_fields_required, $add_fields );
	}
	
	public function trade()
	{
		// main fields required
		$main_fields_required = array( 'CLIENT_ID', 'TYPE', 'CONTACT_NAME', 'CONTACT_TELEPHONE' );
		
		// additional fields required
		$add_fields = array( array( 'make', true ), array( 'model', true ), array( 'year', true ), array( 'mileage', true ), array( 'vin', true ), array( 'condition', true ), 'color_exterior', 'color_interior', 'dealer' );
		
		// call post template
		$this->_postTemplate( 'trade_in', $main_fields_required, $add_fields );
	}
	
	public function finance()
	{
		// main fields required
		$main_fields_required = array( 'CLIENT_ID', 'TYPE', 'CONTACT_NAME', 'CONTACT_TELEPHONE' );
		
		// additional fields required
		$add_fields = array( 'month', 'day', 'year', array( 'civil_status', true ), array( 'address', true ), 'neighborhood', array( 'city', true ), array( 'zip', true ), array( 'employment_status', true ), array( 'monthly_income', true ), 'housing_status', 'housing_payment', 'dealer', array( 'vehicle_interested', true ) );
		
		// call post template
		$this->_postTemplate( 'credit', $main_fields_required, $add_fields );
	}
	
	public function contact()
	{
		// main fields required
		$main_fields_required = array( 'CLIENT_ID', 'TYPE', 'CONTACT_NAME', 'CONTACT_TELEPHONE' );
		
		// additional fields required
		$add_fields = array( 'subject', 'message' );
		
		// call post template
		$this->_postTemplate( 'contact', $main_fields_required, $add_fields );
	}
	
	public function promo()
	{
		// extract promo email
		$this->mod_cms_vars['promo_email'] = parseStr( '{pyro:variables:promo_email}' );
		
		// only proceed if email is present
		if( $this->mod_cms_vars['promo_email'] && $this->input->post( 'subject' ) && $this->input->post( 'message' ) )
		{
			// Load Email Library
			$this->load->library('email');
			
			// Configure email settings
			$this->email->initialize( array( 'mailtype' => 'html' ) );
			
			// Configure email reciepients
			$this->email->from( 'leads@midealervirtual.com', 'MiDealerVirtual.com' );
			$this->email->to( $this->mod_cms_vars['promo_email'] );
			
			// Configure email content
			$this->email->subject( urldecode( $this->input->post( 'subject' ) ) );
			$this->email->message( $this->input->post( 'message' ) );
			
			// Send email
			if( $this->email->send() )
				echo json_encode( array( 'status' => 2 ) );
			else
				echo json_encode( array( 'status' => 0 ) );
		}
		else
			echo json_encode( array( 'status' => 1 ) );
	}
	
/* PRIVATE METHODS */

	private function _postTemplate( $type, $main_fields, $add_fields )
	{
		// arrays required
		$unique_data = array();
		$required_additional = array();
		
		// fetch common data
		$db_data = $this->_dbDataTemplate( $type );
		
		// fetch values
		foreach( $add_fields as $f )
		{
			// variables used
			$req = false;
			$temp = NULL;
			
			// check if field is required
			if( is_array( $f ) )
			{
				$req = true;	// set flag
				$f = $f[0];		// exract from array
			}
			
			// save value in a temp var
			$temp = $this->_postItem( $f );
			
			// save value, if present
			if( $temp != false && /* NEW */ $temp != 'mdvcms_opt_hide' )
			{
				// add to unique data
				$unique_data[$f] = $temp;
				
				// and if its required, add to required array
				if( $req )
				{
					array_push( $required_additional, $f );
				}
			}
		}
		
		// check for DOB
		if( array_key_exists( "month", $unique_data ) && array_key_exists( "day", $unique_data ) && array_key_exists( "year", $unique_data ) )
		{
			// create DOB and delete M D Y from "add_fields"
			$unique_data['dob'] = $unique_data['month']."/".$unique_data['day']."/".$unique_data['year'];
			
			// make it required
			array_push( $required_additional, "dob" );
			
			// delete M D Y from "unique_data"
			unset( $unique_data['month'], $unique_data['day'], $unique_data['year'] );
		}
		
		// add unique data
		if( count( $unique_data ) > 0 )
			$db_data['DATA'] = $unique_data;
			
		// ensure all rules are met
		$validate_all = NULL;
		$validate_main = $this->_validateData( $db_data, $main_fields );
		if( count( $required_additional ) > 0 )
		{
			$validate_additional = $this->_validateData( $unique_data, $required_additional );
			$validate_all = $validate_main && $validate_additional;
		}
		else
			$validate_all = $validate_main;
			
		// if validate passes, insert to DB
		if( $validate_all )
		{
			echo json_encode( $this->_insertToDB( $db_data ) );
		}
		else
			echo json_encode( $this->json_msg_1 );
	}
	
	private function _postItem( $key )
	{
		return ( ( isset( $this->post[$key] ) ) ? $this->post[$key] : false );
	}
	
	private function _dbDataTemplate( $lead_type )
	{
		// detect location
		$client_id = $this->_postItem( 'cid' );
		/*if( strpos( $client_id, "-" ) !== FALSE )
		{
			// extract
			$client_id = explode( "-", $this->_postItem( 'cid' ) );
			
			// save location
			$_POST['extra_location'] = $client_id[1];
			$client_id = $client_id[0];
		}*/
		
		// prepare temp
		$db_data_temp = array( 'CLIENT_ID' => $client_id,
							   'TYPE' => $lead_type,
							   // Removed  'SOURCE' => 'online',
							   'CONTACT_NAME' => trim( $this->_postItem( 'fname' ).' '.$this->_postItem( 'lname' ) ),
							   'ASIGNED_TO' => '0',
							   'STATUS' => 'Nuevo',
							   'HIGHLIGHTED' => '0',
							   'DATE_CONTACTED' => date( 'Y-m-d H:i:s' ) );
		
		// add email & telephone (if present)
		$telephone = $this->post['telephone'];
		$email = $this->post['email'];
		if( $telephone != false )
			$db_data_temp['CONTACT_TELEPHONE'] = $telephone;
		if( $email != false )
			$db_data_temp['CONTACT_EMAIL'] = $email;
			
		// return template
		return $db_data_temp;
	}
	
	private function _validateData( $data, $rules )
	{
		// is_valid flag
		$is_valid = array();
		
		// loop thru all the rules and verify they are met
		foreach( $rules as $r ){
			$current_valid = ( ( isset( $data[$r] ) && ( $data[$r] == false || $data[$r] == NULL || $data[$r] == "" ) ) ? false : true );
			array_push( $is_valid, $current_valid );
		}
		
		// return answer
		return !in_array( false, $is_valid );
	}
	
	private function _insertToDB( $data_to_push )
	{
		// clead data for json encode
		/*foreach( $data_to_push['DATA'] as $k => $v )
		{
			//$data_to_push['DATA'][$k] = htmlspecialchars( $v );	// remove "
			//$data_to_push['DATA'][$k] = str_replace( "'", "", $v );	// remove '
		}*/
		
		// determine if vehicle reservation is occuring
		$send_to_crm = ( $this->mod_cms_vars['crm_type'] !== FALSE ) && ( $this->mod_cms_vars['crm_email'] !== FALSE );
		if( $send_to_crm && isset( $data_to_push['DATA']['veh_id'] ) )
		{
			$veh_obj = $this->mdv_db->query( "SELECT * FROM `vehicles_available_to_viewer` WHERE `VEH_ID` = '".$data_to_push['DATA']['veh_id']."'" );
			$veh_obj = ( $veh_obj ) ? $veh_obj->row() : $veh_obj->result() ;
			$veh_price = ( isset( $data_to_push['DATA']['veh_price'] ) ) ? $data_to_push['DATA']['veh_price'] : "Llame hoy";
		}
		
		// encode json data
		$json_encode = array();
		$patterns = array( '/"/', "/'/" );
		$replacements = array( '', '' );
		foreach( $data_to_push['DATA'] as $k => $v )
		{
			array_push( $json_encode, '"'.preg_replace( $patterns, $replacements, $k ).'":"'.$this->_removeNewLine( preg_replace( $patterns, $replacements, $v ) ).'"' );
		}
		$data_to_push['DATA'] = '{'.implode( ",", $json_encode ).'}';
		
		// clean up rest of values
		foreach( $data_to_push as $k => $v )
		{
			if( $k != "DATA" )
			{
				$data_to_push[$k] = preg_replace( $patterns, $replacements, $v );
				// $data_to_push[$k] = htmlspecialchars( $v );	// remove "
				// $data_to_push[$k] = str_replace( "'", "", $v );	// remove '	
			}
		}
		
		// save keys and values
		$keys = "";
		$values = "";
		
		// prepare query
		foreach( $data_to_push as $k => $v )
		{
			$keys .= "`".$k."`, ";
			$values .= "'".$v."', ";
		}
		$sql = "INSERT INTO `leads_entries` ( ".rtrim( $keys, ", " )." ) VALUES ( ".rtrim( $values, ", " )." )";
		$sql = stripslashes( $sql );
		
		// process insert query
		if( $this->mdv_db->query( $sql ) != false )
		{
			// determine if we need to send lead to any crm, and which type of lead
			if( $send_to_crm )
			{
				// set 1st and 3rd parameters
				if( isset( $veh_obj ) )
				{
					$param_1 = $veh_obj;
					$param_2 = array( 'FNAME' => $this->_postItem( 'fname' ),
									  'LNAME' => $this->_postItem( 'lname' ),
									  'PRICE' => $veh_price );
					$param_3 = 'reservation';
				}
				else
				{
					$param_1 = NULL;
					$param_2 = array( 'CONTACT_NAME' => $this->_postItem( 'fname' ).' '.$this->_postItem( 'lname' ),
									  'SUBJECT' => $this->_postItem( 'subject' ),
									  'MESSAGE' => $this->_postItem( 'message' ) );
					$param_3 = $data_to_push['TYPE'];
				}
				
				// finish param_2
				$param_2['EMAIL'] = $this->_postItem( 'email' );
				$param_2['TELEPHONE'] = $this->_postItem( 'telephone' );
				$param_2['TYPE'] = $data_to_push['TYPE'];
				
				// send to CRM
				$this->_sendToCRM( $param_1, $param_2, $param_3 );
			}
			
			// return json message confirming success
			$this->json_msg_2['id'] = $this->mdv_db->insert_id();
			return $this->json_msg_2;
		}
		else
		{
			return $this->json_msg_0;
		}
	}
	
	public function testMultEmail()
	{
		if( is_array( $this->mod_cms_vars['crm_email'] ) )
		{
			print_r( $this->mod_cms_vars['crm_email'] );
			$top = array_pop( $this->mod_cms_vars['crm_email'] );
			echo "<Br />";
			echo $top;
		}
		else
			echo $this->mod_cms_vars['crm_email'];	
	}
	
	private function _sendToCRM( $veh, $db_data, $type = 'reservation' )
	{
		// Detect what crm type is being used
		$is_excluded = in_array( $db_data['TYPE'], $this->mod_cms_vars['crm_type_exclusion'] );
		if( $this->mod_cms_vars['crm_type'] == "dealersocket" && !$is_excluded )
		{
			// Prepare correct XML feed
			if( $type == 'reservation' )
			{
				// Determine recipient
				$recipient = ( is_array( $this->mod_cms_vars['crm_email'] ) ) ? $this->mod_cms_vars['crm_email'][$veh-CLIENT_ID] : $this->mod_cms_vars['crm_email'] ;
				
				// Prepare XML lead
				$format =
		'<?xml version="1.0" ?>
		<?adf version="1.0" ?>
		<adf>
			<prospect>
				<requestdate>'.date( 'Y-m-d g:i A' ).'</requestdate>
				<vehicle interest="buy" status="'.$veh->CONDITION.'">
					<year>'.$veh->YEAR.'</year>
					<make>'.$veh->MAKE.'</make>
					<model>'.$veh->MODEL.'</model>
					<trim>'.$veh->TRIM.'</trim>
					<vin>'.$veh->VIN.'</vin>
					<stock></stock>
				</vehicle>
				<customer>
					<contact>
						<name part="first">'.$db_data['FNAME'].'</name>
						<name part="last">'.$db_data['LNAME'].'</name>
						<email>'.$db_data['EMAIL'].'</email>
						<phone type="voice" time="day">'.$db_data['TELEPHONE'].'</phone>
						<phone type="cellphone"></phone>
					</contact>
					<comments>Vehicle\'s Internet Price: '.$db_data['PRICE'].' </comments>
				</customer>
				<vendor>
					<contact>
						<service>Mi Dealer Virtual</service>
						<url>http://www.MiDealerVirtual.com/</url>
					</contact>
				</vendor>
				<provider>
					<name>Mi Dealer Virtual</name>
				</provider>
			</prospect>
		</adf>';
			}
			else
			{
				// Determine recipient
				$recipient = ( is_array( $this->mod_cms_vars['crm_email'] ) ) ? array_pop( $this->mod_cms_vars['crm_email'] ) : $this->mod_cms_vars['crm_email'] ;
				
				// Split `CONTACT_NAME`
				$db_data['CONTACT_NAME'] = explode( " ", $db_data['CONTACT_NAME'], 2 );
				
				// Prepare XML lead
				$format =
		'<?xml version="1.0" ?>
		<?adf version="1.0" ?>
		<adf>
			<prospect>
				<requestdate>'.date( 'Y-m-d g:i A' ).'</requestdate>
				<vehicle interest="" status="">
					<year></year>
					<make></make>
					<model></model>
					<trim></trim>
					<vin></vin>
					<stock></stock>
				</vehicle>
				<customer>
					<contact>
						<name part="first">'.$db_data['CONTACT_NAME'][0].'</name>
						<name part="last">'.$db_data['CONTACT_NAME'][1].'</name>
						<email>'.$db_data['EMAIL'].'</email>
						<phone type="voice" time="day">'.$db_data['TELEPHONE'].'</phone>
						<phone type="cellphone"></phone>
					</contact>
					<comments>'.$db_data['SUBJECT'].'
					
					'.$db_data['MESSAGE'].'</comments>
				</customer>
				<vendor>
					<contact>
						<service>Mi Dealer Virtual</service>
						<url>http://www.MiDealerVirtual.com/</url>
					</contact>
				</vendor>
				<provider>
					<name>Mi Dealer Virtual</name>
				</provider>
			</prospect>
		</adf>';
			}
	
			// Load Email Library
			$this->load->library('email');
			
			// Configure email settings
			$this->email->initialize( array( 'mailtype' => 'text' ) );
			
			// Configure email reciepients
			$this->email->from( 'leads@midealervirtual.com', 'MiDealerVirtual.com' );
			$this->email->to( $recipient );
			
			// Configure email content
			$this->email->subject( 'Leads de Internet' );
			$this->email->message( $format );
			
			// Send email
			$this->email->send();
		}
		elseif( $this->mod_cms_vars['crm_type'] == "email_leads" )
		{
			// Prepare correct message
			if( $type == 'reservation' )
			{
				// Prepare email
				$vehicle = $veh->YEAR." ".$veh->MAKE." ".$veh->MODEL.( ( $veh->TRIM != '' ) ? " ".$veh->TRIM." " : " " ).$veh->COLOR;
				$subject = "Nuevo Lead: Reservación de Vehículo";
				$message =
				"<strong>Nombre:</strong> ".$db_data['FNAME']." ".$db_data['LNAME']."<br />".
				"<strong>Tel&eacute;fono:</strong> ".$db_data['TELEPHONE']."<br />".
				"<strong>Email:</strong> ".$db_data['EMAIL']."<br />".
				"<strong>Fecha:</strong> ".date( 'd/m/Y' )."<br />".
				"<strong>Veh&iacute;culo:</strong> ".$vehicle."<br />".
				"<strong>VIN:</strong> ".$veh->VIN."<br />".
				"<strong>Precio:</strong> ".$db_data['PRICE']."<br /><br />".
				"<em><strong>Mi Dealer Virtual (c) ".date( 'Y' )."</strong></em>";
			}
			elseif( $type == 'contact' )
			{
				// Prepare email
				$subject = 'Contactar Cliente: '.$db_data['CONTACT_NAME'];
				$message =
				"<strong>Nombre:</strong> ".$db_data['CONTACT_NAME']."<br />".
				"<strong>Tel&eacute;fono:</strong> ".$db_data['TELEPHONE']."<br />".
				"<strong>Email:</strong> ".$db_data['EMAIL']."<br />".
				"<strong>Fecha:</strong> ".date( 'd/m/Y' )."<br />".
				"<strong>Asunto:</strong> ".$db_data['SUBJECT']."<br />".
				"<strong>Mensaje:</strong> ".$db_data['MESSAGE']."<br /><br />".
				"<em><strong>Mi Dealer Virtual (c) ".date( 'Y' )."</strong></em>";
			}
			elseif( $type == 'service_apt' )
			{
				// Prepare email
				$subject = 'Cita de Servicio: '.$db_data['CONTACT_NAME'];
				$message =
				"<h2>Información de Contacto</h2>".
				"<strong>Nombre:</strong> ".$db_data['CONTACT_NAME']."<br />".
				"<strong>Tel&eacute;fono:</strong> ".$db_data['TELEPHONE']."<br />".
				"<strong>Email:</strong> ".$db_data['EMAIL']."<br />".
				"<h2>Información de Cita</h2>".
				"<strong>Fecha preferida:</strong> ".( ( $this->_postItem( 'preferred_date' ) ) ? $this->_postItem( 'preferred_date' ) : '' )."<br />".
				"<strong>Hora preferida:</strong> ".( ( $this->_postItem( 'preferred_time' ) ) ? $this->_postItem( 'preferred_time' ) : '' )."<br />".
				"<strong>Tipo de servicio:</strong> ".( ( $this->_postItem( 'service_type' ) ) ? $this->_postItem( 'service_type' ) : '' )."<br />".
				"<h2>Información del Vehículo</h2>".
				"<strong>Marca:</strong> ".( ( $this->_postItem( 'make' ) ) ? $this->_postItem( 'make' ) : '' )."<br />".
				"<strong>Modelo:</strong> ".( ( $this->_postItem( 'model' ) ) ? $this->_postItem( 'model' ) : '' )."<br />".
				"<strong>Año:</strong> ".( ( $this->_postItem( 'year' ) ) ? $this->_postItem( 'year' ) : '' )."<br />".
				"<strong>Millaje:</strong> ".( ( $this->_postItem( 'mileage' ) ) ? $this->_postItem( 'mileage' ) : '' )."<br /><br />".
				"<em><strong>Mi Dealer Virtual (c) ".date( 'Y' )."</strong></em>";
			}
			elseif( $type == 'parts' )
			{
				// Prepare email
				$subject = 'Solicitud de Pieza: '.$db_data['CONTACT_NAME'];
				$message =
				"<h2>Información de Contacto</h2>".
				"<strong>Nombre:</strong> ".$db_data['CONTACT_NAME']."<br />".
				"<strong>Tel&eacute;fono:</strong> ".$db_data['TELEPHONE']."<br />".
				"<strong>Email:</strong> ".$db_data['EMAIL']."<br />".
				"<h2>Información de Vehículo</h2>".
				"<strong>Marca:</strong> ".( ( $this->_postItem( 'make' ) ) ? $this->_postItem( 'make' ) : '' )."<br />".
				"<strong>Modelo:</strong> ".( ( $this->_postItem( 'model' ) ) ? $this->_postItem( 'model' ) : '' )."<br />".
				"<strong>Año:</strong> ".( ( $this->_postItem( 'year' ) ) ? $this->_postItem( 'year' ) : '' )."<br />".
				"<strong>Ajuste:</strong> ".( ( $this->_postItem( 'trim' ) ) ? $this->_postItem( 'trim' ) : '' )."<br />".
				"<h2>Información de Pieza</h2>".
				"<strong>Pieza para:</strong> ".( ( $this->_postItem( 'parts_for' ) ) ? $this->_postItem( 'parts_for' ) : '' )."<br />".
				"<strong>Urgencia:</strong> ".( ( $this->_postItem( 'urgency' ) ) ? $this->_postItem( 'urgency' ) : '' )."<br />".
				"<strong>Descripción:</strong><br />".( ( $this->_postItem( 'description' ) ) ? $this->_postItem( 'description' ) : '' )."<br /><br />".
				"<em><strong>Mi Dealer Virtual (c) ".date( 'Y' )."</strong></em>";
			}
			elseif( $type == 'credit' )
			{
				// Prepare birthday
				$m = $this->_postItem( 'month' );
				$d = $this->_postItem( 'day' );
				$y = $this->_postItem( 'year' );
				$dob = $m."/".$d."/".$y;
				$dob = ( $dob != "//" ) ? $dob : '';
				
				// Prepare email
				$subject = 'Solicitud de Financiamiento: '.$db_data['CONTACT_NAME'];
				$message =
				"<h2>Información de Solicitante</h2>".
				"<strong>Nombre:</strong> ".$db_data['CONTACT_NAME']."<br />".
				"<strong>Tel&eacute;fono:</strong> ".$db_data['TELEPHONE']."<br />".
				"<strong>Email:</strong> ".$db_data['EMAIL']."<br />".
				"<strong>Fecha de Nacimiento:</strong> ".$dob."<br />".
				"<strong>Estado Civil:</strong> ".$this->_postItem( 'civil_status' )."<br />".
				"<h2>Información de Dirección</h2>".
				"<strong>Dirección:</strong> ".( ( $this->_postItem( 'address' ) ) ? $this->_postItem( 'address' ) : '' )."<br />".
				"<strong>Urbanización:</strong> ".( ( $this->_postItem( 'neighborhood' ) ) ? $this->_postItem( 'neighborhood' ) : '' )."<br />".
				"<strong>Cuidad:</strong> ".( ( $this->_postItem( 'city' ) ) ? $this->_postItem( 'city' ) : '' )."<br />".
				"<strong>Código Postal:</strong> ".( ( $this->_postItem( 'zip' ) ) ? $this->_postItem( 'zip' ) : '' )."<br />".
				"<h2>Información Financiera</h2>".
				"<strong>Tipo de Empleo:</strong> ".( ( $this->_postItem( 'employment_status' ) ) ? $this->_postItem( 'employment_status' ) : '' )."<br />".
				"<strong>Ingreso Mensual:</strong> ".( ( $this->_postItem( 'monthly_income' ) ) ? $this->_postItem( 'monthly_income' ) : '' )."<br />".
				"<strong>Residencia:</strong>".( ( $this->_postItem( 'housing_status' ) ) ? $this->_postItem( 'housing_status' ) : '' )."<br />".
				"<strong>Pago de Residencia:</strong>".( ( $this->_postItem( 'housing_payment' ) ) ? $this->_postItem( 'housing_payment' ) : '' )."<br />".
				"<h2>Vehículo de Interés</h2>".
				"<strong>Vehículo que Busca:</strong><br />".( ( $this->_postItem( 'vehicle_interested' ) ) ? $this->_postItem( 'vehicle_interested' ) : '' )."<br /><br />".
				"<em><strong>Mi Dealer Virtual (c) ".date( 'Y' )."</strong></em>";
			}
			elseif( $type == 'trade_in' )
			{
				// Prepare email
				$subject = 'Cotización de Trade-in: '.$db_data['CONTACT_NAME'];
				$message =
				"<h2>Información del Cliente</h2>".
				"<strong>Nombre:</strong> ".$db_data['CONTACT_NAME']."<br />".
				"<strong>Tel&eacute;fono:</strong> ".$db_data['TELEPHONE']."<br />".
				"<strong>Email:</strong> ".$db_data['EMAIL']."<br />".
				"<h2>Información de Vehículo</h2>".
				"<strong>Marca:</strong> ".( ( $this->_postItem( 'make' ) ) ? $this->_postItem( 'make' ) : '' )."<br />".
				"<strong>Modelo:</strong> ".( ( $this->_postItem( 'model' ) ) ? $this->_postItem( 'model' ) : '' )."<br />".
				"<strong>Año:</strong> ".( ( $this->_postItem( 'year' ) ) ? $this->_postItem( 'year' ) : '' )."<br />".
				"<strong>Millaje:</strong> ".( ( $this->_postItem( 'mileage' ) ) ? $this->_postItem( 'mileage' ) : '' )."<br />".
				"<strong>Número de Chassis:</strong> ".( ( $this->_postItem( 'vin' ) ) ? $this->_postItem( 'vin' ) : '' )."<br />".
				"<strong>Condición:</strong> ".( ( $this->_postItem( 'condition' ) ) ? $this->_postItem( 'condition' ) : '' )."<br />".
				"<strong>Color Exterior:</strong> ".( ( $this->_postItem( 'color_exterior' ) ) ? $this->_postItem( 'color_exterior' ) : '' )."<br />".
				"<strong>Color Interior:</strong> ".( ( $this->_postItem( 'color_interior' ) ) ? $this->_postItem( 'color_interior' ) : '' )."<br /><br />".
				"<em><strong>Mi Dealer Virtual (c) ".date( 'Y' )."</strong></em>";
			}
			
			// Load Email Library
			$this->load->library('email');
			
			// Configure email settings
			$this->email->initialize( array( 'mailtype' => 'html' ) );
			
			// Configure email reciepients
			$this->email->from( 'leads@midealervirtual.com', 'MiDealerVirtual.com' );
			$this->email->to( $this->mod_cms_vars['crm_email'] );
			
			// Configure email content
			$this->email->subject( $subject );
			$this->email->message( $message );
			
			// Send email
			$this->email->send();
		}
	}
	
	// private function to remove new line chars
	private function _removeNewLine( $str )
	{
		// Order of replacement
		$order = array( "\r\n", "\n", "\r" );
		$replace = '<br />';
		
		// Replace
		return str_replace( $order, $replace, $str );
	}
}
?>