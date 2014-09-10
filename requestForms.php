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
			'database' => 'requestforms',
			'table' => 'requestforms',
			'administrators' => true,
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
		  `feedbackRecipient` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'E-mail of feedback recipient',
		  `welcomeTextHtml` text COLLATE utf8_unicode_ci COMMENT 'HTML fragment for welcome text',
		  `datasourceSocietyCategory` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'societiesdirectory.categories.[id,name]' COMMENT 'Datasource for society form: category',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Settings' AUTO_INCREMENT=2 ;
		INSERT INTO `settings` (`id`, `feedbackRecipient`, `welcomeTextHtml`) VALUES (1, 'coordinator@" . "cusu.cam.ac.uk', '<p>With these forms, you can request CUSU staff to set up a new section for you on the new CUSU website.</p><p>Please note that requests will only be processed during office hours.</p>');

		-- Election form
		CREATE TABLE IF NOT EXISTS `election` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Request to add an election';
		
		-- Manager form
		CREATE TABLE IF NOT EXISTS `manager` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
		  `crsid` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Username (@cam.ac.uk)',
		  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name',
		  `confirm` int(1) NOT NULL COMMENT 'I confirm I have the right to administrate this group',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Request to be a society or group manager';
		
		-- Society form
		CREATE TABLE IF NOT EXISTS `society` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
		  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Society name',
		  `category` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Category',
		  `description` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description',
		  `websiteUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Website',
		  `facebookUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Facebook page',
		  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'E-mail address of society',
		  `sellMemberships` enum('','Yes','No','Not sure') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Do you wish to sell memberships to your group online?',
		  `membershipCost` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Cost of membership',
		  `membershipLength` enum('','Annual','Term') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Length of memberships to be available',
		  `person` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Society Administrator/President (full name required)',
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
		$list = array ();
		foreach ($this->forms as $form => $title) {
			$list[$form] = "<a href=\"{$this->baseUrl}/{$form}/\">" . htmlspecialchars ($title) . '</a>';
		}
		$html .= application::htmlUl ($list, false, 'requestformslist boxylist');
		
		# Show the HTML
		echo $html;
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
		$attributes['file']['directory'] = '/tmp/';
		$attributes['file']['attachment'] = true;
		
		# Create the databinded form
		require_once ('ultimateForm.php');
		$form = new form (array (
			'databaseConnection' => $this->databaseConnection,
			'displayRestrictions' => false,
			'autofocus' => true,
			'picker' => true,
			'cols' => 60,
			'rows' => 10,
		));
		$form->dataBinding (array (
			'database' => $this->settings['database'],
			'table' => $table,
			'intelligence' => true,
			'int1ToCheckbox' => true,
			'attributes' => $attributes,
		));
		if (!$result = $form->process ($html)) {
			echo $html;
			return;
		}
		
		application::dumpData ($result);
		
		# Show the HTML
		echo $html;
	}
	
	
	# Settings page
	public function settings ($dataBindingSettingsOverrides = array ())
	{
		# Define overrides
		$dataBindingSettingsOverrides = array (
			'attributes' => array (
				'welcomeTextHtml' => array ('editorToolbarSet' => 'Basic', 'width' => 500, ),
			),
		);
		
		# Run the native settings page
		parent::settings ($dataBindingSettingsOverrides);
	}
}

?>
