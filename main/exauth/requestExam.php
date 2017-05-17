<?php

require_once '../inc/global.inc.php';
//require_once '/var/www/vithoulkas/krumo/class.krumo.php';
api_block_anonymous_users();


Display :: display_header($nameTools);

$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$title = $_POST['title'];
$address = $_POST['address'];
$postcode = $_POST['postcode'];
$city = $_POST['city'];
$country = $_POST['country'];
$telephone = $_POST['telephone'];
$country_code = $_POST['country_code'];
$hours_from = $_POST['hours_from'];
$hours_to = $_POST['hours_to'];
$email = $_POST['email'];
$formsubmitted = $_POST['formsubmitted'];
$errors='NO';

if(empty($firstname)
   or empty($lastname) 
   or empty($address) 
   or empty($postcode) 
   or empty($city) 
   or empty($country) 
   or empty($telephone) 
   or empty($country_code) 
   or empty($email) 
	){
	$errors='YES';
}


	$user_id = api_get_user_id();

?>


<div id="submain"><h1> Exam Registration</h1>
<?php
if($errors=='NO' and $formsubmitted!='Y'){
?>
<div class="normal-message">
PLEASE FILL IN THE FORM BELOW, ONLY IF YOU HAVE COMPLETED THE COURSE AND ARE READY TO TAKE THE FINAL EXAMINATION. THERE IS NO NEED TO COMPLETE THIS FORM IF YOU HAVE NOT COMPLETED THE COURSE.<p>

In order to begin the processing of your request to participate in the online exams, please fill in the following form. <p>Read carefully the notes for each field in order to provide accurate and exact information. The accuracy of the information is critical to ensure that you will be able to take the exams. 
</div>
<?php
}
?>

<?php
if($errors=='NO' and $formsubmitted=='Y'){

	$newStatus='NEW';
 	$unique_id = sha1('SOME SECRET' . uniqid());
	$user_id = api_get_user_id();
	//Insert record in DB
	$sql = "insert into exam_applications (date_submitted,firstname,lastname,salutation,address,postcode,city,country,telephone, country_code,hours_from,hours_to,email,confirmation_id,user_id,application_status)";
            $sql .= " values (now(),\"$firstname\",\"$lastname\",\"$title\",\"$address\",\"$postcode\",\"$city\",\"$country\",\"$telephone\",\"$country_code\",\"$hours_from\",\"$hours_to\",\"$email\",\"$unique_id\",\"$user_id\",'NEW')";
            Database::query($sql);


	//send email
	 $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
	$email_admin = api_get_setting('emailAdministrator');
	$emailsubject = 'Exam Registration Confirmation Classical Homeopathy E-Learning Program ';

	$emailbody = 'Thank you for registering for your exam. Please click on this link to verify your exam request https://www.vithoulkas.edu.gr/main/exauth/validaterequest.php?id='.$unique_id;
	@api_mail('', $email, $emailsubject, $emailbody, $sender_name, $email_admin);
?>

<div class="normal-message">
Your application has been submitted. To verify your application check your email and click on the link provided in the email.
</div>

<?php
} else {

?>



<?php
if($errors=='YES' and $formsubmitted=='Y'){
	echo '<div class="error-message">The form contains incorrect or incomplete data. Please please complete all fields in the form.</div>';
 } 
?>
<div id="main"> 
<form  action="/main/exauth/requestExam.php" method="post" name="form1" id="form1">
	
	<div class="row">
		<div class="label">
		</div>
		<div class="formw">	
			<b>Identification</b>
		</div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span> First name
		</div>
		<div class="formw">	<input size="40" name="firstname" type="text" value="<?php echo $firstname;?>"/>
		</div>
	</div>
	<div class="row">
		<div class="label">

			<span class="form_required">*</span> Last name
		</div>
		<div class="formw">	<input size="40" name="lastname" type="text" value="<?php echo $lastname;?>" />
		</div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span> Salutation
		</div>
		<div class="formw">	
		<select class="chzn-select" id="title" name="title">
		<option value="Mr.">Mr.</option>
		<option value="Mrs.">Mrs.</option>
		<option value="Ms.">Ms.</option>
		<option value="Dr.">Dr.</option>
		<option value="Prof.">Prof.</option>
		</select>
		</div>
	</div>
	<div class="row" >
		<div class="label" style="color: red;">NOTE:</div>
		<div class="formw" style="color: red;">	
	Please enter correctly your full name in the fields above, capitalizing the first letter of each name.</br> 
	This information will be printed on your certificate award exactly as you have provided it.
		</div>
	</div>
	<div class="row">
		<div class="label">
		</div>
		<div class="formw">	
			<b>Communication Address</b>
		</div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span> Street Address
		</div>
		<div class="formw">	<input size="40" name="address" type="text" value="<?php echo $address;?>" />
		</div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span> Postal Code
		</div>
		<div class="formw">	<input size="40" name="postcode" type="text" value="<?php echo $postcode;?>" />
		</div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span> City &amp; State
		</div>
		<div class="formw">	<input size="40" name="city" type="text" value="<?php echo $city;?>" />
		</div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span> Country
		</div>
		<div class="formw">	<select class="chzn-select" id="country" name="country">
	<option value="Afghanistan">Afghanistan</option>
	<option value="Albania">Albania</option>
	<option value="Algeria">Algeria</option>
	<option value="American Samoa">American Samoa</option>
	<option value="Andorra">Andorra</option>
	<option value="Angola">Angola</option>
	<option value="Anguilla">Anguilla</option>
	<option value="Antarctica">Antarctica</option>
	<option value="Antigua and Barbuda">Antigua and Barbuda</option>
	<option value="Argentina">Argentina</option>
	<option value="Armenia">Armenia</option>
	<option value="Aruba">Aruba</option>
	<option value="Australia">Australia</option>
	<option value="Austria">Austria</option>
	<option value="Azerbaijan">Azerbaijan</option>
	<option value="Bahamas">Bahamas</option>
	<option value="Bahrain">Bahrain</option>
	<option value="Bangladesh">Bangladesh</option>
	<option value="Barbados">Barbados</option>
	<option value="Belarus">Belarus</option>
	<option value="Belgium">Belgium</option>
	<option value="Belize">Belize</option>
	<option value="Benin">Benin</option>
	<option value="Bermuda">Bermuda</option>
	<option value="Bhutan">Bhutan</option>
	<option value="Bolivia">Bolivia</option>
	<option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
	<option value="Botswana">Botswana</option>
	<option value="Bouvet Island">Bouvet Island</option>
	<option value="Brazil">Brazil</option>
	<option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
	<option value="Brunei Darussalam">Brunei Darussalam</option>
	<option value="Bulgaria">Bulgaria</option>
	<option value="Burkina Faso">Burkina Faso</option>
	<option value="Burundi">Burundi</option>
	<option value="Cambodia">Cambodia</option>
	<option value="Cameroon">Cameroon</option>
	<option value="Canada">Canada</option>
	<option value="Cape Verde">Cape Verde</option>
	<option value="Cayman Islands">Cayman Islands</option>
	<option value="Central African Republic">Central African Republic</option>
	<option value="Chad">Chad</option>
	<option value="Chile">Chile</option>
	<option value="China">China</option>
	<option value="Christmas Island">Christmas Island</option>
	<option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
	<option value="Colombia">Colombia</option>
	<option value="Comoros">Comoros</option>
	<option value="Congo">Congo</option>
	<option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
	<option value="Cook Islands">Cook Islands</option>
	<option value="Costa Rica">Costa Rica</option>
	<option value="Cote D'ivoire">Cote D'ivoire</option>
	<option value="Croatia">Croatia</option>
	<option value="Cuba">Cuba</option>
	<option value="Cyprus">Cyprus</option>
	<option value="Czech Republic">Czech Republic</option>
	<option value="Denmark">Denmark</option>
	<option value="Djibouti">Djibouti</option>
	<option value="Dominica">Dominica</option>
	<option value="Dominican Republic">Dominican Republic</option>
	<option value="Ecuador">Ecuador</option>
	<option value="Egypt">Egypt</option>
	<option value="El Salvador">El Salvador</option>
	<option value="Equatorial Guinea">Equatorial Guinea</option>
	<option value="Eritrea">Eritrea</option>
	<option value="Estonia">Estonia</option>
	<option value="Ethiopia">Ethiopia</option>
	<option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
	<option value="Faroe Islands">Faroe Islands</option>
	<option value="Fiji">Fiji</option>
	<option value="Finland">Finland</option>
	<option value="France">France</option>
	<option value="French Guiana">French Guiana</option>
	<option value="French Polynesia">French Polynesia</option>
	<option value="French Southern Territories">French Southern Territories</option>
	<option value="Gabon">Gabon</option>
	<option value="Gambia">Gambia</option>
	<option value="Georgia">Georgia</option>
	<option value="Germany">Germany</option>
	<option value="Ghana">Ghana</option>
	<option value="Gibraltar">Gibraltar</option>
	<option value="Greenland">Greenland</option>
	<option value="Grenada">Grenada</option>
	<option value="Guadeloupe">Guadeloupe</option>
	<option value="Guam">Guam</option>
	<option value="Guatemala">Guatemala</option>
	<option value="Guinea">Guinea</option>
	<option value="Guinea-bissau">Guinea-bissau</option>
	<option value="Guyana">Guyana</option>
	<option value="Haiti">Haiti</option>
	<option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
	<option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
	<option value="Honduras">Honduras</option>
	<option value="Hong Kong">Hong Kong</option>
	<option value="Hungary">Hungary</option>
	<option value="Iceland">Iceland</option>
	<option value="India">India</option>
	<option value="Indonesia">Indonesia</option>
	<option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
	<option value="Iraq">Iraq</option>
	<option value="Ireland">Ireland</option>
	<option value="Israel">Israel</option>
	<option value="Italy">Italy</option>
	<option value="Jamaica">Jamaica</option>
	<option value="Japan">Japan</option>
	<option value="Jordan">Jordan</option>
	<option value="Kazakhstan">Kazakhstan</option>
	<option value="Kenya">Kenya</option>
	<option value="Kiribati">Kiribati</option>
	<option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
	<option value="Korea, Republic of">Korea, Republic of</option>
	<option value="Kuwait">Kuwait</option>
	<option value="Kyrgyzstan">Kyrgyzstan</option>
	<option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
	<option value="Latvia">Latvia</option>
	<option value="Lebanon">Lebanon</option>
	<option value="Lesotho">Lesotho</option>
	<option value="Liberia">Liberia</option>
	<option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
	<option value="Liechtenstein">Liechtenstein</option>
	<option value="Lithuania">Lithuania</option>
	<option value="Luxembourg">Luxembourg</option>
	<option value="Macao">Macao</option>
	<option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
	<option value="Madagascar">Madagascar</option>
	<option value="Malawi">Malawi</option>
	<option value="Malaysia">Malaysia</option>
	<option value="Maldives">Maldives</option>
	<option value="Mali">Mali</option>
	<option value="Malta">Malta</option>
	<option value="Marshall Islands">Marshall Islands</option>
	<option value="Martinique">Martinique</option>
	<option value="Mauritania">Mauritania</option>
	<option value="Mauritius">Mauritius</option>
	<option value="Mayotte">Mayotte</option>
	<option value="Mexico">Mexico</option>
	<option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
	<option value="Moldova, Republic of Monaco">Moldova, Republic of Monaco</option>
	<option value="Mongolia">Mongolia</option>
	<option value="Montserrat">Montserrat</option>
	<option value="Morocco">Morocco</option>
	<option value="Mozambique">Mozambique</option>
	<option value="Myanmar">Myanmar</option>
	<option value="Namibia">Namibia</option>
	<option value="Nauru">Nauru</option>
	<option value="Nepal">Nepal</option>
	<option value="Netherlands">Netherlands</option>
	<option value="Netherlands Antilles">Netherlands Antilles</option>
	<option value="New Caledonia">New Caledonia</option>
	<option value="New Zealand">New Zealand</option>
	<option value="Nicaragua">Nicaragua</option>
	<option value="Niger">Niger</option>
	<option value="Nigeria">Nigeria</option>
	<option value="Niue">Niue</option>
	<option value="Norfolk Island">Norfolk Island</option>
	<option value="Northern Mariana Islands">Northern Mariana Islands</option>
	<option value="Norway">Norway</option>
	<option value="Oman">Oman</option>
	<option value="Pakistan">Pakistan</option>
	<option value="Palau">Palau</option>
	<option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
	<option value="Panama">Panama</option>
	<option value="Papua New Guinea">Papua New Guinea</option>
	<option value="Paraguay">Paraguay</option>
	<option value="Peru">Peru</option>
	<option value="Philippines">Philippines</option>
	<option value="Pitcairn">Pitcairn</option>
	<option value="Poland">Poland</option>
	<option value="Portugal">Portugal</option>
	<option value="Puerto Rico">Puerto Rico</option>
	<option value="Qatar">Qatar</option>
	<option value="Reunion">Reunion</option>
	<option value="Romania">Romania</option>
	<option value="Russian Federation">Russian Federation</option>
	<option value="Rwanda">Rwanda</option>
	<option value="Saint Helena">Saint Helena</option>
	<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
	<option value="Saint Lucia">Saint Lucia</option>
	<option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
	<option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
	<option value="Samoa">Samoa</option>
	<option value="San Marino">San Marino</option>
	<option value="Sao Tome and Principe">Sao Tome and Principe</option>
	<option value="Saudi Arabia">Saudi Arabia</option>
	<option value="Senegal">Senegal</option>
	<option value="Serbia and Montenegro">Serbia and Montenegro</option>
	<option value="Seychelles">Seychelles</option>
	<option value="Sierra Leone">Sierra Leone</option>
	<option value="Singapore">Singapore</option>
	<option value="Slovakia">Slovakia</option>
	<option value="Slovenia">Slovenia</option>
	<option value="Solomon Islands">Solomon Islands</option>
	<option value="Somalia">Somalia</option>
	<option value="South Africa">South Africa</option>
	<option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
	<option value="Spain">Spain</option>
	<option value="Sri Lanka">Sri Lanka</option>
	<option value="Sudan">Sudan</option>
	<option value="Suriname">Suriname</option>
	<option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
	<option value="Swaziland">Swaziland</option>
	<option value="Sweden">Sweden</option>
	<option value="Switzerland">Switzerland</option>
	<option value="Syrian Arab Republic">Syrian Arab Republic</option>
	<option value="Taiwan, Province of China">Taiwan, Province of China</option>
	<option value="Tajikistan">Tajikistan</option>
	<option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
	<option value="Thailand">Thailand</option>
	<option value="Timor-leste">Timor-leste</option>
	<option value="Togo">Togo</option>
	<option value="Tokelau">Tokelau</option>
	<option value="Tonga">Tonga</option>
	<option value="Trinidad and Tobago">Trinidad and Tobago</option>
	<option value="Tunisia">Tunisia</option>
	<option value="Turkey">Turkey</option>
	<option value="Turkmenistan">Turkmenistan</option>
	<option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
	<option value="Tuvalu">Tuvalu</option>
	<option value="Uganda">Uganda</option>
	<option value="Ukraine">Ukraine</option>
	<option value="United Arab Emirates">United Arab Emirates</option>
	<option value="United Kingdom">United Kingdom</option>
	<option value="United States">United States</option>
	<option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
	<option value="Uruguay">Uruguay</option>
	<option value="Uzbekistan">Uzbekistan</option>
	<option value="Vanuatu">Vanuatu</option>
	<option value="Venezuela">Venezuela</option>
	<option value="Viet Nam">Viet Nam</option>
	<option value="Virgin Islands, British">Virgin Islands, British</option>
	<option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
	<option value="Wallis and Futuna">Wallis and Futuna</option>
	<option value="Western Sahara">Western Sahara</option>
	<option value="Yemen">Yemen</option>
	<option value="Zambia">Zambia</option>
	<option value="Zimbabwe">Zimbabwe</option>
	</select>
		</div>
	</div>
        <div class="row" >
                <div class="label" style="color: red;">NOTE:</div>
                <div class="formw" style="color: red;">
Please enter your address correctly. If there is a region or state that you need to add, write this in the City field, after the City/Town of your residence. <p> This address will be used to deliver to you the digital certificate which will be <br>installed in your computer in order to participate in the online examination. You should be personally available at this address <br>during regular business hours to accept the delivery and pickup of official documents by a courier service. If you are not personally available, and a third person handles the official documents on your behalf, your exam application could be invalidated.
                </div>
        </div>
        <div class="row">
                <div class="label">
                </div>
                <div class="formw">
                        <b>Telephone and Email</b>
                </div>
        </div>

	<div class="row">
		<div class="label">
			<span class="form_required">*</span> Telephone Number
		</div>
		<div class="formw">	<input size="40" name="telephone" type="text" value="<?php echo $telephone;?>" />
		</div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span> Country Code
		</div>
		<div class="formw">	<input size="40" name="country_code" type="text" value="<?php echo $country_code;?>"/>
		</div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span> Regular Business Hours
		</div>
		<div class="formw"> from: <select class="chzn-select" id="hours_from" name="hours_from">
			<option value="06:00">06:00</option>	
			<option value="07:00">07:00</option>	
			<option value="08:00">08:00</option>	
			<option value="09:00">09:00</option>	
			<option value="10:00">10:00</option>	
			<option value="11:00">11:00</option>	
			<option value="12:00">12:00</option>	
			<option value="13:00">13:00</option>	
			<option value="14:00">14:00</option>	
			<option value="15:00">15:00</option>	
			<option value="16:00">16:00</option>	
			<option value="17:00">17:00</option>	
			<option value="18:00">18:00</option>	
			<option value="19:00">19:00</option>	
			<option value="20:00">20:00</option>	
			</select>
			to: <select class="chzn-select" id="hours_to" name="hours_to">
			<option value="06:00">06:00</option>	
			<option value="07:00">07:00</option>	
			<option value="08:00">08:00</option>	
			<option value="09:00">09:00</option>	
			<option value="10:00">10:00</option>	
			<option value="11:00">11:00</option>	
			<option value="12:00">12:00</option>	
			<option value="13:00">13:00</option>	
			<option value="14:00">14:00</option>	
			<option value="15:00">15:00</option>	
			<option value="16:00">16:00</option>	
			<option value="17:00">17:00</option>	
			<option value="18:00">18:00</option>	
			<option value="19:00">19:00</option>	
			<option value="20:00">20:00</option>	
                        </select>

		</div>
	</div>
	<div class="row" >
		<div class="label" style="color: red;">NOTE:</div>
		<div class="formw" style="color: red;">	
Enter your telephone number and the country code for international incoming calls. This number will be used by the <br>
courier service to contact you in order to arrange delivery and pickup of official documents. You should be reacheable <br>
at this phone number during regular business hours. Times are the local times in your country or region.
		</div>
	</div>
	<div class="row">
		<div class="label">
			<span class="form_required">*</span> E-mail
		</div>
		<div class="formw">	<input size="40" name="email" type="text" value="<?php echo $email;?>" />
		</div>
	</div>
	<div class="row" >
		<div class="label" style="color: red;">NOTE:</div>
		<div class="formw" style="color: red;">	
Please confirm your regular email address. This address should match the one that you registered with for the <br>
course initially. It should also match with your Paypal account address. Payment from a different account, or <br>
with any alternative means  is not accepted for the online examination.  You will also receive an email at this<br>
 address, shortly after submitting this form which will ask you to confirm your application for the online examination.
		</div>
	</div>
	<div class="row">
		<div class="label">
		</div>
		<div class="formw">	<button class="save" name="submit" type="submit" >Register</button>
		</div>
	</div>
	<input type="hidden" name="formsubmitted" value="Y">
</form>

</div>
<?php
}
Display :: display_footer();

?>
