$(document).ready(function(){
    $('#resetSecretQuestions input').on('click', function(e){
        bootbox.confirm("Are you sure you want to erase your secret answers? You will have to set your three secret answers again before being able to reset your password using secret questions.", function(result) {
            if (result) {
                $(e.target).closest("form").submit();
                return true;
            }
        });

        return false;
    });
});