# ADReset Documentation

## Installing ADReset on Windows Server 2012 R2

### Prerequisites
1. Windows Server 2012 R2 with:
  * Administrative access
  * "IE Enhanced Security" disabled for the installation (it can be reenabled afterwards)
  * Visual C++ Redistributable for Visual Studio 2012 Update 4 (x86) installed (<a href="https://www.microsoft.com/en-us/download/details.aspx?id=30679">Download x86 Version Here</a>)
  * DNS configured to point to at least one Domain Controller (optional)
  * At least one Domain Controller with LDAPS enabled (Admin access to the Domain Controller maybe required)
  
### Installing IIS, PHP, and MySQL
<ol>
  <li>Open "Server Manager" => Click on "Manage" => Click on "Add Roles and Features"</li>
  <li>In the "Add Roles and Features Wizard", click "Next" => Click "Next" => Click "Next" => Check "Web Server (IIS) => If a windows pops up asking "Add features that are required for Web Server (IIS)?", click "Add Features" => Click "Next"</li>
  <li>When asked to "Select features", check ".NET Framework 3.5 Features" => Click "Next" => Click "Next" => Click "Next" => Click "Install"</li>
  <li>Once IIS and .NET Framework 3.5 is installed, you may close the "Add Roles and Features Wizard" window and close "Server Manager"</li>
  <li>Download and install the "Microsoft Web Platform Installer" from <a href="http://go.microsoft.com/fwlink/?LinkId=255386">here</a></li>
  <li>Open "Microsoft Web Platform Installer" => Search for "PHP 5.5" => Click on "Add" button in the row for "PHP 5.5" => Click on "Install" => On the pop-up Window, click "I Accept" => Once installed, click on "Finish".</li>
  <li>In "Microsoft Web Platform Installer" => Search for "MySQL 5.5" => Click on "Add" button in the row for "MySQL Windows 5.5" => Click on "Install" => On the pop-up Window, type in a root password for MySQL => Uncheck "Save my password" => Click on "Continue" => Click on "I Accept" => Once installed, click on "Finish" => Close "Web Platform Installer"</li>
</ol>

### Downloading ADReset and Granting the Proper Permissions
<ol>
  <li>Download and install "git for Windows" from http://msysgit.github.io/ (git is used to download and update ADReset)</li>
  <li>Run "Git GUI" as an Administrator => Click on "Clone Existing Repository" => Enter "https://github.com/PrahlM93/ADReset.git" for the "Source Location" => Enter "C:/inetpub/adreset" as the "Target Directory" => Click on "Clone" => Close "Git GUI"</li>
  <li>Browse to "C:\inetpub\adreset\resources" => Right-click on "core" => Click on "Properties" => Click on the "Security" tab => Click on "Edit" => In the "Permissions for core" window, click on "Add" => In the "Enter the object names to select" field, type "iusr" => Click on "Check Names" (if this doesn't work, ensure the location for the search is the local server and not Active Directory) => Click on "OK" => Check "Modify" for "IUSR" => Click on "OK" => Click on "OK".</li>
  <li>Browse to "C:\inetpub\adreset\resources" => Right-click on "logs" => Click on "Properties" => Click on the "Security" tab => Click on "Edit" => In the "Permissions for logs" window, click on "Add" => In the "Enter the object names to select" field, type "iusr" => Click on "Check Names" (if this doesn't work, ensure the location for the search is the local server and Active Directory) => Click on "OK" => Check "Modify" for "IUSR" => Click on "OK" => Click on "OK".</li>
</ol>

### Configuring IIS and PHP

<ol>
  <li>Open "Internet Information Services (IIS) Manager" => Click on your Server Name in the left pane => Double-click on "PHP Manager" => Click on "Enable or disable an extension" => Select on "php_ldap.dll" => Click on "Enable" in the  "Actions" pane.</li>
  <li>In the left pane, expand "Sites" => right-click on "Default Web Site" => Hover on "Manage Website" => Click on "Advanced Settings"</li>
  <li>In the "Advanced Settings" windows, change "Physical Path" to "%SystemDrive%\inetpub\adreset\public" => Click on "OK"</li>
  <li>Click on your Server Name in the left pane => Click on "Restart" in the Actions pane.</li>
</ol>

### Configuring PHP LDAP
<ol>
  <li>Create the folder "C:\openldap"</li>
  <li>Create the folder "C:\openldap\sysconf"</li>
  <li>In order to have ADReset communicate over LDAPS, it needs to have the appropriate Active Directory Certificate Services (ADCS) CA certificate in BASE64 format. That certificate must be exported. To do so, you can follow this <a href="Retrieving the CA Certificate used for LDAPS.md">this tutorial</a></li>
  <li>After the following the tutorial in the previous step, make sure the certificate is named "LDAPS-CA.cer" and is located in "C:\openldap\sysconf"</li>
  <li>Browse to "C:\openldap\sysconf" and create a text file called ldap.conf (make sure the extension is .conf and not .txt) => Add the following contents:</li>
  <code>
TLS_REQCERT never
  </code><br />
  <code>
TLS_CACERT C:\openldap\sysconf\LDAPS-CA.cer
  </code>
</ol>

### Configure MySQL
<ol>
  <li>Open "MySQL 5.5 Command Line Client" => Enter the root password for MySQL set earlier => Then enter the following commands (change password to a strong password):</li>
  <code>
CREATE DATABASE IF NOT EXISTS adreset;
  </code><br />
  <code>
CREATE USER 'adresetuser'@'127.0.0.1' IDENTIFIED BY 'password';
  </code><br />
  <code>
GRANT ALL PRIVILEGES ON adreset.* TO 'adresetuser'@'127.0.0.1';
  </code><br />
  <code>
FLUSH PRIVILEGES;
  </code><br />
  <code>
QUIT;
  </code>
</ol>

### Configure ADReset
<ol>
  <li>Open "Internet Explorer" and browse to "http://localhost/installer.php". If the IIS configuration was successful, you should be presented with the "Install ADReset" page. (Note: The server version of Internet Explorer does not correctly render padding on forms)</li>
  <li>On this page, it will make sure you have all the PHP modules that are required. If any are missing, go back to the section “Installing Apache, MySQL, and PHP”. If you followed the directions correctly, you should see a screen that looks like this:<br /><br />
    <img src="documentation images/Installer - 1.png" />
  </li>
  
  <li>Now enter the settings you configured in step 2. It should look something like this:<br /><br />
    <img src="documentation images/Installer - 2.png" />
  </li>
  
  <li>Once the form is filled out, click on “Connect”</li>
  
  <li>Afterwards, you must create a local administrator account to initially configure ADReset. Here is an example:<br /><br />
    <img src="documentation images/Installer - 3.png" />
  </li>
  
  <li>Once the form is filled out, click on “Create”</li>
  
  <li>If successful, you should see the following message:<br /><br />
    <img src="documentation images/Installer - 4.png" />
  </li>
  
  <li>Go back to your command-line shell on Ubuntu and delete installer.php with the following command:</li>
  <code>
    sudo rm /var/www/adreset/public/installer.php
  </code>
  
  <li>Go back to ADReset and login with your new Administrator account.</li>
  
  <li>Upon login, you should be taken to the “Connection Settings” page. Here you will specify how to connect to Active Directory. Here is an example where sky.local is the domain:<br /><br />
    <img src="documentation images/Installer - 5.png" />
  </li>
  
  <li>Make sure that the Domain Controller you specify has LDAPS enabled. If all of your Domain Controllers have LDAPS enabled, it is recommended to put the domain name in the Domain Controller field (i.e. sky.local) as this will allow ADReset to connect to any available Domain Controller.</li>
  
  <li>If the connection was successful, you will receive the following message:<br /><br />
    <img src="documentation images/Installer - 6.png" />
  </li>
  
  <li>Now, it is time configure the System Settings, to do so, click on “Manage” then click on “System Settings”</li>
</ol>
