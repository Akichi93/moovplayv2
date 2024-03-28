$(function () {
    var maxFiles = 3; // Définir le nombre maximum de fichiers autorisés
    var addButton = $('.btn-success'); // Bouton d'ajout
    var wrapper = $('.increment'); // Conteneur des champs de fichier
    var fieldHTML = '<div class="hdtuto control-group lst input-group" style="margin-top:10px"><input type="file" name="images[]" class="myfrm form-control"><div class="input-group-btn"><button class="btn btn-danger" type="button"><i class="fldemo glyphicon glyphicon-remove"></i>Retirer</button></div></div>'; // Modèle de champ de fichier supplémentaire


    var x = 1; //Initial field counter is 1

    //Once add button is clicked
    $(addButton).click(function () {
        //Check maximum number of input fields
        if (x < maxFiles) {
            x++; //Increment field counter
            $(wrapper).append(fieldHTML); //Add field html
        }
    });

    // Lorsque le bouton "Ajouter" est cliqué
    // $(addButton).click(function () {
    //     var fileCount = $(".increment").length;
    //     if (fileCount < maxFiles) {
    //         $(wrapper).after(fieldHTML); // Ajouter un champ de fichier supplémentaire
    //         // Désactiver le bouton "Ajouter" s'il y a 5 champs de fichier
    //         if (fileCount + 1 === maxFiles) {
    //             $(addButton).prop("disabled", true);
    //         }
    //     } else {
    //         alert("Vous ne pouvez pas ajouter plus de " + maxFiles + " fichiers.");
    //     }
    // });

     

    // Lorsque le bouton "Retirer" est cliqué
    $("body").on("click", ".btn-danger", function () {
        $(this).parents(".hdtuto").remove(); // Supprimer le champ de fichier
        // Réactiver le bouton "Ajouter" s'il y a moins de 5 champs de fichier
        var fileCount = $(".increment").length;
        if (fileCount < maxFiles) {
            $(addButton).prop("disabled", false);
        }
    });
});


