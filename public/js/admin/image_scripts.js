$(function () {
    var maxFiles = 3;
    var addButton = $('.btn-success');
    var wrapper = $('.increment');
    var fieldHTML = '<div class="hdtuto control-group lst input-group" style="margin-top:10px"><input type="file" name="filenames[]" class="myfrm form-control"><div class="input-group-btn"><button class="btn btn-danger" type="button"><i class="fldemo glyphicon glyphicon-remove"></i> Supprimer</button></div></div>';
    var x = 1;

    $(addButton).click(function () {
        if (x < maxFiles) {
            x++;
            $(wrapper).append(fieldHTML);
        }
        if (x === maxFiles) {
            $(addButton).prop("disabled", true);
        }
    });

    $("body").on("click", ".btn-danger", function () {
        $(this).closest('.hdtuto').remove();
        x--;
        if (x < maxFiles) {
            $(addButton).prop("disabled", false);
        }
    });
    
});