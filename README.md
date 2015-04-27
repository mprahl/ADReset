# ADReset

## About
ADReset is a self-service Active Directory password reset portal. Written with Bootstrap 3, it is fully responsive, and thus works flawlessly on any device, including mobile phones. ADReset allows a user to securely reset their password using secret questions defined by an administrator or by having a reset link sent to their email (the email address is defined in Active Directory). The server-side is written entirely in PHP and thus will work on any operating system.

## Key Features
- Password Reset Via Secret Questions
- Password Reset Via Email
- Change Password Using Old Password
- Active Directory Security Groups Limit Who Is Allowed To Reset Their Password
- Entirely Configurable Via The Web Interface
- PowerShell Script To See If A User's Questions Have Been Set

## Documentation
Please browse to the "docs" folder for documentation on how to install and administer ADReset

## Credits
Written by Matthew Prahl except for the PHPMailer and Captcha classes
