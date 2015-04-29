<?php
    require_once(__DIR__ . '/../core/init.php');
    function ADPasswordPolicyMatch($password) {

        try {
            $AD = new AD();
        }
        catch(Exception $e) {
            $AD = null;
            Logger::log('error', $e . ' when attempting to get AD Password Policy.');
            return false;
        }

        $complexityRequirement = $AD->isPWComplexityRequired();
        $minPwdLength = $AD->getMinPasswordLength();

        $complexityScore = 0;
        if ($complexityRequirement == true) {
            //Check if there is an uppercase
            if (preg_match('@[A-Z]@', $password)) {
                $complexityScore++;
            }
            //Check if there is a lowercase
            if (preg_match('@[a-z]@', $password)) {
                $complexityScore++;
            }
            //Check if there is a number
            if (preg_match('@[0-9]@', $password)) {
                $complexityScore++;
            }
            //Check if there is a special character
            if (preg_match('@[\p{P}\p{S}]@', $password)) {
                $complexityScore++;
            }

            //If there weren't at least three matches above, then the password is not complex enough
            if ($complexityScore < 3) {
                return false;
            }
        }

        if (strlen($password) >= $minPwdLength) {
            return true;
        }

        return false;
    }

    function ADPasswordPolicyWritten() {
        try {
            $AD = new AD();
        }
        catch(Exception $e) {
            Logger::log('error', $e . ' when attempting to writeout the AD Password Policy.');
            $AD = null;
            return 'NA';
        }

        $complexityRequirement = $AD->isPWComplexityRequired();
        $minPwdLength = $AD->getMinPasswordLength();
        if ($complexityRequirement) {
            $AD = null;
            return 'The password must be ' . $minPwdLength . ' or more characters. It must also contain three of the following:<br />Uppercase, Lowercase, Number, and a Special Character';
        }
        else {
            $AD = null;
            return 'The password must be ' . $minPwdLength . ' or more characters.';
        }
    }
