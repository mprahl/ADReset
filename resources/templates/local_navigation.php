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
            <!-- Logo designed and made by Mandy Patterson -->
            <a href="/index.php" class="navbar-brand"><img src="/img/ADReset_Local.png" /></a>
        </div>
        <!-- Navigation links for toggling -->
        <div id="navbarCollapse" class="collapse navbar-collapse">
			<!-- Right links on the navbar -->
			<ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle">Manage <b class="caret"></b></a>
					<ul role="menu" class="dropdown-menu">
						<li><a class="connectionSettingsLink" href="/settings/connectionsettings.php">Connection Settings</a></li>
						<li class="divider"></li>
						<li><a href="/settings/systemsettings.php">System Settings</a></li>
					</ul>
				</li>
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle">Account <b class="caret"></b></a>
					<ul role="menu" class="dropdown-menu">
							<li><a href="/settings/localusersettings.php">Settings</a></li>
						<li class="divider"></li>
						<li><a href="/localadmin.php?logout">Logout</a></li>
					</ul>
				</li>
				<li><a href="/localadmin.php?logout">Logout</a></li>
			</ul>
        </div>
    </div>
</nav>
<script src="/js/localLogoutConfirm.js"></script>