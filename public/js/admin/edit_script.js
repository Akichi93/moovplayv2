$(function () {
    var maxFiles = 3;
    var wrapper = $('.increment');

    // Fonction pour ajouter un bouton "Ajouter" après la suppression
    function addButtonAfterDeletion() {
        var addButtonHTML = '<div class="input-group-btn"> \
                                <button class="btn btn-success add-button" type="button"> \
                                    <i class="fldemo glyphicon glyphicon-plus"></i>Ajouter \
                                </button> \
                            </div>';
        $(wrapper).append(addButtonHTML);
    }

    // Gérer la suppression des éléments image existants
    $("body").on("click", ".deleted-btn", function () {
        $(this).closest('.increment').remove();
        addButtonAfterDeletion(); // Ajouter le bouton "Ajouter" après la suppression
    });

    // Gérer l'ajout de nouveaux champs de fichier
    $("body").on("click", ".add-button", function () {
        var numExistingFields = $('.increment').length;
        var maxFiles = 3; // Le nombre maximal de champs de fichier autorisé
        if (numExistingFields < maxFiles) {
            var newFieldHTML = '<div class="input-group hdtuto control-group lst increment"> \
                                    <div class="row col-md-6"> \
                                        <input type="file" name="images[]" class="myfrm form-control"> \
                                    </div> \
                                    <div class="row col-md-6"> \
                                        <div class="image-container"></div> \
                                        <div class="input-group-btn" style="display: inline-block;"> \
                                            <button class="btn btn-danger deleted-btn" type="button"> \
                                                <i class="fldemo glyphicon glyphicon-remove"></i> \
                                                Supprimer \
                                            </button> \
                                        </div> \
                                    </div> \
                                </div>';
            $(wrapper).append(newFieldHTML);
        }
    });
});
