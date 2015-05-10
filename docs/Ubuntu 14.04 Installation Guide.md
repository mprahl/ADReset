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
  To:<br />
  <code>
    DirectoryIndex index.php index.html index.cgi index.pl index.xhtml index.htm
  </code>
  
  <li>Close and save dir.conf</li>
  
  <li>Restart Apache with the following command:</li>
  <code>
    sudo service apache2 restart
  </code>
</ol>

### Installing ADReset With Apache and MySQL:
<ol>
  <li>ADReset requires a database and a MySQL user, to do this, start by connecting MySQL with the following command:</li>
  <code>
    mysql -u root -p
  </code>
  
  <li>Once you enter your root password, type the following command (replace password with a strong password):</li>
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
    FLUSH PRIVILEGES; QUIT;
  </code>
  
  <li>In order to have ADReset communicate over LDAPS, it needs to have the appropriate Windows Active Directory Certificate Services (ADCS) CA certificate in BASE64 format. Export that certificate, and then create a new file with the following command:</li>
  <code>
    sudo nano /usr/local/share/ca-certificates/adca.crt
  </code>
  
  <li>Paste the contents of the ADCD CA certificate in adca.crt and save the file.</li>
  
  <li>Now, run the following command for Ubuntu to import the new certificate:</li>
  <code>
    sudo update-ca-certificates
  </code>
  
  <li>Now edit ldap.conf with the following command:</li>
  <code>
    sudo nano /etc/ldap/ldap.conf
  </code>
  
  <li>Then change the following line (if the file is missing, just create it with that line in it):</li>
  <code>
    TLS_CACERT /etc/ssl/certs/ca-certificates.crt
  </code><br />
  To:<br />
  <code>
    TLS_CACERT /usr/local/share/ca-certificates/adca.crt
  </code>
  
  <li>Install git with the following command:</li>
  <code>
    sudo apt-get install git
  </code>
  
  <li>Make a directory for ADReset with the following command:</li>
  <code>
    sudo mkdir /var/www/adreset && sudo cd /var/www/adreset
  </code>
  
  <li>Download ADReset with the following command:</li>
  <code>
    sudo git clone https://github.com/PrahlM93/ADReset.git /var/www/adreset
  </code>
  
  <li>Set the appropriate permissions for Apache with the following commands:</li>
  <code>
  sudo chown -R www-data:www-data /var/www/adreset
  </code><br />
  <code>
  sudo find /var/www/adreset/ -type d -exec chmod 550 {} \;
  </code><br />
  <code>
  sudo find /var/www/adreset/ -type f -exec chmod 440 {} \;
  </code><br />
  <code>
  sudo chmod 770 /var/www/adreset/resources/core
  </code><br />
  <code>
  sudo chmod 770 /var/www/adreset/resources/logs
  </code>
  
  <li>
    Optional But Recommended – Setting up SSL (HTTPS):
    <ol>
      <li>
        Create the directory to hold the certificates with the following command:<br />
        <code>
          sudo mkdir /etc/apache2/certificates
        </code>
      </li>
      
      <li>
        Then move the signed certificate and the private key to the folder just created with the following commands:<br />
        <code>
          sudo mv /path/to/cert/certname.pem /etc/apache2/certificates/cert.pem
        </code><br />
        <code>
          sudo mv /path/to/key/keyname.pem /etc/apache2/certificates/key.pem
        </code>
      </li>
      
      <li>
        Provide proper permissions to the certificate and private key with the following commands:<br />
        <code>
          sudo chown -R root:root /etc/apache2/certificates
        </code>
        <code>
          sudo chmod 440 /etc/apache2/certificates/*
        </code>
      </li>
      
      <li>
        Enable the SSL module for Apache with the following command:<br />
        <code>
          sudo a2enmod ssl
        </code>
      </li>
      
      <li>
        Enable the Rewrite module for Apache with the following command:<br />
        <code>
          sudo a2enmod rewrite
        </code>
      </li>
      
      <li>
        Enable the Headers module for Apache with the following command:<br />
        <code>
          sudo a2enmod headers
        </code>
      </li>
    </ol>
  </li>
  
  <li>Disable the default site for Apache:</li>
  <code>
    sudo a2dissite 000-default.conf
  </code>
  
  <li>
    Create an Apache configuration file for ADReset with the following command:<br />
    <code>
      sudo nano /etc/apache2/sites-available/adreset.conf
    </code>
    <ul>
      <li>
        For just HTTP (no SSL):<br />
<pre>
&lt;VirtualHost *:80&gt;
  ServerAdmin webmaster@localhost
  DocumentRoot /var/www/adreset/public
  
  ErrorLog ${APACHE_LOG_DIR}/error.log
  CustomLog ${APACHE_LOG_DIR}/access.log combined
&lt;/VirtualHost&gt;
</pre>
      </li>
      
      <li>
        For SSL (HTTPS):
<pre>
&lt;VirtualHost *:80&gt;
  ServerAdmin webmaster@localhost
  DocumentRoot /var/www/adreset/public
  
  ErrorLog ${APACHE_LOG_DIR}/error.log
  CustomLog ${APACHE_LOG_DIR}/access.log combined
  RewriteEngine On
  RewriteCond %{HTTPS} off
  RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI}
&lt;/VirtualHost&gt;

&lt;IfModule mod_ssl.c&gt;
  &lt;VirtualHost *:443&gt;
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/adreset/public
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
    
    SSLEngine on
    #Prevents SSL Strip
    Header set Strict-Transport-Security "max-age=16070400; includeSubDomains"
    SSLCertificateFile /etc/apache2/certificates/cert.pem
    SSLCertificateKeyFile /etc/apache2/certificates/key.pem
    
    &lt;FilesMatch &quot;\.(cgi|shtml|phtml|php)$&quot;&gt;
      SSLOptions +StdEnvVars
    &lt;/FilesMatch&gt;
    
    BrowserMatch "MSIE [2-6]" \
    nokeepalive ssl-unclean-shutdown \
    downgrade-1.0 force-response-1.0
    
    # MSIE 7 and newer should be able to use keepalive
    BrowserMatch "MSIE [17-9]" ssl-unclean-shutdown
  &lt;/VirtualHost&gt;
&lt;/IfModule&gt;
</pre>
      </li>
    </ul>
  </li>
  
  <li>Enable the new site with the following command:</li>
  <code>
    sudo a2ensite adreset.conf
  </code>

  <li>Restart Apache with the following command:</li>
  <code>
    sudo service apache2 restart
  </code>
  
  <li>From any computer on the network, navigate to ADReset’s installer (replacing ip.add.re.ss with your web server’s IP address):</li>
  <code>
    http://ip.add.re.ss/installer.php
  </code>
</ol>
