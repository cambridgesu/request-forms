<?php

# Request forms application
require_once ('frontControllerApplication.php');
class requestForms extends frontControllerApplication
{
	# Function to assign defaults
	public function defaults ()
	{
		# Specify available arguments as defaults or as NULL (to represent a required argument)
		$defaults = array (
			
			# Main settings
			'database' => 'requestforms',
			'table' => 'requestforms',
			'administrators' => true,
			'apiUsername' => false,
			
			# Internal parameters
			'useCamUniLookup' => true,
		);
		
		# Return the defaults
		return $defaults;
	}
	
	
	# Function to assign supported actions
	public function actions ()
	{
		# Define available tasks
		$actions = array (
			'form' => array (
				'description' => false,
				'url' => 'form/%1/',
				'usetab' => 'home',
				'authentication' => true,
			),
		);
		
		# Return the actions
		return $actions;
	}
	
	
	# Define the database structure
	private function databaseStructure ()
	{
		return "
		-- Administrators
		CREATE TABLE IF NOT EXISTS `administrators` (
		  `crsid` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
		  `active` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
		  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`crsid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Administrators';
		
		-- Settings
		CREATE TABLE IF NOT EXISTS `settings` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key (ignored)',
		  `feedbackRecipient` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'E-mail of form recipient',
		  `feedbackRecipientEpayments` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'E-mail of e-payments form recipient',
		  `welcomeTextHtml` text COLLATE utf8_unicode_ci COMMENT 'HTML fragment for welcome text',
		  `epaymentsTermsUrl` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'E-payments terms URL',
		  `datasourceSocietyCategory` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'societiesdirectory.categories.[id,name]' COMMENT 'Datasource for society form: category',
		  `datasourceElectionCollege` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'bwp.overview.[college,name]' COMMENT 'Datasource for election form: college',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Settings';
		INSERT INTO `settings` (`id`, `feedbackRecipient`, `welcomeTextHtml`) VALUES (1, 'coordinator@" . "cusu.cam.ac.uk', '<p>With these forms, you can request CUSU staff to set up a new section for you on the new CUSU website.</p><p>Please note that requests will only be processed during office hours.</p>');

		-- Election form
		CREATE TABLE IF NOT EXISTS `election` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
		  `submittedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Your username (@cam.ac.uk)',
		  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Election name',
		  `returningOfficers` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Usernames (@cam.ac.uk) of Returning Officer(s)',
		  `type` enum('','Society election','JCR election','MCR election','CUSU election','Faculty election') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type',
		  `studentGroup` set('Undergraduate','Graduate') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Student group(s) eligible to vote',
		  `college` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Limit to college members?',
		  `candidates` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'List of positions (e.g. President) and candidates for each post',
		  `startDate` date NOT NULL COMMENT 'Start date',
		  `startTime` time NOT NULL COMMENT 'Start time',
		  `endDate` date NOT NULL COMMENT 'End date',
		  `endTime` time NOT NULL COMMENT 'End time',
		  `electoralRoll` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Electoral roll (list of usernames, one per line)',
		  `file` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Manifestos - please upload file containing each manifesto',
		  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at (automatic timestamp)',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Request to add an election';
		
		-- E-payments form
		CREATE TABLE IF NOT EXISTS `epayments` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
		  `submittedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Your username (@cam.ac.uk)',
		  `societyName` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Society name',
		  `bankAccountName` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Bank account name',
		  `bankAccountNumber` varchar(8) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Bank account number',
		  `bankSortCode` varchar(8) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Bank account Sort Code',
		  `vatRegistered` enum('No','Yes') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Is the society VAT registered?',
		  `timeEstablished` enum('<6 months','6-24 months','2-5 years','5+ years') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Approximate length of time your society has existed',
		  `societyAffiliation` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Is the Society affiliated to a specific College or Department?',
		  `seniorTreasurerUsername` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Senior Treasurer e-mail',
		  `seniorTreasurerName` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Senior Treasurer name',
		  `juniorTreasurerUsername` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Junior Treasurer @cam username',
		  `juniorTreasurerName` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Junior Treasurer name',
		  `juniorTreasurerPhone` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Junior Treasurer phone number',
		  `juniorTreasurerCourse` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Junior Treasurer course',
		  `juniorTreasurerEndDate` date NOT NULL COMMENT 'Junior Treasurer course end date',
		  `agreeTerms` int(1) NOT NULL COMMENT 'I agree to the Terms below',
		  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at (automatic timestamp)',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Setup epayments for a Society';
		
		-- Manager form
		CREATE TABLE IF NOT EXISTS `manager` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
		  `submittedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Your username (@cam.ac.uk)',
		  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Your name',
		  `societyName` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of Society',
		  `isRegistered` enum('','Yes','No') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Is your Society registered with the Societies Syndicate?',
		  `confirm` int(1) NOT NULL COMMENT 'I confirm I have the right to administrate this group',
		  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at (automatic timestamp)',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Request to be a society or group manager';
		
		-- Society form
		CREATE TABLE IF NOT EXISTS `society` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
		  `submittedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Your username (@cam.ac.uk)''',
		  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Society name',
		  `category` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Category',
		  `description` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description',
		  `websiteUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Website',
		  `facebookUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Facebook page',
		  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'E-mail address of society',
		  `isRegistered` enum('','Registered','Not registered') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Is your Society registered with the Societies Syndicate?',
		  `sellMemberships` enum('','Yes','No','Not sure','Not applicable - unregistered society') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Do you wish to sell memberships to your group online? (Available only to registered societies.)',
		  `membershipCost` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Cost of membership',
		  `membershipLength` enum('','Annual','Term') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Length of memberships to be available',
		  `person` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Society Administrator/President (full name required)',
		  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at (automatic timestamp)',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Request to add a society or club';
		";
	}
	
	
	# Additional constructor processing
	public function main ()
	{
		# Get the list of forms
		$this->forms = $this->getForms ();
		
		# Get datasource specifications for form fields
		$this->datasources = $this->getDatasources ();
		
	}
	
	
	# Function to get the list of forms
	private function getForms ()
	{
		# Get the list of tables and the comment for each
		$tables = $this->databaseConnection->getTables ($this->settings['database']);
		#!# Add getting comments as an option in getTables natively
		$forms = array ();
		foreach ($tables as $table) {
			$forms[$table] = $this->databaseConnection->getTableComment ($this->settings['database'], $table);
		}

		# Set tables to exclude
		#!# Ideally the list would be supplied to getTables() natively
		$excludeTables = array ('administrators', 'settings', );
		foreach ($excludeTables as $table) {
			unset ($forms[$table]);
		}
		
		# Return the forms
		return $forms;
	}
	
	
	# Function to get datasource specifications for use in form fields
	#!# These ought to be replaced with HTTP-level APIs
	private function getDatasources ()
	{
		# Extract the definitions from the settings array
		$datasources = array ();
		foreach ($this->settings as $setting => $value) {
			if (preg_match ('/^datasource([A-Z][a-z]+)([A-Z][a-z]+)$/', $setting, $matches)) {
				if (preg_match ('/^([^.]+)\.([^.]+)\.\[(.+)\]$/', $value, $valueMatches)) {
					$table = strtolower ($matches[1]);
					$field = strtolower ($matches[2]);
					$fields = explode (',', $valueMatches[3], 2);
					$datasources[$table][$field] = array (
						'database' => $valueMatches[1],
						'table' => $valueMatches[2],
						'fields' => $fields,
						'orderBy' => array_pop ($fields),
					);
				}
			}
		}
		
		# Return the datasources list
		return $datasources;
	}
	
	
	# Home page
	public function home ()
	{
		# Start the HTML
		$html = '';
		
		# Add introduction HTML
		$html .= $this->settings['welcomeTextHtml'];
		
		# Show a list of the available forms
		$html .= $this->formsList ('requestformslist boxylist');
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to create a list of forms
	public function formsList ($cssClass = false)
	{
		# Create the list
		$list = array ();
		foreach ($this->forms as $form => $title) {
			$list[$form] = "<a href=\"{$this->baseUrl}/{$form}/\">" . htmlspecialchars ($title) . '</a>';
		}
		
		# Compile the HTML
		$html .= application::htmlUl ($list, false, $cssClass);
		
		# Return the HTML
		return $html;
	}
	
	
	# Form page
	public function form ($table)
	{
		# Start the HTML
		$html = '';
		
		# Add the title
		$html .= "\n<h2>" . htmlspecialchars ($this->forms[$table]) . '</h2>';
		
		# Start databinding attributes
		$attributes = array ();
		
		# Determine any lookups required
		if (isSet ($this->datasources[$table])) {
			foreach ($this->datasources[$table] as $field => $datasource) {
				$attributes[$field]['values'] = $this->databaseConnection->selectPairs ($datasource['database'], $datasource['table'], array (), $datasource['fields'], true, $datasource['orderBy']);
				$attributes[$field]['type'] = 'select';
			}
		}
		
		# Standard overrides to form structure
		$attributes['submittedBy']['type'] = 'email';
		$attributes['submittedBy']['default'] = $this->user . '@cam.ac.uk';
		$attributes['submittedBy']['editable'] = false;
		$attributes['name']['default'] = $this->userName;
		$attributes['file']['directory'] = $this->dataDirectory;
		$attributes['file']['forcedFileName'] = $table . '_' . $this->user . '_' . date ('Y-m-d_H-i-s');
		$attributes['file']['attachments'] = true;
		
		# Add autocomplete to any username fields
		$autocompleteFields = $this->databaseConnection->getFieldnames ($this->settings['database'], $table, false, '[Uu]sername');
		if ($autocompleteFields) {
			foreach ($autocompleteFields as $autocompleteField) {
				
				# Determine the accompanying name field
				$nameField = preg_replace ('/^(.+)[Uu]sername$/', '$1', $autocompleteField) . 'Name';
				
				# JS function to copy the e-mail address, and extract name and split it into forename and surname; see: http://stackoverflow.com/a/12340803
				$focusSelectJsFunction = "
					function( event, ui ) {
						var name = ui.item.label.replace(/^.+\((.+)\)$/g, '$1');
						$( '#form_{$autocompleteField}' ).val( ui.item.value " . ($this->emailDomain ? "+ '@{$this->emailDomain}' " : '') . ");
						$( '#form_{$nameField}' ).val( name );
					}
				";
				$autocompleteOptions = array (
					'delay'		=> 0,
					'focus'		=> $focusSelectJsFunction,
					'select'	=> $focusSelectJsFunction,
				);
				
				# Add the autocomplete to the form specification
				$attributes[$autocompleteField]['autocomplete'] = $this->baseUrl . '/data.html';
				$attributes[$autocompleteField]['autocompleteOptions'] = $autocompleteOptions;
			}
		}
		
		# Table-specific overrides to form structure
		switch ($table) {
			case 'epayments':
				$attributes['societyName']['heading'] = array (2 => 'Society details');
				$attributes['seniorTreasurerUsername']['heading'] = array (2 => 'Senior Treasurer details');
				$attributes['juniorTreasurerUsername']['heading'] = array (2 => 'Junior Treasurer details');
				$attributes['agreeTerms']['heading'] = array (2 => 'Terms and conditions');
				$attributes['agreeTerms']['title'] = 'I agree to the <a href="' . $this->settings['epaymentsTermsUrl'] . '" target="_blank" title="[Link opens in a new window]">Terms</a>';
				break;
		}
		
		# Determine the form recipient
		$formRecipient = $this->settings['feedbackRecipient'];
		$tableSpecificSetting = 'feedbackRecipient' . ucfirst ($table);
		if (isSet ($this->settings[$tableSpecificSetting])) {
			$formRecipient = $this->settings[$tableSpecificSetting];
		}
		
		# Create the databinded form
		require_once ('ultimateForm.php');
		$form = new form (array (
			'databaseConnection' => $this->databaseConnection,
			'displayRestrictions' => false,
			'autofocus' => true,
			'picker' => true,
			'cols' => 60,
			'rows' => 10,
			'formCompleteText' => 'Thank you for your submission. We will be in touch shortly.',
		));
		$form->dataBinding (array (
			'database' => $this->settings['database'],
			'table' => $table,
			'intelligence' => true,
			'int1ToCheckbox' => true,
			'attributes' => $attributes,
		));
		$form->setOutputEmail ($formRecipient, $this->settings['administratorEmail'], 'Website request form: ' . $this->forms[$table], NULL, 'submittedBy');
		if (!$result = $form->process ($html)) {
			echo $html;
			return;
		}
		
		# Insert the submission into the database as a backup
		$this->databaseConnection->insert ($this->settings['database'], $table, $result);
		
		# Show the HTML
		echo $html;
	}
	
	
	# Settings page
	public function settings ($dataBindingSettingsOverrides = array ())
	{
		# Define overrides
		$dataBindingSettingsOverrides = array (
			'attributes' => array (
				'welcomeTextHtml' => array ('editorToolbarSet' => 'Basic', 'width' => 500, 'height' => 175, ),
			),
		);
		
		# Run the native settings page
		parent::settings ($dataBindingSettingsOverrides);
	}
	
	
	# API call for dashboard
	public function apiCall_dashboard ($username = NULL)
	{
		# Start the HTML
		$html = '';
		
		# State that the service is enabled
		$data['enabled'] = true;
		
		# Ensure a username is supplied
		if (!$username) {
			$data['error'] = 'No username was supplied.';
			return $data;
		}
		
		# Define description
		$data['descriptionHtml'] = "<p>With these forms, you can request CUSU staff to set up a new section for you on the new CUSU website.</p>";
		
		# Add list of forms
		$html .= $this->formsList ();
		
		# Register the HTML
		$data['html'] = $html;
		
		# Return the data
		return $data;
	}
	
	
	# Function to provide autocomplete functionality
	public function data ()
	{
		# Get the data
		$data = $this->dataLookup ();
		
		# Arrange the data
		$json = json_encode ($data);
		
		# Send JSON headers
		header ('Content-type: application/json; charset=UTF-8');
		
		# Allow access only to the current domain, rather than any external site
		header ('Access-Control-Allow-Origin: ' . $_SERVER['_SITE_URL']);
		
		# Send the text
		echo $json;
	}
	
	
	# Function to get data from lookup
	private function dataLookup ()
	{
		# Get the search term
		if (!isSet ($_GET['term']) || !strlen ($_GET['term'])) {return array ('error' => 'No query term was sent');}
		$term = trim ($_GET['term']);
		
		# Get the data
		require_once ('camUniData.php');
		$data = camUniData::lookupUsers ($term, true, $indexByUsername = true);
		
		# Remove keys so that they are indexed numerically
		$data = array_values ($data);
		
		# Return the data
		return $data;
	}
}

?>
