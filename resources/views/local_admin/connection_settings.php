<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Connection Settings';
    require_once(__DIR__ . '/../../templates/header.php');
?>
<body>
<!-- Navigation Menu Starts -->
<?php
    require_once(__DIR__ . '/../../templates/local_navigation.php');
?>
<!-- Navigation Menu Ends -->
<!-- Content Starts -->
<div class="container" id="mainContentBody">
    <h2 class="topHeader">Connection Settings</h2>
    <h3>Welcome <?php echo ucwords($_SESSION['user_name']); ?>,</h3>
    <br />
    <div class="col-md-12">
        <?php
            // Show potential feedback from the settings changes object
            if (FlashMessage::flashIsSet('ChangeConnectionSettingsError')) {
                FlashMessage::displayFlash('ChangeConnectionSettingsError', 'error');
            }
            elseif (FlashMessage::flashIsSet('ChangeConnectionSettingsMessage')) {
                FlashMessage::displayFlash('ChangeConnectionSettingsMessage', 'message');
            }
        ?>
    </div>
    <h4>What would you like to change?</h4>
        <div class="panel-group" id="accordion">
            <!-- Mail Alias Columns Form -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        Domain Connection Settings
                    </h4>
                </div>
                <div id="collapse4" class="panel-collapse ">
                    <div class="panel-body">
                        <p class="systemSettingsSubheader">
                            Enter the connection information below:
                        </p>
                    <form class="form-horizontal" method="post" action="/settings/connectionsettings.php" name="updateConnectionSettings">
                    <fieldset>
                        <div class="form-group">
                            <label for="inputDomainController" class="col-lg-2 control-label">
                                <a class="tool-tip" data-toggle="tooltip" data-placement="top" data-original-title="Enter the Domain Controller name here. An example would be 'dc1.example.local'">Domain Controller:</a>
                            </label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control" id="inputDomainController" placeholder="Domain Controller DNS Name" name="connection_dc" value="<?php echo preg_replace('{^.*//}', '' , $connectiongSettings->get('DC')); ?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPort" class="col-lg-2 control-label">
                                <a class="tool-tip" data-toggle="tooltip" data-placement="top" data-original-title="Enter the LDAPS port here. The default SSL port is '636'.">LDAPS Port:</a>
                            </label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control" id="inputPort" placeholder="LDAPS Port" name="connection_port" value="<?php echo $connectiongSettings->get('port'); ?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputUsername" class="col-lg-2 control-label">
                                <a class="tool-tip" data-toggle="tooltip" data-placement="top" data-original-title="Enter the Username that will be used to connect to Active Directory and reset passwords. An example would be 'adreset_accnt'">Username:</a>
                            </label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control" id="inputUsername" placeholder="Username" name="connection_username" value="<?php echo $connectiongSettings->get('username'); ?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword" class="col-lg-2 control-label">Password:</label>
                            <div class="col-lg-10">
                                <input type="password" class="form-control" id="inputPassword" placeholder="Password" name="connection_password" value="" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputDomainName" class="col-lg-2 control-label">
                                <a class="tool-tip" data-toggle="tooltip" data-placement="top" data-original-title="Enter the name of the Domain that will be used for this application. An example would 'example.local'">Domain Name:</a>
                            </label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control" id="inputDomainName" placeholder="Domain Name" name="connection_domainName" value="<?php echo $connectiongSettings->get('domainName'); ?>" autocomplete="off">
                            </div>
                        </div>
                        <br />
                        <div class="form-group">
                            <div class="col-lg-10 col-lg-offset-2">
                                <button type="submit" class="btn btn-primary" name="ChangeConnectionSettings" value="Change Connection Settings">Update</button>
                                <button class="btn btn-default" type="reset">Reset</button>
                            </div>
                        </div>
                    </fieldset>
                    </form>
                    </div>
                </div>
            </div>
      </div> 
</div>
<!-- Content Ends -->
</body>
</html>