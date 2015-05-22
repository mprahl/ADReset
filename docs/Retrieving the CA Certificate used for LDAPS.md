# ADReset Documentation

## Exporting the CA Certificate used for LDAPS

### Prerequisites
1. Domain Controller With:
  * LDAPS Enabled (Done by installing and configuring Active Directory Certificate Services on the Domain Controller and restarting)
  * Administrative access available

### Exporting the CA Certificate
<ol>
  <li>Open the "Certificates" MMC by running:</li>
  <code>
    certmgr.msc
  </code>
  
  <li>Expand "Trusted Root Certificate Authorities" => click on "Certificates"<br /><br />
    <img src="documentation images/Certificate - 1.png" />
  </li>
  
  <li>Then find the Certificate Authority that was generated during the installation of Active Directory Certificate Services. This will often be a variation of the server name and/or the domain name. The one generated for the example sky.local domain looks like the following:<br /><br />
    <img src="documentation images/Certificate - 2.png" />
  </li>
  
  <li>Once you've found the appropriate certificate, right-click on it => hover on "All Tasks" => click on "Export"<br /><br />
    <img src="documentation images/Certificate - 3.png" />
  </li>
  
  <li>On the "Certificate Export Wizard", click on "Next" => Select "Base-64 encoded X.509 (.CER)"<br /><br />
    <img src="documentation images/Certificate - 4.png" />
  </li>
  
  <li>Click on "Next" => Click on "Browse" => Select a temporary location for the certificate such as your "Desktop", and name it something such as "LDAPS-CA"<br /><br />
    <img src="documentation images/Certificate - 5.png" />
  </li>
  
  <li>Click on "Next" => Click on "Finish"</li>
  
  <li>Navigate to where you saved "LDAPS-CA.cer". If you are installing ADReset on Windows, just copy the certificate file over to C:\openldap\sysconf, otherwise, right-click on it => hover on "Open With" => click on "Notepad"<br /><br />
    <img src="documentation images/Certificate - 6.png" />
  </li>
  
  <li>You may now copy the contents to the server you are running ADReset on<br /><br />
    <img src="documentation images/Certificate - 7.png" />
  </li>
</ol>
