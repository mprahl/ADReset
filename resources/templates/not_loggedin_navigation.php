<?php 
    require_once(__DIR__ . '/../core/init.php');
    $systemSettings = new SystemSettings;
    $isEmailResetEnabled = $systemSettings->getOtherSetting('emailresetenabled');
    if (isset($isEmailResetEnabled) && $isEmailResetEnabled == 'true') {
        echo '<script src="/js/resetPrompt.js"></script>';
    }
?>

<nav role="navigation" class="navbar navbar-default navbar-static-top">
    <div class="container">
        <!-- The brand and the toggle bar (to collapse the menu) -->
        <div class="navbar-header">
            <button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="/index.php" class="navbar-brand"><img src="/img/ADReset.png" /></a>
        </div>
        <!-- Navigation links for toggling -->
        <div id="navbarCollapse" class="collapse navbar-collapse">
            <!-- Right links on the navbar -->
            <ul class="nav navbar-nav navbar-right">
                <li><a href="/index.php">Home</a></li>
                <li><a href="/account.php">Set Questions</a></li>
            <?php
                if (isset($isEmailResetEnabled) && $isEmailResetEnabled == 'true') {
                    echo '<li><a href="/resetpwemail.php" id="resetpw">Forgot Password</a></li>';
                }
                else {
                    echo '<li><a href="/resetpw.php" id="resetpw">Forgot Password</a></li>';
                }
            ?>
                <li><a href="/changepw.php">Change Password</a></li>
                <li><a href="/account.php?page=<?php echo urlencode(substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1)); ?>">Login</a></li>
            </ul>
        </div>
    </div>
</nav>