<?php
	Class AD {
		private $connectionSettings;
		private $ad_connection;

		public $errorCount = 0;
		public $errorMsgs = array();

		public function __construct() {
			// Set the connection settings
			if(!$this->getConnectionSettings()) {
				throw new Exception('The AD connection settings are not properly set');
				exit();
			}

			// Connect and bind with the account specified in init.php
			// If this fails, throw an exception
			if (!($this->connect() && $this->bind())) {
				throw new Exception('Unable to connect to the AD server');
				exit();
			}
		}

		public function __destruct() {
			//Close the AD connection on destruction
			ldap_unbind($this->ad_connection);
		}

		private function getConnectionSettings() {
	        $connectionSettingsObject = new ConnectionSettings();
	        $this->connectionSettings = $connectionSettingsObject->getAll();
	        // Make sure all the settings are set
	        if (!empty($this->connectionSettings) && $connectionSettingsObject->areAllSettingsSet()) {
                return true;
	        }
	        // default return
	        return false;
	    }

		private function connect() {
			if ($this->ad_connection = ldap_connect($this->connectionSettings['DC'], $this->connectionSettings['port'])) {
				return true;
			}
			else {
				$this->errorCount++;
				$this->errorMsgs[] = 'The Active Directory connection failed.';
				return false;
			}
		}

		private function bind() {
			ldap_set_option($this->ad_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($this->ad_connection, LDAP_OPT_REFERRALS, 0);

			if (ldap_bind( $this->ad_connection, $this->connectionSettings['username'] . '@' . $this->connectionSettings['domainName'], $this->connectionSettings['password'] )) {
				return true;
			}
			else {
				$this->errorCount++;
				$this->errorMsgs[] = 'The Active Directory bind failed. Check the username and password.';
				return false;
			}
		}

		protected function checkUserCredentials($username, $password) {
			if (isset($this->connectionSettings['DC']) && isset($this->connectionSettings['port'])) {
	            if ($user_ad_connection = ldap_connect($this->connectionSettings['DC'], $this->connectionSettings['port'])) {
	                if (isset($username) && isset($password)) {
			            ldap_set_option($user_ad_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
			            ldap_set_option($user_ad_connection, LDAP_OPT_REFERRALS, 0);

			            $ldapUsername = $username;
			            // If they entered username@domain.local, remove the @domain.local
			            $ldapUsername = preg_replace('{@.*$}', '' , $ldapUsername);
			            // If they entered domain\username, then remove domain\
			            $ldapUsername = preg_replace('{^.*\\\}', '' , $ldapUsername);
			            // Add the @domain.local
			            $ldapUsername = $ldapUsername . '@' . $this->connectionSettings['domainName'];

			            if (@ldap_bind( $user_ad_connection, $ldapUsername, $password )) {
			            	ldap_unbind($user_ad_connection);
			                return true;
			            }
			        }
	            }
	        }

	        return false;
		}

		public function search($query) {
			if (isset($query)) {
				if ($result = ldap_search($this->ad_connection, $this->connectionSettings['baseDN'], $query)) {
		        	$data = ldap_get_entries($this->ad_connection, $result);
		        	return $data;
		        }

			    else {
			    	return array();
			    }
			}

			else {
				return array();
			}
		}

		public function getUser($username) {
			if (isset($username)) {
				$sanitizedUsername = $this->sanitizeLDAP($username);
				if ($user = $this->search('(&(objectCategory=person)(objectClass=user)(sAMAccountName=' . $sanitizedUsername . '))')) {
					if ($user['count'] == 1) {
						$userArray = $this->formatSingleResult($user);

						// This will format the membership properly
						if (isset($userArray['memberof'])) {
							$userArray['memberof'] = $this->getMembership($username);
						}
		        		return $userArray;
					}
				}
			}
			return array();
		}

		public function getUserAttribute($username, $attribute) {
			if (isset($username) && isset($attribute)) {
				$user = $this->getUser($username);
				$attribute = strtolower($attribute);
				
				if (isset($user[$attribute])) {
					return $user[$attribute];
				}
			}

			return false;
		}

		public function getUserGUID($username) {
			if (isset($username)) {
				$user = $this->getUser($username);
				if (isset($user['objectguid'])) {
					return $user['objectguid'];
				}
			}

			return false;
		}

		public function getUserBinaryGUID($username) {
			if (isset($username)) {
				$sanitizedUsername = $this->sanitizeLDAP($username);
				if ($user = $this->search('(&(objectCategory=person)(objectClass=user)(sAMAccountName=' . $sanitizedUsername . '))')) {
					if ($user['count'] == 1) {
						if (isset($user[0]['objectguid'][0])) {
							return $user[0]['objectguid'][0];
						}
					}
				}
			}

			return false;
		}

		public function getUserFromGUID($GUID) {
			if (isset($GUID)) {
				$searchableGUID = $this->stringGUIDToSearchable($GUID);
				if ($searchableGUID) {
					if ($user = $this->search('(objectguid=' . $searchableGUID . ')')) {
						$userArray = $this->formatSingleResult($user);
						return $userArray;
					}
				}
			}

			return false;
		}

		public function getEmail($username) {
			if (isset($username)) {
				$user = $this->getUser($username);
				if (isset($user['mail'])) {
					return $user['mail'];
				}
			}

			return false;
		}

		public function unlockAccount($username) {
			if (isset($username)) {
				if ($userDN = $this->getUser($username)['distinguishedname']) {
					// Set the lockout time to 0 for the user
					$lockouttime=array();
		        	$lockouttime["lockouttime"][0] = 0;
					if (ldap_mod_replace($this->ad_connection, $userDN, $lockouttime)) {
						return true;
		            }
				}
			}
			 return false;
		}

		public function setPassword($username, $newPassword) {
			if (isset($username) && isset($newPassword)) {
				if ($userDN = $this->getUser($username)['distinguishedname']) {
					$encodedPW=array();
		        	$encodedPW["unicodePwd"][0] = $this->encodePassword($newPassword);
					if (ldap_mod_replace($this->ad_connection, $userDN, $encodedPW)) {
						return true;
		            }
				}
				
			}
            return false;
		}

		public function changePassword($username, $password, $newPassword) {
			if (isset($username) && isset($password) && isset($newPassword)) {
				// If the user's current password is correct, then they can change their password
				if ($this->checkUserCredentials($username, $password)) {
					if ($this->setPassword($username, $newPassword)) {
						return true;
					}

					return false;
				}
			}

			return false;
		}

		// Since Active Directory requires a unicode formatted password, this will convert ASCII to it
		protected function encodePassword($password) {
	        $password="\"" . $password . "\"";
	        $encoded="";
	        for ($i=0; $i < strlen($password); $i++) {
	        	$encoded .= "{$password{$i}}\000";
	        }
	        return $encoded;
		}

		public function isPWComplexityRequired() {
			if ($results = $this->search('(&(objectClass=domainDNS))')) {
				$complexityRequired = $results[0]['pwdproperties'][0];

				if ($complexityRequired == 1) {
					return true;
				}
				elseif ($complexityRequired == 0) {
					return false;
				}
			}

			// If the query fails, err on the side of caution and enforce the complexity restrictions
			return true;
		}

		public function getMinPasswordLength() {
			if ($results = $this->search('(&(objectClass=domainDNS))')) {
				$minPasswordLength = $results[0]['minpwdlength'][0];
				if (isset($minPasswordLength)) {
					return $minPasswordLength;
				}
			}

			// default return
			return false;
		}

		public function getGroup($groupSAM) {
			if (isset($groupSAM)) {
				$sanitizedGroupSAM = $this->sanitizeLDAP($groupSAM);
				if ($group = $this->search('(&(objectCategory=group)(objectClass=group)(sAMAccountName=' . $sanitizedGroupSAM . '))')) {
					if ($group['count'] == 1) {
						$groupArray = $this->formatSingleResult($group);
		        		return $groupArray;
					}
				}
			}
			return array();
		}

		public function getGroupSAMFromGUID($GUID) {
			if (isset($GUID)) {
				$searchableGUID = $this->stringGUIDToSearchable($GUID);
				if ($searchableGUID) {
					if ($group = $this->search('(objectguid=' . $searchableGUID . ')')) {
						if ($group['count'] == 1) {
							if (isset($group[0]['samaccountname'][0])) {
								return $group[0]['samaccountname'][0];
							}
						}
						
					}
				}
			}

			return false;
		}

		public function getGUIDFromGroupName($groupName) {
			if (isset($groupName)) {
				$sanitizedGroup = $this->sanitizeLDAP($groupName);
				if ($group = $this->search('(&(objectCategory=group)(objectClass=group)(name=' . $sanitizedGroup . '))')) {
					if ($group['count'] == 1) {
						if (isset($group[0]['objectguid'][0])) {
							return $this->GUIDToString($group[0]['objectguid'][0]);
						}
					}
				}
			}
		}

		public function getObjectByDN($distinguishedName) {
			if (isset($distinguishedName)) {
				$sanitizedDN = $this->sanitizeLDAP($distinguishedName);
				if ($object = $this->search('(distinguishedName=' . $sanitizedDN . ')')) {
					if ($object['count'] == 1) {
						$objectArray = $this->formatSingleResult($object);
		        		return $objectArray;
					}
				}
			}

			return array();
		}

		public function getMembership($username) {
			if (isset($username)) {
				$sanitizedUsername = $this->sanitizeLDAP($username);
				if ($userObject = $this->search('(&(objectCategory=person)(objectClass=user)(sAMAccountName=' . $sanitizedUsername . '))')) {
					if ($userObject['count'] == 1) {

						$user = $this->formatSingleResult($userObject);

						$groupMembership = array();
						// Add the Primary Group which is not part of the memberof attribute. This is typically Domain Users
						$groupMembership[] = $this->getPrimaryGroupName($user['primarygroupid']);

						// Checks to see if $user['memberof'] is an array. This is because when the user is only part of one group, the varable is a string.
						// More than one it is an array
						if (isset($user['memberof']) && is_array($user['memberof'])) {
							foreach ($user['memberof'] as $group) {
								//This if statement weeds out the initial index of $user['memberof'] which is the total of groups the user is a part of
								if (!is_int($group)) {
									$groupMembership[] = $this->getObjectByDN($group)['name'];
								}
							}
							
						}
						elseif (isset($user['memberof']) && is_string($user['memberof'])) {
							$groupMembership[] = $this->getObjectByDN($user['memberof'])['name'];
						}

						return $groupMembership;
					}
				}

			}

			return array();
		}

		public function isMemberOf($username, $groupName) {
			if (isset($username) && isset($groupName)) {
				$groupMembership = $this->getMembership($username);
				if (isset($groupMembership)) {
					if (in_array($groupName, $groupMembership)) {
						return true;
					}
					// If the user is only part of one group, then the groupMembership will be a string
					elseif ($groupMembership == $groupName) {
						return true;
					}
					else {
						return false;
					}
				}
			}

			else {
				return false;
			}
		}

		public function getPrimaryGroupName($primaryGroupID) {
			if (isset($primaryGroupID)) {
				// Search for the Domain SID
				$search = ldap_read($this->ad_connection, $this->connectionSettings['baseDN'],  '(objectclass=*)', array('objectSid'));
				$result = ldap_get_entries($this->ad_connection, $search);

				$domainSID = $this->SIDtoString($result[0]['objectsid'][0]);
				// Search for the Primary Group which is DomainSID-PrimaryGroupID
				$search = ldap_search($this->ad_connection, $this->connectionSettings['baseDN'], "objectSid=${domainSID}-${primaryGroupID}", array('cn'));
				$result = ldap_get_entries($this->ad_connection, $search);
				
				$primaryGroup = $this->formatSingleResult($result);
				if (isset($primaryGroup)) {
					return($this->getObjectByDN($primaryGroup['dn'])['name']);
				}
			}

			return '';
		}

		public function getPrimaryGroupDN($primaryGroupID) {
			if (isset($primaryGroupID)) {
				// Search for the Domain SID
				$search = ldap_read($this->ad_connection, $this->connectionSettings['baseDN'],  '(objectclass=*)', array('objectSid'));
				$result = ldap_get_entries($this->ad_connection, $search);

				$domainSID = $this->SIDtoString($result[0]['objectsid'][0]);
				// Search for the Primary Group which is DomainSID-PrimaryGroupID
				$search = ldap_search($this->ad_connection, $this->connectionSettings['baseDN'], "objectSid=${domainSID}-${primaryGroupID}", array('cn'));
				$result = ldap_get_entries($this->ad_connection, $search);
				
				$primaryGroup = $this->formatSingleResult($result);
				if (isset($primaryGroup)) {
					return $primaryGroup['dn'];
				}
			}

			return '';
		}

		// This function cleans up the result into a single level array for attributes unles the attribute has mutliple values, then it becomes an array within the array.
		// It also converts the binary data returned from objectsid and objectguid to text
		private function formatSingleResult($result) {
			if (isset($result)) {
				//Grab only the first result, since there is only one user being returned. It saves us from having to do [0] before every other index
	        	$object = $result[0];
	    		$objectArray = array();

	    		foreach ($object as $objectAttributeKey => $objectAttribute) {
	    			// This if statement will filters the numerical indexes that just print the attributes without values
	    			if ($objectAttributeKey != 'count' && !(is_int($objectAttributeKey))) {
	    				// Determine if its an array. If so, expand it.
		    			if (is_array($objectAttribute)) {
		    				// Since there is always a objectAttribute['count'], this if statement checks to see if there is more than just objectAttribute['count'] and objectAttribute[0]
		    				// if so, then create an array without objectAttribute['count']
		    				if (count($objectAttribute) > 2) {
		    					$attributeValues = array();
		        				foreach ($objectAttribute as $objectAttributeValue) {
	        						//Since the SID and GUID comes back as a binary, these if statements will make sure the string data is formatted like in the Attribute Values tab in Active Directory Users and Computers 
	        						if ($objectAttributeKey == 'objectsid') {
	        							$attributeValues[] = $this->SIDtoString($objectAttributeValue);
	    							}
	    							elseif ($objectAttributeKey == 'objectguid') {
	    								$attributeValues[] = $this->GUIDtoString($objectAttributeValue);
	    							}
	    							else {
	    								$attributeValues[] = $objectAttributeValue;
	    							}
		        				}

		        				$objectArray[$objectAttributeKey] = $attributeValues;
		        			}
		        			// If there is only objectAttribute['count'] and objectAttribute[0], then make the value a string and not a single indexed array
		        			elseif (count($objectAttribute) == 2) {
								//Since the SID and GUID comes back as a binary, these if statements will make sure the string data is formatted like in the Attribute Values tab in Active Directory Users and Computers 
								if ($objectAttributeKey == 'objectsid') {
									$objectArray[$objectAttributeKey] = $this->SIDtoString($objectAttribute[0]);
								}
								elseif ($objectAttributeKey == 'objectguid') {
									$objectArray[$objectAttributeKey] = $this->GUIDtoString($objectAttribute[0]);
								}
								else {
									$objectArray[$objectAttributeKey] = $objectAttribute[0];
								}
							}
		    			}
		    			// If it isn't an array, no need to expand, just assign the value as is
		    			else {
		    				$objectArray[$objectAttributeKey] = $objectAttribute;
		    			}
		    		}
	    		}

	    		return $objectArray;
	    	}

	    	else {
	    		return array();
	    	}	    	
		}

		// Since the SID from PHP LDAP is returned in binary form, this function converts it the string we are all accustomed to in the Attribute Editor in Active Directory Users and Computers
		// From http://blogs.freebsdish.org/tmclaugh/2010/07/21/finding-a-users-primary-group-in-ad/
		public function SIDtoString($adSID) {
			$srl = ord($adSID[0]);
			$number_sub_id = ord($adSID[1]);
			$x = substr($adSID,2,6);
			$h = unpack('N',"\x0\x0".substr($x,0,2));
			$l = unpack('N',substr($x,2,6));
			$iav = bcadd(bcmul($h[1],bcpow(2,32)),$l[1]);
			for ($i=0; $i<$number_sub_id; $i++)
			{
			$sub_id = unpack('V', substr($adSID, 8+4*$i, 4));
			$sub_ids[] = $sub_id[1];
			}
			return sprintf('S-%d-%d-%s', $srl, $iav, implode('-',$sub_ids));
		}

		// Since the GUID from PHP LDAP is returned in binary form, this function converts it the string we are all accustomed to in the Attribute Editor in Active Directory Users and Computers
		// From http://www.null-byte.org/development/php-active-directory-ldap-authentication/
		public function GUIDtoString($adGUID) {
		   $GUIDinHex = str_split(bin2hex($adGUID), 2);
		   $GUID = "";
		   $first = array_reverse(array_slice($GUIDinHex, 0, 4));

		   foreach($first as $value)
		   {
		      $GUID .= $value;
		   }

		   $GUID .= "-";
		   $second = array_reverse(array_slice($GUIDinHex, 4, 2, true), true);
		   
		   foreach($second as $value)
		   {
		      $GUID .= $value;
		   }

		   $GUID .= "-";
		   $third = array_reverse(array_slice($GUIDinHex, 6, 2, true), true);

		   foreach($third as $value)
		   {
		      $GUID .= $value;
		   }

		   $GUID .= "-";
		   $fourth = array_slice($GUIDinHex, 8, 2, true);

		   foreach($fourth as $value)
		   {
		      $GUID .= $value;
		   }

		   $GUID .= "-";
		   $last = array_slice($GUIDinHex, 10, 16, true);

		   foreach($last as $value)
		   {
		      $GUID .= $value;
		   }

		   return $GUID;
		}

		public function stringGUIDToSearchable($adGUIDString) {
			$GUIDArray = explode('-', $adGUIDString);
			$GUID = "\\";
			for ($i = 0; $i < 3; $i++) {
				for ($j = strlen($GUIDArray[$i]) - 1; $j > 0; $j -= 2) {
					$GUID .= $GUIDArray[$i][$j - 1];
					$GUID .= $GUIDArray[$i][$j] . "\\";
				}
			}

			for ($i = 3; $i < 5; $i++) {
				for ($j = 0; $j < strlen($GUIDArray[$i]); $j++) {
					$GUID .= $GUIDArray[$i][$j];
					$j++;
					
					// On the last string and the last index of the array, don't add a backslash
					if (strlen($GUIDArray[$i]) - 1 == $j && $i == 4) {
						$GUID .= $GUIDArray[$i][$j];
					}
					else {
						$GUID .= $GUIDArray[$i][$j] . "\\";
					}
				}
			}

			return $GUID;
		}

		// Function was taken from https://krivokuca.net/2012/08/word-of-advice-escaping-ldap-filter-values-in-php
		public function sanitizeLDAP($string) {
		 	if (isset($string)) {
		 		$metaChars = array ("\\00", "\\", "(", ")", "*");
			    $quotedMetaChars = array ();

			    foreach ($metaChars as $key => $value) {
			        $quotedMetaChars[$key] = '\\'. dechex (ord ($value));
			    }

			    $string = str_replace (
			        $metaChars, $quotedMetaChars, $string
			    );

			    return ($string);
		 	}

		 	return '';
		}
	}
	