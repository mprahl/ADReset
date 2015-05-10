# ADReset Documentation

## Installing ADReset on Ubuntu 14.04

### Prerequisites
1. Ubuntu 14.04 server with:
  * Sudo/Root access
  * DNS configured to point to at least one Domain Controller
  * At least one Domain Controller with LDAPS enabled

### Installing Apache, MySQL, and PHP:
<ol>
  <li>Update Ubuntu with the following commands:</li>
    <code>
      sudo apt-get update
    </code><br />
    <code>
      sudo apt-get dist-upgrade
    </code>
    
  <li>Install the Apache2 webserver with the following command:</li>
    <code>
      sudo apt-get install apache2
    </code>
    
  <li>Install MySQL with the following command:</li>
  <code>
    sudo apt-get install mysql-server php5-mysql
  </code>
  
  <li>When prompted, enter a strong root password for MySQL.</li>
  
  <li>Initialize and secure MySQL with the following commands:</li>
  <code>
    sudo mysql_install_db
  </code><br />
  <code>
    sudo mysql_secure_installation
  </code>
  
  <li>
    After running the “mysql_secure_installation” command, enter “Y” for:
    <ol>
      <li>Remove anonymous users</li>
      <li>Disallow root login remotely</li>
      <li>Remove test database and access to it</li>
      <li>Reload privilege tables now</li>
    </ol>
  </li>
  
  <li>Install PHP and the appropriate modules:</li>
  <code>
    sudo apt-get install php5 libapache2-mod-php5 php5-mcrypt php5-ldap php5-gd
  </code>
  
  <li>Enable PHP mcrypt with the following command (may not be necessary):</li>
  <code>
    sudo php5enmod mcrypt
  </code>
  
  <li>Tell Apache to prefer index.php over index.html by editing dir.conf with the following command:</li>
  <code>
    sudo nano /etc/apache2/mods-enabled/dir.conf
  </code>
  
  <li>In dir.conf, change the following:</li>
  <code>
    DirectoryIndex index.html index.cgi index.pl index.php index.xhtml index.htm
  </code><br />
  &nbsp;&nbsp;&nbsp;&nbsp;To:<br />
  <code>
    DirectoryIndex index.php index.html index.cgi index.pl index.xhtml index.htm
  </code>
  
  <li>Close and save dir.conf</li>
  
  <li>Restart Apache with the following command:</li>
  <code>
    sudo service apache2 restart
  </code>
</ol>

### Installing ADReset on Apache:
