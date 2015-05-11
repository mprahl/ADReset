# ADReset

## About
ADReset is a self-service Active Directory password reset portal. Written with Bootstrap 3, it is fully responsive, and thus works flawlessly on any device, including mobile phones. ADReset allows a user to securely reset their password using secret questions defined by an administrator or by having a reset link sent to their email (the email address is defined in Active Directory). The server-side code is written entirely in PHP and thus will work on any or webserver operating system.

## Key Features
- Password Reset Via Secret Questions
- Password Reset Via Email
- Change Password Using Old Password
- Active Directory Security Groups Limit Who Is Allowed To Reset Their Password
- Entirely Configurable Via The Web Interface
- PowerShell Script To See If A User's Questions Have Been Set

## Documentation
<ul>
  <li><a href="docs/Ubuntu 14.04 Installation Guide.md">Ubuntu 14.04 (Apache and MySQL) Installation Guide</a></li>
  <li><a href="docs/Retrieving the CA Certificate used for LDAPS.md">Retrieving the CA Certificate used for LDAPS</a></li>
</ul>

For more documentation please browse to the "docs" folder

## Credits
Written by Matthew Prahl except for the PHPMailer and Captcha classes

## Screenshots
<h4>Home Screen</h4>
<img src="screenshots/1%20Home%20-%20Desktop.jpg" alt="Home" width="75%" max-width="1300"/>

<h4>Home Screen - Responsive</h4>
<img src="screenshots/2%20Home%20-%20Responsive.jpg" alt="Home Responsive" width="25%" max-width="500"/>

<h4>Answering Secret Questions</h4>
<img src="screenshots/3%20Secret%20Questions.jpg" alt="Answering Secret Questions" width="75%" max-width="1300"/>

<h4>Selecting A New Password After Answering Questions</h4>
<img src="screenshots/4%20New%20Password.jpg" alt="Selecting A New Password" width="75%" max-width="1300"/>

<h4>Change Password</h4>
<img src="screenshots/5%20Change%20Password.jpg" alt="Changing A Password" width="75%" max-width="1300"/>

<h4>System Settings</h4>
<img src="screenshots/6%20System%20Settings%201.jpg" alt="Home" width="75%" max-width="1300"/>

<img src="screenshots/7%20System%20Settings%202.jpg" alt="Home" width="75%" max-width="1300"/>
