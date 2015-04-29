<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'User Settings';
    require_once(__DIR__ . '/../templates/header.php');
?>
<body>
<!-- Navigation Menu Starts -->
<?php
    require_once(__DIR__ . '/../templates/navigation.php');
?>
<!-- Navigation Menu Ends -->
<script src="/js/resetSecretQuestionsPrompt.js"></script>
<!-- Content Starts -->
<div class="container" id="mainContentBody">
    <h2 class="topHeader">User Settings</h2>
    <h3>Welcome <?php echo ucwords($_SESSION['user_name']); ?>,</h3>
    <br />
    <div class="col-md-12">
        <?php
            // Show potential feedback from the settings changes object
            if (FlashMessage::flashIsSet('ChangeUserSettingsError')) {
                FlashMessage::displayFlash('ChangeUserSettingsError', 'error');
            }
            elseif (FlashMessage::flashIsSet('ChangeUserSettingsMessage')) {
                FlashMessage::displayFlash('ChangeUserSettingsMessage', 'message');
            }
            elseif (($numSecretQuestionsSet = $userSettings->numSecretQuestionsSetToUser($_SESSION['user_name'])) < 3) {
        ?>
        <div class="col-md-12">
            <div class="alert alert-info infoBlurb" role="alert">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <p>Setting your secret questions will allow you to reset your Windows (Active Directory) password without the assistance of the Help Desk.</p>
                <p>Three secret questions must be set before this feature can be taken advantage of. As of now, you have <?php echo $numSecretQuestionsSet; ?> out of 3 set.</p>
            </div>
        </div>
        <?php
                $numSecretQuestionsSet = null;
            }
        ?>
    </div>
    <h4>What would you like to change?</h4>
        <div class="panel-group" id="accordion">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        User Settings
                    </h4>
                </div>
                <div id="collapse4" class="panel-collapse ">
                    <div class="panel-body">
                        <p class="systemSettingsSubheader">
                            <a href="#" class="tool-tip" data-toggle="tooltip" data-placement="top" data-original-title="Below you can fill out your secret questions and answers. Once all three are set, you will be able to reset your Windows (Active Directory) password using them.">Manage Your Secret Questions Below:</a>
                        </p>
                        <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr>
                                <th class="center">Secret Questions:</th>
                                <th class="center">Secret Answers:</th>
                                <th class="center">Actions:</th>
                            </tr></thead>
                            <tbody>
                            <?php
                                $usersQuestions = $userSettings->getSecretQuestionsSetToUser($_SESSION['user_name']);
                                foreach ($usersQuestions as $question) {
                                    echo '<tr>
                                        <form class="btn-link-form" method="post" action="usersettings.php" name="editSecretAnswer">
                                        <td class="center width-forty" style="min-width:150px">', $question, '</td>
                                        <td class="width-fifty" style="min-width:150px"><input type="password" class="form-control" placeholder="Enter a new secret answer" name="secretAnswer"></td>
                                        <td class="center"><input type="submit" class="btn btn-link center" name="editSecretAnswer" value="Update"></td>
                                        <input type="hidden" name="secretQuestion" value="' . $question . '">
                                        </form>
                                    </tr>';
                                }

                                $numSecretQuestionsSet = $userSettings->numSecretQuestionsSetToUser($_SESSION['user_name']); 
                                if ($numSecretQuestionsSet < 3) {
                                    for ($i = $numSecretQuestionsSet; $i < 3; $i++) {
                                        echo '<tr>
                                            <form class="btn-link-form" method="post" action="usersettings.php" name="addSecretAnswer">
                                            <td class="width-forty" style="min-width:150px">';

                                        $secretQuestions = $userSettings->getUniqueSecretQuestionsForUser($_SESSION['user_name']);

                                        echo '<select class="form-control secretQuestionSelect" name="secretQuestion">';

                                        foreach ($secretQuestions as $secretQuestion) {
                                            echo '<option>' . $secretQuestion . '</option>';
                                        }
                                        echo '</select>';
                                        
                                        // Allow input on the next quesiton needed to be filled
                                        if ($i == $numSecretQuestionsSet) {
                                            echo '</td>
                                                <td class="width-fifty" style="min-width:150px"><input type="password" class="form-control" placeholder="Enter your secret answer" name="secretAnswer"></td>
                                                <td class="center"><input type="submit" class="btn btn-link" name="addSecretAnswer" value="Add"></td>
                                                </form>
                                            </tr>';
                                        }
                                        // Disable the input for subsequent questions
                                        else {
                                            echo '</td>
                                                <td class="width-fifty" style="min-width:150px"><input type="password" class="form-control" placeholder="Enter your secret answer" name="secretAnswer" disabled></td>
                                                <td class="center"><input type="submit" class="btn btn-link" name="addSecretAnswer" value="Add" disabled></td>
                                                </form>
                                            </tr>';
                                        }
                                        
                                    }
                                }
                            ?>
                            </tbody>
                        </table>
                        </div>
                    </div>

                    <form method="post" action="usersettings.php" id="resetSecretQuestions" name="resetSecretQuestions">
                        <input type="button" class="btn btn-danger resetQuestionsBtn" name="resetSecretQuestions" value="Erase Questions">
                        <input type="hidden" name="resetSecretQuestions" value="Reset Questions">
                    </form>
                </div>
            </div>
      </div> 
</div>
<!-- Content Ends -->
</body>
</html>